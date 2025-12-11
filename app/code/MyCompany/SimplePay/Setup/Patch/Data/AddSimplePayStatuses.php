<?php

namespace MyCompany\SimplePay\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSimplePayStatuses implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    public function apply()
    {
        // prepare database
        $this->moduleDataSetup->startSetup();

        $statuses = [
            [
                'status' => 'waiting_simple_pay',
                'label'  => 'Waiting for Simple Pay'
            ],
            [
                'status' => 'simple_pay_paid',
                'label'  => 'Simple Pay Paid'
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('sales_order_status'),
            $statuses,
            ['label']
        );

        // a status must belong to a state (child - parent relationship)
        $statusStates = [
            [
                'status'     => 'waiting_simple_pay',
                'state'      => 'new',
                'is_default' => 0, // it is not the default status
                'visible_on_front' => 1 // the customer can see this status in my orders
            ],
            [
                'status'     => 'simple_pay_paid',
                'state'      => 'processing',
                'is_default' => 0,
                'visible_on_front' => 1
            ]
        ];

        $this->moduleDataSetup->getConnection()->insertOnDuplicate(
            $this->moduleDataSetup->getTable('sales_order_status_state'),
            $statusStates,
            ['state', 'is_default', 'visible_on_front']
        );

        $this->moduleDataSetup->endSetup();

    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
