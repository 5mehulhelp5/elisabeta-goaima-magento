<?php
namespace MyCompany\LegalPerson\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement as Subject;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;

class ShippingInformationManagement
{
    protected $cartRepository;
    protected $addressResource;
    protected $logger;
    protected $request;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressResource $addressResource,
        LoggerInterface $logger,
        Request $request
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressResource = $addressResource;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function afterSaveAddressInformation(
        Subject $subject,
                $result,
                $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $this->logger->info('LegalPerson: START processing CartID ' . $cartId);

        try {
            // 1. Luam tot Request-ul Brut
            $bodyParams = $this->request->getBodyParams();

            // DEBUG: Scriem TOT ce primim intr-un fisier separat pentru a fi 100% siguri
            file_put_contents(BP . '/var/log/legal_payload.log', print_r($bodyParams, true));

            // 2. Cautare Recursiva (Gaseste cheia oriunde ar fi ea in JSON)
            $cuiValue = $this->findValueRecursive($bodyParams, 'legal_cui');
            $companyValue = $this->findValueRecursive($bodyParams, 'legal_company');

            $this->logger->info("LegalPerson: Extracted Values - CUI: " . ($cuiValue ?? 'NULL') . ", Company: " . ($companyValue ?? 'NULL'));

            // 3. Salvare Fortata
            if ($cuiValue || $companyValue) {
                $quote = $this->cartRepository->getActive($cartId);
                $shippingAddress = $quote->getShippingAddress();

                if ($shippingAddress->getId()) {
                    $shippingAddress->setData('legal_cui', $cuiValue);
                    $shippingAddress->setData('legal_company', $companyValue);

                    // Salvare directa in DB
                    $this->addressResource->save($shippingAddress);

                    $this->logger->info("LegalPerson: SUCCESS - Saved to DB via ResourceModel.");
                } else {
                    $this->logger->warning("LegalPerson: Shipping Address has no ID.");
                }
            } else {
                $this->logger->warning("LegalPerson: Values are still NULL after recursive search. Check var/log/legal_payload.log");
            }

        } catch (\Exception $e) {
            $this->logger->error('LegalPerson CRITICAL: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Functie care cauta o cheie in orice adancime a array-ului
     * Suporta si formatul Magento customAttributes: [{attribute_code: "x", value: "y"}]
     */
    private function findValueRecursive($array, $keySearch) {
        if (!is_array($array)) {
            return null;
        }

        foreach ($array as $key => $value) {
            // Cazul 1: Cheia este gasita direct (ex: in extension_attributes)
            if ($key === $keySearch && !is_array($value)) {
                return $value;
            }

            // Cazul 2: Formatul Custom Attributes (Array de obiecte)
            if (is_array($value) && isset($value['attribute_code']) && $value['attribute_code'] === $keySearch) {
                return $value['value'] ?? null;
            }

            // Cazul 3: Continuam cautarea in adancime
            if (is_array($value)) {
                $result = $this->findValueRecursive($value, $keySearch);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }
}
