<?php
namespace MyCompany\StorePickup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CopyPickupDataToOrder implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        if ($quote->getShippingAddress()->getShippingMethod() == 'storepickup_storepickup') {
            $order->setData('pickup_store', $quote->getData('pickup_store'));
            $order->setData('pickup_time', $quote->getData('pickup_time'));
        }
    }
}
