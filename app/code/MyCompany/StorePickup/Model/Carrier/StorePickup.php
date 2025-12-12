<?php

namespace MyCompany\StorePickup\Model\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;

class StorePickup extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'storepickup';

    protected $_isFixed = true;
    protected $rateResultFactory;
    protected $rateMethodFactory;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        array $data = []
    ) {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = $this->rateResultFactory->create();
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($this->_code);
        $method->setMethodTitle('Pickup');

        $subtotal = $request->getBaseSubtotalWithDiscount();
        $threshold = $this->getConfigData('free_shipping_subtotal');
        $shippingPrice = $this->getConfigData('price');

        if ($threshold && $subtotal >= $threshold) {
            $shippingPrice = 0.00;
        }

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);

        $result->append($method);

        return $result;


    }
    public function isTrackingAvailable()
    {
        // TODO: Implement isTrackingAvailable() method.
    }

    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('title')];
    }

    public function isValid($value)
    {
        // TODO: Implement isValid() method.
    }
}
