<?php

namespace MyCompany\SimplePay\Plugin\Adminhtml;

use Magento\Sales\Block\Adminhtml\Order\View;

class AddOrderButton
{
    public function beforeSetLayout(View $subject){
        $order = $subject->getOrder();

        if($order->getPayment()->getMethod() == 'simplepay' && $order->getStatus() == 'waiting_simple_pay'){
            $url = $subject->getUrl('simplepay/order/markpaid', ['order_id' => $order->getId()]);
            $subject->addButton(
                'simplepay_mark_paid',
                [
                    'label' => __('Mark as Simple Pay Paid'),
                    'class' => 'primary',
                    'onclick' => 'setLocation(\'' . $url . '\')',
                ]
            );
        }
    }
}
