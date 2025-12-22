<?php

namespace MyCompany\Warranty\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Warranty extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('mycompany_warranty', 'entity_id');
    }
}
