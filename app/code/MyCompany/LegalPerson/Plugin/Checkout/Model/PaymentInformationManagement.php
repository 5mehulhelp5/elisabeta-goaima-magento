<?php
namespace MyCompany\LegalPerson\Plugin\Checkout\Model;

use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Framework\Webapi\Rest\Request;
use Psr\Log\LoggerInterface;

class PaymentInformationManagement
{
    protected $cartRepository;
    protected $addressResource;
    protected $quoteAddressFactory;
    protected $logger;
    protected $request;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressResource $addressResource,
        AddressFactory $quoteAddressFactory,
        LoggerInterface $logger,
        Request $request
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressResource = $addressResource;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->logger = $logger;
        $this->request = $request;
    }

    public function afterSavePaymentInformation(
        PaymentInformationManagementInterface $subject,
                                              $result,
                                              $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->logger->info('LegalPerson Billing: START processing CartID ' . $cartId);

        try {
            $bodyParams = $this->request->getBodyParams();
            $billingData = $bodyParams['billingAddress'] ?? [];

            $cuiValue = null;
            $companyValue = null;

            if (!empty($billingData['extension_attributes'])) {
                $ext = $billingData['extension_attributes'];
                $cuiValue = $ext['legal_cui'] ?? null;
                $companyValue = $ext['legal_company'] ?? null;
            }

            if ((!$cuiValue || !$companyValue) && !empty($billingData['customAttributes'])) {
                foreach ($billingData['customAttributes'] as $attr) {
                    if (($attr['attribute_code'] ?? '') === 'legal_cui') $cuiValue = $attr['value'];
                    if (($attr['attribute_code'] ?? '') === 'legal_company') $companyValue = $attr['value'];
                }
            }

            if (!$cuiValue && !$companyValue) {
                $this->logger->info('LegalPerson Billing: Trying copy from Shipping...');

                $quote = $this->cartRepository->getActive($cartId);
                $shippingAddress = $quote->getShippingAddress();

                if ($shippingAddress->getId()) {
                    $freshShipping = $this->quoteAddressFactory->create()->load($shippingAddress->getId());
                    $cuiValue = $freshShipping->getData('legal_cui');
                    $companyValue = $freshShipping->getData('legal_company');
                }
            }

            $this->logger->info("LegalPerson Billing Values found: CUI=" . ($cuiValue ?? 'NULL'));

            if ($cuiValue || $companyValue) {
                $quote = $this->cartRepository->getActive($cartId);
                $quoteBillingAddress = $quote->getBillingAddress();

                if ($quoteBillingAddress && $quoteBillingAddress->getId()) {
                    $realBillingModel = $this->quoteAddressFactory->create()->load($quoteBillingAddress->getId());

                    if ($realBillingModel->getId()) {
                        $realBillingModel->setData('legal_cui', $cuiValue);
                        $realBillingModel->setData('legal_company', $companyValue);

                        $this->addressResource->save($realBillingModel);
                        $this->logger->info("LegalPerson Billing: SUCCESS - Saved to database.");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('LegalPerson Billing CRITICAL: ' . $e->getMessage());
        }

        return $result;
    }
}
