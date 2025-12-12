<?php
namespace MyCompany\OrderExport\Model\Export;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use MyCompany\OrderExport\Model\Export\Strategy\BuilderInterface;

class JsonDataBuilder
{
    const XML_PATH_FORMAT_VERSION = 'mycompany_export/sftp/format_version';

    /**
     * @var BuilderInterface[]
     */
    protected $strategies;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param array $strategies Injectate via di.xml
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $strategies = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->strategies = $strategies;
    }

    public function build(OrderInterface $order): array
    {
        // 1. Citim versiunea din Admin (ex: 'standard' sau 'detailed')
        $version = $this->scopeConfig->getValue(self::XML_PATH_FORMAT_VERSION);

        // 2. Verificăm dacă strategia există, altfel folosim una default
        if (!isset($this->strategies[$version])) {
            // Fallback la 'standard' dacă configurarea e greșită
            $version = 'standard';
        }

        if (!isset($this->strategies[$version])) {
            throw new \Exception("Export strategy '{$version}' not found.");
        }

        // 3. Delegăm execuția strategiei alese
        return $this->strategies[$version]->build($order);
    }
}
