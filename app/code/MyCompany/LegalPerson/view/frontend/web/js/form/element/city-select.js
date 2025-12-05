define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'mage/url'
], function (Select, $, url) {
    'use strict';

    return Select.extend({
        defaults: {
            imports: {
                updateRegion: '${ $.parentName }.region_id:value',
                updateCountry: '${ $.parentName }.country_id:value'
            },
            filterOptions: true,
            chipsEnabled: false,
            disableLabel: true,
            multiple: false,
            isRo: false,
            currentRegionId: null
        },

        initialize: function () {
            this._super();

            var current = this.value();
            if (Array.isArray(current)) {
                this.value(current.length ? current[0] : '');
            }

            return this;
        },

        onUpdate: function (value) {
            if (Array.isArray(value)) {
                var stringValue = value.length ? value[0] : '';
                this.value(stringValue);
                return;
            }
            return this._super(value);
        },

        updateCountry: function (countryId) {
            this.isRo = (countryId === 'RO');
            if (this.isRo && this.currentRegionId) {
                this.fetchCities(this.currentRegionId);
            }
        },

        updateRegion: function (regionId) {
            this.currentRegionId = regionId;
            if (!this.isRo || !regionId) return;
            this.fetchCities(regionId);
        },

        fetchCities: function (regionId) {
            var self = this;
            $('body').trigger('processStart');

            $.ajax({
                url: url.build('legalperson/city/index'),
                data: { region_id: regionId },
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    self.options(data);

                    var current = self.value();
                    if (Array.isArray(current)) current = current.length ? current[0] : '';

                    var found = data.find(function(opt){ return opt.value === current; });
                    if (!found) self.value('');
                    else self.value(current);
                },
                complete: function () {
                    $('body').trigger('processStop');
                }
            });
        }
    });
});
