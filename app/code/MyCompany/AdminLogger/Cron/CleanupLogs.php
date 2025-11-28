<?php

namespace MyCompany\AdminLogger\Cron;

use MyCompany\AdminLogger\Model\ResourceModel\ActionLog\CollectionFactory;
use MyCompany\AdminLogger\Helper\Config;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CleanupLogs
{
    protected $collectionFactory;
    protected $configHelper;
    protected $date;

    public function __construct(
        CollectionFactory $collectionFactory,
        Config $configHelper,
        DateTime $date
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configHelper = $configHelper;
        $this->date = $date;
    }


    public function execute()
    {
        $retentionDays = $this->configHelper->getRetentionPeriod();
        if (!$retentionDays) {
            return;
        }

        $timestamp = strtotime("-$retentionDays days");
        $dateLimit = $this->date->date('Y-m-d H:i:s', $timestamp);

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('created_at', ['lt' => $dateLimit]);

        foreach ($collection as $log) {
            $log->delete();
        }
    }
}
