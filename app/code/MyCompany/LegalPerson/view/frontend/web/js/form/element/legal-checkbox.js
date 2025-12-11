// with requirejs we list the dependencies
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

        // hides and hides the inputs
        onCheckedChanged: function (checked) {

            // gets the dataScope
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
                    }
                });
            });
        }
    });
});
