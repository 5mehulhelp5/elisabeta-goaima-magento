<?php

namespace MyCompany\SimplePay\Gateway\Validator;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;
use Magento\Store\Model\ScopeInterface;

class CustomerGroupValidator extends AbstractValidator
{
    protected $scopeConfig;
    protected $customerRepository;

    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ScopeConfigInterface $scopeConfig,
        CustomerRepositoryInterface $customerRepository
    ) {
        parent::__construct($resultFactory);
        $this->scopeConfig = $scopeConfig;
        $this->customerRepository = $customerRepository;
    }

    public function validate(array $validationSubject): ResultInterface
    {
        $allowedGroups = $this->scopeConfig->getValue(
            'payment/simplepay/specific_groups',
            ScopeInterface::SCOPE_STORE
        );

        if (!$allowedGroups) {
            return $this->createResult(true);
        }

        $allowedGroups = explode(',', $allowedGroups);

        if (!isset($validationSubject['payment']) || !$validationSubject['payment'] instanceof PaymentDataObjectInterface) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $paymentDO = $validationSubject['payment'];
        $orderAdapter = $paymentDO->getOrder();

        $customerGroupId = Group::NOT_LOGGED_IN_ID;

        $customerId = $orderAdapter->getCustomerId();

        if ($customerId) {
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customerGroupId = $customer->getGroupId();
            } catch (\Exception $e) {
                $customerGroupId = Group::NOT_LOGGED_IN_ID;
            }
        }

        $isValid = in_array($customerGroupId, $allowedGroups);

        return $this->createResult(
            $isValid,
            $isValid ? [] : [__('This payment method is not available for your customer group.')]
        );
    }
}
