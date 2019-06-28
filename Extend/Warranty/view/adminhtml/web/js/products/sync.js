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

                $.get({
                    url : this.options.url,
                    data: {
                        website: $('#website_switcher').val(),
                        store: $('#store_switcher').val()
                    }
                })
                    .done(function(data) {
                        button.text('Sync Products');
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        button.text('Sync Products');
                    });
            }
        });

        return $.extend.productSync;
    });