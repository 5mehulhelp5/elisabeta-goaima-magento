<?php

namespace MyCompany\Warranty\Model\ResourceModel\Warranty;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _constructor()
    {
        $this->init(
            \MyCompany\Warranty\Model\Warranty::class,
            \MyCompany\Warranty\Model\ResourceModel\Warranty::class
        );
    }
}
