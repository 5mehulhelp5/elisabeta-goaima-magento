<?php

namespace MyCompany\LegalPerson\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

//    from system.xml section id / group id / field id separated by /
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            'legalperson_config/general/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }
}
