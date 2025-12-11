<?php
namespace MyCompany\LegalPerson\Plugin\Checkout;

use MyCompany\LegalPerson\Helper\Data;

// plugin class that modifies the structure of the checkout ui to integrate custom fields
// this plugin is hooked by frontend/di.xml
// we intercept the jsLayout (which is a json object which defines ui components) and before it is sent to the browser we add some fields
class LayoutProcessor
{
    protected $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    // this is going to be executed after the initial layout is built
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $subject, $jsLayout)
    {
        // if disabled we return the original layout
        if (!$this->helper->isEnabled()) {
            return $jsLayout;
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {

            $shippingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $this->addAddressDetails($shippingFields, 'shippingAddress.custom_attributes');

            $this->addLegalFields($shippingFields, 'shippingAddress.custom_attributes', 'shippingAddress.custom_attributes');

            $this->convertCityToSelect($shippingFields, 'shippingAddress');
        }

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])) {

            $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'];

            // we have to loop through all payments methods because the billing is nested under each payment form
            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                if (!isset($paymentMethodValue['children']['form-fields']['children'])) {
                    continue;
                }

                $billingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']['form-fields']['children'];

                $paymentCode = str_replace('-form', '', $paymentMethodForm);
                $dataScope = 'billingAddress' . $paymentCode . '.custom_attributes';
                $customScope = 'billingAddress' . $paymentCode . '.custom_attributes';

                $this->addAddressDetails($billingFields, $dataScope);

                $this->addLegalFields($billingFields, $dataScope, $customScope);

                $this->convertCityToSelect($billingFields, 'billingAddress' . $paymentCode);
            }
        }

        return $jsLayout;
    }

    protected function addLegalFields(&$fields, $dataScopePrefix, $customScope)
    {
        $legalFields = [
            'is_legal_checkbox' => [
                // view/frontend/web is implicitly prepended by magento
                'component' => 'MyCompany_LegalPerson/js/form/element/legal-checkbox',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'id' => 'is_legal_checkbox',
                    'description' => 'Legal Person',
                ],
                'dataScope' => $dataScopePrefix . '.is_legal_checkbox',
                'label' => '',
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => 0,
            ],
            'legal_company' => [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                ],
                'dataScope' => $dataScopePrefix . '.legal_company',
                'label' => 'Company Name',
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

        foreach ($legalFields as $code => $fieldConfig) {
            $fields[$code] = $fieldConfig;
        }
    }

    private function addAddressDetails(&$fields, $dataScopePrefix)
    {
        $newFields = [
            'street_number' => ['label' => 'Number', 'sortOrder' => 71],
            'building'      => ['label' => 'Building',  'sortOrder' => 72],
            'floor'         => ['label' => 'Floor',  'sortOrder' => 73],
            'apartment'     => ['label' => 'Ap',    'sortOrder' => 74],
        ];

        foreach ($newFields as $code => $config) {
            if (isset($fields[$code])) continue;

            $fields[$code] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $dataScopePrefix,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    'additionalClasses' => 'legal-address-short-field',
                ],
                'dataScope' => $dataScopePrefix . '.' . $code,
                'label' => $config['label'],
                'provider' => 'checkoutProvider',
                'visible' => true,
                'validation' => [],
                'sortOrder' => $config['sortOrder'],
            ];
        }
    }

    private function convertCityToSelect(&$fields, $scopePrefix)
    {
        if (isset($fields['city'])) {
            $fields['city']['component'] = 'MyCompany_LegalPerson/js/form/element/city-select';
            $fields['city']['config']['elementTmpl'] = 'ui/grid/filters/elements/ui-select';
            $fields['city']['config']['filterOptions'] = true;
            $fields['city']['sortOrder'] = 80;
            $fields['city']['dataType'] = 'string';
            $fields['city']['config']['additionalClasses'] = 'custom-city-dropdown';
        }
    }
}
