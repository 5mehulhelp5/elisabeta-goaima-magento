<?php
namespace MyCompany\StorePickup\Plugin\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\Info;

class InfoPlugin
{
    public function afterToHtml(Info $subject, $result)
    {
        if ($subject->getNameInLayout() !== 'order_info') {
            return $result;
        }

        $order = $subject->getOrder();
        if (!$order) {
            return $result;
        }

        if ($order->getShippingMethod() !== 'storepickup_storepickup') {
            return $result;
        }

        $pickupStore = $order->getData('pickup_store');
        $pickupTime  = $order->getData('pickup_time');

        $html = '<div style="background-color: #f5f5f5; padding: 15px; margin-top: 15px; border: 1px solid #ccc; clear: both;">';
        $html .= '<h3 style="margin-top:0;">' . __('Store Pickup Details') . '</h3>';

        if ($pickupStore) {
            $html .= '<p><strong>' . __('Location:') . '</strong> ' . $subject->escapeHtml($pickupStore) . '</p>';
        } else {
            $html .= '<p style="color:red;">Store data is missing inside Order object.</p>';
        }

        if ($pickupTime) {
            $html .= '<p><strong>' . __('Time slot:') . '</strong> ' . $subject->escapeHtml($pickupTime) . '</p>';
        }

        $html .= '</div>';

        return $result . $html;
    }
}
