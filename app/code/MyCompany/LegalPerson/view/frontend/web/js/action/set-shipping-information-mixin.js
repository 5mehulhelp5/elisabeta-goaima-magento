define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (!shippingAddress['extension_attributes']) {
                shippingAddress['extension_attributes'] = {};
            }

            var cui = null;
            var company = null;

            // 1. Cautam in input-urile vizibile (DOM) - Cea mai sigura metoda pentru Incognito/Manual
            var cuiInput = $('input[name*="legal_cui"]');
            var companyInput = $('input[name*="legal_company"]');

            if (cuiInput.length) cui = cuiInput.val();
            if (companyInput.length) company = companyInput.val();

            // 2. Fallback: Cautam in obiectul existent (custom_attributes)
            if (!cui && shippingAddress['custom_attributes'] && shippingAddress['custom_attributes']['legal_cui']) {
                var attr = shippingAddress['custom_attributes']['legal_cui'];
                cui = (typeof attr === 'object' && attr.value) ? attr.value : attr;
            }
            if (!company && shippingAddress['custom_attributes'] && shippingAddress['custom_attributes']['legal_company']) {
                var attr = shippingAddress['custom_attributes']['legal_company'];
                company = (typeof attr === 'object' && attr.value) ? attr.value : attr;
            }

            // 3. Setam valorile
            if (cui) shippingAddress['extension_attributes']['legal_cui'] = cui;
            if (company) shippingAddress['extension_attributes']['legal_company'] = company;

            console.log('LegalPerson Mixin Data:', shippingAddress['extension_attributes']);

            // TRUC: Fortam actualizarea obiectului quote
            // Uneori modificarile "in-place" nu sunt detectate de Magento
            quote.shippingAddress(shippingAddress);

            return originalAction();
        });
    };
});
