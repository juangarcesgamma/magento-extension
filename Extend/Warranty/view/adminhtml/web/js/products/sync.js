define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function($, alert, $t) {
        'use strict';

        $.widget('extend.productSync', {
            options: {
                url: ''
            },

            _create: function() {
                this._super();
                this._bind();
            },

            _bind: function() {
                $(this.element).click(this.syncProducts.bind(this));
            },

            syncProducts: function(event) {
                event.preventDefault();
                var button =  $(this.element);
                button.text('Sync in progress...');
                button.attr("disabled", true);

                $.get({
                    url : this.options.url
                })
                    .done(function(data) {
                        button.text('Sync Products');
                        button.attr("disabled", false);
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        button.text('Sync Products');
                        button.attr("disabled", false);
                    });
            }
        });

        return $.extend.productSync;
    });