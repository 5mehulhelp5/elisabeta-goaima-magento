<?php

namespace MyCompany\AdminLogger\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    // the structure is section id / group id / field id
    const XML_PATH_ENABLED = 'admin_logger/general/enabled';
    const XML_PATH_RETENTION = 'admin_logger/general/retention_days';
    const XML_PATH_ACTIONS = 'admin_logger/general/logged_actions';
    const XML_PATH_ENTITIES = 'admin_logger/general/logged_entities';

    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function getRetentionPeriod()
    {
       return (int) $this->scopeConfig->getValue(self::XML_PATH_RETENTION, ScopeInterface::SCOPE_STORE);
    }

    public function getLoggedActionTypes()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_ACTIONS, ScopeInterface::SCOPE_STORE);
        return $value ? explode(',', $value) : [];
    }

    public function getLoggedEntities()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_ENTITIES, ScopeInterface::SCOPE_STORE);
        return $value ? explode(',', $value) : [];
    }
}
