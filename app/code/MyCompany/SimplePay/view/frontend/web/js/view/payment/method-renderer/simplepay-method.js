define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component){
        'use strict';

        return Component.extend({
            defaults: {
                template: 'MyCompany_SimplePay/payment/simplepay'
            },

            getInstructions: function (){
                return window.checkoutConfig.payment.simplepay.instructions;
            }

        })
    }

)
