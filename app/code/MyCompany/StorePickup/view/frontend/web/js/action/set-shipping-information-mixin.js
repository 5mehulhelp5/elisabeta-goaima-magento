define([
    'mage/utils/wrapper',
    'MyCompany_StorePickup/js/model/pickup-state',
    'Magento_Checkout/js/model/quote'
], function (wrapper, pickupState, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            var method = quote.shippingMethod();
            if (method && method.carrier_code === 'storepickup') {
                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }

                shippingAddress['extension_attributes']['pickup_store'] = pickupState.pickupStore();
                shippingAddress['extension_attributes']['pickup_time'] = pickupState.pickupTime();
            }

            return originalAction();
        });
    };
});
