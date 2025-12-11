define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    function normalizeCity(address) {
        if (address && address.city && Array.isArray(address.city)) {
            address.city = address.city.length ? address.city[0] : '';
        }
    }

    // we ensure that when the customer proceeds from the shipping step to the payment step that the request to backend has the custom data
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            var billingAddress = quote.billingAddress();

            if (!shippingAddress['extension_attributes']) {
                shippingAddress['extension_attributes'] = {};
            }

            var cui = null;
            var company = null;
            var cuiInput = $('input[name*="legal_cui"]');
            var companyInput = $('input[name*="legal_company"]');

            if (cuiInput.length) cui = cuiInput.val();
            if (companyInput.length) company = companyInput.val();

            if (!cui && shippingAddress['custom_attributes'] && shippingAddress['custom_attributes']['legal_cui']) {
                var attrCui = shippingAddress['custom_attributes']['legal_cui'];
                cui = (typeof attrCui === 'object' && attrCui.value) ? attrCui.value : attrCui;
            }
            if (!company && shippingAddress['custom_attributes'] && shippingAddress['custom_attributes']['legal_company']) {
                var attrComp = shippingAddress['custom_attributes']['legal_company'];
                company = (typeof attrComp === 'object' && attrComp.value) ? attrComp.value : attrComp;
            }

            if (cui) shippingAddress['extension_attributes']['legal_cui'] = cui;
            if (company) shippingAddress['extension_attributes']['legal_company'] = company;

            var streetNumber = $('input[name*="street_number"]').val();
            var building = $('input[name*="building"]').val();
            var floor = $('input[name*="floor"]').val();
            var apartment = $('input[name*="apartment"]').val();

            if(streetNumber) shippingAddress['extension_attributes']['street_number'] = streetNumber;
            if(building) shippingAddress['extension_attributes']['building'] = building;
            if(floor) shippingAddress['extension_attributes']['floor'] = floor;
            if(apartment) shippingAddress['extension_attributes']['apartment'] = apartment;

            normalizeCity(shippingAddress);

            if (billingAddress) {
                normalizeCity(billingAddress);
            }

            quote.shippingAddress(shippingAddress);

            return originalAction();
        });
    };
});
