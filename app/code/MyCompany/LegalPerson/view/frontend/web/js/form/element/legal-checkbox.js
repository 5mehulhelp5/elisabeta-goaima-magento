define([
    'Magento_Ui/js/form/element/boolean',
    'uiRegistry'
], function (Boolean, registry) {
    'use strict';

    return Boolean.extend({
        defaults: {
            listens: {
                'checked': 'onCheckedChanged'
            }
        },

        onCheckedChanged: function (checked) {
            var scope = this.parentName;
            var legalFields = ['legal_company', 'legal_cui'];

            var standardFields = ['firstname', 'lastname', 'company'];

            legalFields.forEach(function (field) {
                registry.get(scope + '.' + field, function (component) {
                    component.visible(checked);
                    if (checked) {
                        component.required(true);
                    } else {
                        component.required(false);
                        component.value('');
                    }
                });
            });

            standardFields.forEach(function (field) {
                registry.get(scope + '.' + field, function (component) {
                    if (component) {
                        component.visible(!checked);

                        if (!checked && field === 'company') {
                        }
                    }
                });
            });

            if (checked) {
                this.setupAutoFill(scope);
            }
        },

        setupAutoFill: function(scope) {
            var self = this;
            registry.get(scope + '.legal_company', function (companyComponent) {
                companyComponent.on('value', function(value) {
                    if (self.value() === true) {
                        registry.get(scope + '.lastname', function(lastname) {
                            if(lastname) lastname.value(value);
                        });
                        registry.get(scope + '.firstname', function(firstname) {
                            if(firstname) firstname.value('Legal Entity');
                        });
                    }
                });
            });
        }
    });
});
