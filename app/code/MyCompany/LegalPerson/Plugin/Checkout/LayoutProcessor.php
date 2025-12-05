<?php

namespace MyCompany\LegalPerson\Plugin\Checkout;

use MyCompany\LegalPerson\Helper\Data;

class LayoutProcessor
{
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $jsLayout)
    {
        if (!$this->helper->isEnabled()) {
            return $jsLayout;
        }

        // 1. Adaugam campurile in SHIPPING Address
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {

            $shippingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $shippingCustomFields = $this->getFields('shippingAddress.custom_attributes', 'shippingAddress.custom_attributes');

            foreach ($shippingCustomFields as $code => $field) {
                $shippingFields[$code] = $field;
            }
        }

        // 2. Adaugam campurile in BILLING Address
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])) {

            $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                // Verificare de siguranta pentru PHP 8
                if (!isset($paymentMethodValue['children']['form-fields']['children'])) {
                    continue;
                }

                $paymentCode = str_replace('-form', '', $paymentMethodForm);
                $scope = 'billingAddress' . $paymentCode . '.custom_attributes';
                $billingFields = $this->getFields($scope, $scope);

                foreach ($billingFields as $code => $field) {
                    $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                    ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']['form-fields']['children'][$code] = $field;
                }
            }
        }

        return $jsLayout;
    }

    protected function getFields($dataScopePrefix, $customScope)
    {
        return [
            'is_legal_checkbox' => [
                'component' => 'MyCompany_LegalPerson/js/form/element/legal-checkbox',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'id' => 'is_legal_checkbox',
                    'description' => 'Persoana Juridica',
                ],
                'dataScope' => $dataScopePrefix . '.is_legal_checkbox',
                'label' => '',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 0,
                'id' => 'is_legal_checkbox'
            ],
            'legal_company' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => $dataScopePrefix . '.legal_company',
                'label' => 'Nume Companie',
                'provider' => 'checkoutProvider',
                'visible' => false,
                'validation' => [
                    'required-entry' => true,
                    'min_text_length' => 3
                ],
                'sortOrder' => 40,
            ],
            'legal_cui' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => $dataScopePrefix . '.legal_cui',
                'label' => 'CUI',
                'provider' => 'checkoutProvider',
                'visible' => false,
                'validation' => [
                    'required-entry' => true
                ],
                'sortOrder' => 41,
            ]
        ];
    }
}
