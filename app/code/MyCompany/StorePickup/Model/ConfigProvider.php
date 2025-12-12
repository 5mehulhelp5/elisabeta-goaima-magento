<?php
namespace MyCompany\StorePickup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider implements ConfigProviderInterface
{
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    // the array returned will be merged into window.checkoutConfig
    public function getConfig()
    {
        $stores = $this->scopeConfig->getValue('carriers/storepickup/stores');
        $slots = $this->scopeConfig->getValue('carriers/storepickup/time_slots');

        $storeList = $stores ? preg_split('/\r\n|\r|\n/', $stores) : [];
        $slotList = $slots ? preg_split('/\r\n|\r|\n/', $slots) : [];

        return [
            'shipping' => [
                'storepickup' => [
                    'stores' => array_filter($storeList),
                    'slots' => array_filter($slotList)
                ]
            ]
        ];
    }
}
