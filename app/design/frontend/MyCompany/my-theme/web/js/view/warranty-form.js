define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/storage',
    'mage/url'
], function (Component, ko, $, storage, urlBuilder){
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Theme/warranty-form'
        },

        initialize: function (){
            this._super();

            this.orderId = ko.observable('');
            this.email = ko.observable('');
            this.description = ko.observable('');

            this.isSubmitted = ko.observable(false);
            this.successMessage = ko.observable('');
            this.errorMessage = ko.observable('');
        },

        submitForm: function (){
            let self = this;

            self.errorMessage('');

            if(!this.orderId || !this.email){
                self.errorMessage('Please fill in email and order id');
                return;
            }

            const payload = {
                order_id: this.orderId(),
                email: this.email(),
                description: this.description()

            };

            const serviceUrl = urlBuilder.build('warranty/index/submit');

            $('body').trigger('processStart');

            storage.post(
                serviceUrl,
                JSON.stringify(payload),
                true
            ).done(function (response){
                if(response.success){
                    self.successMessage(response.message);
                    self.isSubmitted(true);

                    self.orderId('');
                    self.email('');
                    self.description('');
                } else {
                    self.errorMessage(response.message || 'Error');
                }
            }).fail(function (response){
                self.errorMessage('Error. Retry');
            }).always(function (){
                $('body').trigger('processStop');
            })
        }
    });
});
