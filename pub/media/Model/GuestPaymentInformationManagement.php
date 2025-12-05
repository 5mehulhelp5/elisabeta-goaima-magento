<?php
namespace media\Model;

use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;
use Psr\Log\LoggerInterface;

class GuestPaymentInformationManagement
{
    protected $cartRepository;
    protected $addressResource;
    protected $quoteAddressFactory;
    protected $quoteIdMaskFactory;
    protected $logger;
    protected $request;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        AddressResource $addressResource,
        AddressFactory $quoteAddressFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LoggerInterface $logger,
        Request $request
    ) {
        $this->cartRepository = $cartRepository;
        $this->addressResource = $addressResource;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * Plugin pentru Guest Checkout
     */
    public function afterSavePaymentInformation(
        GuestPaymentInformationManagementInterface $subject,
                                                   $result,
                                                   $cartId,
                                                   $email,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = null
    ) {
        $this->logger->info('LegalPerson GUEST Billing: START. MaskedID: ' . $cartId);

        try {
            // Decodam Masked Quote ID in Integer ID
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $realCartId = $quoteIdMask->getQuoteId();

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
                $this->logger->info('LegalPerson GUEST: Copying from Shipping...');

                $quote = $this->cartRepository->getActive($realCartId);

                $shippingAddress = $quote->getShippingAddress();
                if ($shippingAddress->getId()) {
                    $freshShipping = $this->quoteAddressFactory->create()->load($shippingAddress->getId());
                    $cuiValue = $freshShipping->getData('legal_cui');
                    $companyValue = $freshShipping->getData('legal_company');
                }
            }

            $this->logger->info("LegalPerson GUEST Values: CUI: " . ($cuiValue ?? 'NULL'));

            if ($cuiValue || $companyValue) {
                $quote = $this->cartRepository->getActive($realCartId);
                $quoteBillingAddress = $quote->getBillingAddress();

                if ($quoteBillingAddress && $quoteBillingAddress->getId()) {
                    $realBillingModel = $this->quoteAddressFactory->create()->load($quoteBillingAddress->getId());

                    if ($realBillingModel->getId()) {
                        $realBillingModel->setData('legal_cui', $cuiValue);
                        $realBillingModel->setData('legal_company', $companyValue);
                        $this->addressResource->save($realBillingModel);

                        $this->logger->info("LegalPerson GUEST: SUCCESS - Saved.");
                    }
                }
            }

        } catch (\Exception $e) {
            $this->logger->error('LegalPerson GUEST CRITICAL: ' . $e->getMessage());
        }

        return $result;
    }
}
