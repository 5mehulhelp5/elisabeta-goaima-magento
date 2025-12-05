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
            $bodyParams = $this->request->getBodyParams();

            $keys = [
                'legal_cui',
                'legal_company',
                'street_number',
                'building',
                'floor',
                'apartment'
            ];

            $dataToSave = [];
            foreach ($keys as $key) {
                $val = $this->findValueRecursive($bodyParams, $key);
                if ($val) {
                    $dataToSave[$key] = $val;
                }
            }

            if (!empty($dataToSave)) {
                $quote = $this->cartRepository->getActive($cartId);
                $shippingAddress = $quote->getShippingAddress();

                if ($shippingAddress->getId()) {
                    foreach ($dataToSave as $k => $v) {
                        $shippingAddress->setData($k, $v);
                    }
                    $this->addressResource->save($shippingAddress);
                    $this->logger->info("LegalPerson: SUCCESS - Saved attributes: " . implode(', ', array_keys($dataToSave)));
                } else {
                    $this->logger->warning("LegalPerson: Shipping Address has no ID.");
                }
            } else {
                $this->logger->info("LegalPerson: No custom attributes found in payload.");
            }

        } catch (\Exception $e) {
            $this->logger->error('LegalPerson CRITICAL: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Cauta o cheie in array multidimensional (pentru ca structura payload-ului variaza)
     */
    private function findValueRecursive($array, $keySearch) {
        if (!is_array($array)) {
            return null;
        }

        foreach ($array as $key => $value) {
            if ($key === $keySearch && !is_array($value)) {
                return $value;
            }

            if (is_array($value) && isset($value['attribute_code']) && $value['attribute_code'] === $keySearch) {
                return $value['value'] ?? null;
            }

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
