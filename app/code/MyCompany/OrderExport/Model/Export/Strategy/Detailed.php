<?php
namespace MyCompany\OrderExport\Model\Export\Strategy;

use Magento\Sales\Api\Data\OrderInterface;

class Detailed implements BuilderInterface
{
    public function build(OrderInterface $order): array
    {
        $itemsData = [];

        foreach ($order->getAllVisibleItems() as $item) {
            $itemsData[] = [
                'sku'          => $item->getSku(),
                'name'         => $item->getName(),
                'qty'          => (int)$item->getQtyOrdered(),
                'price'        => (float)$item->getPrice(),
                'row_total'    => (float)$item->getRowTotal(),
                'product_type' => $item->getProductType()
            ];
        }

        return [
            'header' => [
                'reference_id' => $order->getIncrementId(),
                'placed_at'    => $order->getCreatedAt(),
                'currency'     => $order->getOrderCurrencyCode(),
                'status'       => $order->getStatus(),
            ],
            'customer' => [
                'email' => $order->getCustomerEmail(),
                'first_name' => $order->getCustomerFirstname(),
                'last_name'  => $order->getCustomerLastname(),
            ],
            'totals' => [
                'subtotal'    => (float)$order->getSubtotal(),
                'tax'         => (float)$order->getTaxAmount(),
                'shipping'    => (float)$order->getShippingAmount(),
                'discount'    => (float)$order->getDiscountAmount(),
                'grand_total' => (float)$order->getGrandTotal(),
            ],
            'items' => $itemsData
        ];
    }
}
