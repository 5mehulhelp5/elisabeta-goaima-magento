<?php

namespace MyCompany\SimplePay\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class MarkPaid extends Action
{
    protected $orderRepository;

    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        try {
            $order = $this->orderRepository->get($orderId);
            if ($order->getPayment()->getMethod() == 'simplepay') {
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                $order->setStatus('simple_pay_paid');
                $order->addCommentToStatusHistory(__('Payment confirmed by admin via Simple Pay'));
                $this->orderRepository->save($order);
                $this->messageManager->addSuccessMessage(__('Order marked as paid.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('sales/order/view', ['order_id' => $orderId]);
    }
}
