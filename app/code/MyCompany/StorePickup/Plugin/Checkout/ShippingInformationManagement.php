<?php
namespace MyCompany\StorePickup\Plugin\Checkout;

class ShippingInformationManagement
{
    protected $quoteRepository;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
                                                              $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $shippingAddress = $addressInformation->getShippingAddress();

        if ($shippingAddress->getExtensionAttributes()) {
            $extAttributes = $shippingAddress->getExtensionAttributes();

            $pickupStore = $extAttributes->getPickupStore();
            $pickupTime = $extAttributes->getPickupTime();

            $quote = $this->quoteRepository->getActive($cartId);
            $quote->setData('pickup_store', $pickupStore);
            $quote->setData('pickup_time', $pickupTime);
        }
    }
}
