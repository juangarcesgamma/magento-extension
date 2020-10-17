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
                let sku = selectedProduct();

                if(sku !== ''){
                    renderWarranties(sku);
                }
            });
        });

        function match(attributes, selected_options) {
            return _.isEqual(attributes, selected_options);
        }

        function selectedProduct() {
            let selected_options = {};
			
			//Added custom code to be able to select products on diferent Magento Versions (A. Figueroa 2020-10-16)
			let option_parent = $('div.product-options-wrapper').parent();
			console.log(document.getElementsByName("item"));
			console.log(document.getElementsByName("item")[0].value);
			if(document.getElementsByName("item").value !== ''){
				const productConfig = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig;
				return productConfig.skus[document.getElementsByName('item')[0].value];
			}else{
				//Original code A. Figueroa 2020-10-16
				let options = $('div.swatch-attribute');
				options.each((index, value) => {
					let attribute_id = $(value).attr('attribute-id');
					let option_selected = $(value).attr('option-selected');
					if (!attribute_id || !option_selected) {
						return '';
					}
					selected_options[attribute_id] = option_selected;
				});

				const productConfig = $('[data-role=swatch-options]').data('mageSwatchRenderer').options.jsonConfig;

				for (let [productId, attributes] of Object.entries(productConfig.index)) {
					if (match(attributes, selected_options)) {
						return productConfig.skus[productId];
					}
				}
			}
			//End of custom code
        }

        function renderWarranties(productSku){
            const component = Extend.buttons.instance('#extend-offer');
            component.setActiveProduct(productSku);
        }

        $('#product-addtocart-button').click((event) => {
            event.preventDefault();

            /** get the component instance rendered previously */
            const component = Extend.buttons.instance('#extend-offer');
            /** get the users plan selection */
            const plan = component.getPlanSelection();

            let sku = params.productSku !== '' ? params.productSku : selectedProduct();

            if (plan) {
                addWarranty(plan, sku);
                $('#product_addtocart_form').submit();
            } else {
                Extend.modal.open({
                    referenceId: sku,
                    onClose: function (plan) {
                        if (plan) {
                            addWarranty(plan,sku)
                        } else {
                            $("input[name^='warranty']").remove();
                        }
                        $('#product_addtocart_form').submit();
                    }
                });
            }

        });

        function addWarranty(plan, sku){

            $.each(plan, (attribute, value) => {
                $('<input />').attr('type', 'hidden')
                    .attr('name', 'warranty[' + attribute + ']')
                    .attr('value', value)
                    .appendTo('#product_addtocart_form');
            });

            $('<input />').attr('type', 'hidden')
                .attr('name', 'warranty[product]')
                .attr('value', sku)
                .appendTo('#product_addtocart_form');
        }

    };
});