<?php

namespace MyCompany\Warranty\Model;

use Magento\Framework\Model\AbstractModel;

class Warranty extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Warranty::class);
    }
}
