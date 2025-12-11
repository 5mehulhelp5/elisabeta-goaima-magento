<?php

namespace MyCompany\SimplePay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = "simplepay";

    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig){
        $this->scopeConfig = $scopeConfig;
    }


    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'instructions' => $this->scopeConfig->getValue(
                        'payment/simplepay/instructions',
                        ScopeInterface::SCOPE_STORE
                    ),
                ]
            ]
        ];
    }
}
