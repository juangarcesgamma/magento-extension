define([
    'jquery',
    'underscore'
], function ($, _) {

    return function (params) {

        Extend.buttons.render('#extend-offer', {
            referenceId: params.productSku
        });

        $(document).ready(function () {
            $('div.product-options-wrapper').click(() => {
                selectedProduct();
            });
        });

        function match(attributes, selected_options) {
            return _.isEqual(attributes, selected_options);
        }

        function selectedProduct() {
            let selected_options = {};

            let options = $('div.swatch-attribute');

            options.each((index, value) => {
                let attribute_id = $(value).attr('attribute-id');
                let option_selected = $(value).attr('option-selected');
                if (!attribute_id || !option_selected) {
                    return;
                }
                selected_options[attribute_id] = option_selected;
            });

            const productConfig = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig;

            for (let [productId, attributes] of Object.entries(productConfig.index)) {
                if (match(attributes, selected_options)) {
                    let sku = productConfig.skus[productId];

                    const component = Extend.buttons.instance('#extend-offer');
                    component.setActiveProduct(sku);
                }
            }
        }

    };
});