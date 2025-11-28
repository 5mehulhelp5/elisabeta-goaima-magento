<?php

namespace MyCompany\AdminLogger\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ActionLog extends AbstractDb
{
    // AbstractDb has already a constructor that is used by magento so we use _construct (which is called by __construct)
    protected function _construct()
    {
        // creates a relationship with the db
        // we need to pass the table name and primary key, which are used in the generated sql
        $this->_init('my_company_admin_action_log', 'log_id');
    }
}
