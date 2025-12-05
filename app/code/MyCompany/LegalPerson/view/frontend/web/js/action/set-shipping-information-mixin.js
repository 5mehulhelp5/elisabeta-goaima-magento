define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    /**
     * Funcție auxiliară pentru a transforma City din Array în String
     */
    function normalizeCity(address) {
        if (address && address.city && Array.isArray(address.city)) {
            address.city = address.city.length ? address.city[0] : '';
        }
    }

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

            console.log('Shipping City cleaned:', shippingAddress.city);
            if (billingAddress) {
                console.log('Billing City cleaned:', billingAddress.city);
            }

            quote.shippingAddress(shippingAddress);

            return originalAction();
        });
    };
});
