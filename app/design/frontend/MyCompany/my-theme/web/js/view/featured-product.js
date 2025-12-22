define([
    'uiComponent',
    'ko',
    'jquery',
    'mage/url',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'jquery/jquery.cookie'
], function (Component, ko, $, urlBuilder, storage, customerData) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Theme/featured-product'
        },

        initialize: function () {
            this._super();
            var data = this.productConfig;

            this.productName = data.name;
            this.variants = data.variants;

            this.currentImage = ko.observable(data.base_image);
            this.currentPrice = ko.observable(0);
            this.selectedVariantId = ko.observable();

            this.buttonLabel = ko.observable('Add to Cart');
            this.isProcessing = ko.observable(false);

            if (this.variants.length > 0) {
                this.selectVariant(this.variants[0]);
            }
        },

        onVariantChange: function (obj, event) {
            var selectedId = event.target.value;
            var variant = this.variants.find(function(v) {
                return v.id == selectedId;
            });

            if (variant) {
                this.selectVariant(variant);
            }
        },

        selectVariant: function (variant) {
            this.selectedVariantId(variant.id);
            this.currentPrice(parseFloat(variant.price).toFixed(2));

            if (variant.image && variant.image.indexOf('no_selection') === -1) {
                this.currentImage(variant.image);
            }
        },

        addToCart: function () {
            if (!this.selectedVariantId()) {
                alert('Please select a size!');
                return;
            }

            this.isProcessing(true);
            this.buttonLabel('Adding...');

            var self = this;
            var formUrl = urlBuilder.build('checkout/cart/add');

            $.ajax({
                url: formUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    product: this.selectedVariantId(),
                    qty: 1,
                    form_key: $.cookie('form_key')
                },
                success: function (res) {
                    self.isProcessing(false);
                    self.buttonLabel('Added! ✔');

                    customerData.reload(['cart'], true);
                    customerData.invalidate(['messages']);

                    setTimeout(function() {
                        self.buttonLabel('Add to Cart');
                    }, 2000);
                },
                error: function (xhr) {
                    self.isProcessing(false);
                    self.buttonLabel('Error');
                    console.error('AJAX Add to cart error:', xhr);

                    alert('Could not add product to cart. Please try again.');
                }
            });
        }
    });
});
