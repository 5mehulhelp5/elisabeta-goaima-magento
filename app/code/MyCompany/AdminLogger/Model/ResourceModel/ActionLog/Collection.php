<?php

namespace MyCompany\AdminLogger\Model\ResourceModel\ActionLog;

use MyCompany\AdminLogger\Model\ActionLog as Model;
use MyCompany\AdminLogger\Model\ResourceModel\ActionLog as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
