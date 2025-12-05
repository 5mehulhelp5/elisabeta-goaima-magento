<?php

namespace MyCompany\LegalPerson\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'legalperson_config/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}
