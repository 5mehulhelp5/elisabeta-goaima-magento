<?php

namespace MyCompany\AdminLogger\Model;

use Magento\Framework\Model\AbstractModel;

class ActionLog extends AbstractModel
{
    protected function _construct()
    {
        // created a relationship between the model and resource model
        $this->_init(ResourceModel\ActionLog::class);
    }
}
