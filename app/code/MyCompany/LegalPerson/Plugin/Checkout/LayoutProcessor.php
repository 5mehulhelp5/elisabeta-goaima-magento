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

        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'])) {

            $shippingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

            $this->addAddressDetails($shippingFields, 'shippingAddress.custom_attributes');

            $this->convertCityToSelect($shippingFields, 'shippingAddress');
        }

        // 2. Procesare Adresa de Facturare (Billing Address) pentru fiecare metoda de plata
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'])) {

            $paymentForms = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'];

            foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                // Verificam daca metoda de plata are formular de adresa (unele nu au)
                if (!isset($paymentMethodValue['children']['form-fields']['children'])) {
                    continue;
                }

                $billingFields = &$jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$paymentMethodForm]['children']['form-fields']['children'];

                $paymentCode = str_replace('-form', '', $paymentMethodForm);
                $scope = 'billingAddress' . $paymentCode . '.custom_attributes';

                $this->addAddressDetails($billingFields, $scope);

                $this->convertCityToSelect($billingFields, 'billingAddress' . $paymentCode);
            }
        }

        return $jsLayout;
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

    private function addAddressDetails(&$fields, $dataScopePrefix)
    {
        $newFields = [
            'street_number' => ['label' => 'Numar', 'sortOrder' => 71],
            'building'      => ['label' => 'Bloc',  'sortOrder' => 72],
            'floor'         => ['label' => 'Etaj',  'sortOrder' => 73],
            'apartment'     => ['label' => 'Ap',    'sortOrder' => 74], // Label mai scurt
        ];

        foreach ($newFields as $code => $config) {
            $fields[$code] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $dataScopePrefix,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input',
                    // --- MODIFICARE AICI: Adăugăm o clasă CSS comună ---
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
}
