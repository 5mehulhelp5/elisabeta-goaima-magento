<?php
namespace MyCompany\OrderExport\Model\Export\Strategy;

use Magento\Sales\Api\Data\OrderInterface;

interface BuilderInterface
{
    public function build(OrderInterface $order): array;
}
