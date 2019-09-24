define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function ($, alert, $t) {
        'use strict';

        $.widget('extend.refundWarranty', {
            options: {
                url: '',
                contractId: ''
            },

            _create: function () {
                this._super();
                this._bind();
            },

            _bind: function () {
                $(this.element).click(this.refundWarranty.bind(this));
            },
            refundWarranty: function (event) {
                event.preventDefault();

                $('body').trigger('processStart');

                $.get({
                    showLoader: true,
                    url: this.options.url,
                    data: {
                        contractId: this.options.contractId
                    }
                })
                    .done(function (data) {
                        $('body').trigger('processStop');
                        alert({
                            title: $.mage.__("Refund complete!"),
                            content: $.mage.__("The refund was successfully"),
                        });
                    })
                    .fail(function (jqXHR, textStatus, errorThrown) {
                        $('body').trigger('processStop');
                        alert({
                            title: $.mage.__("Refund failed"),
                            content: $.mage.__("An unexpected error, please try again later"),
                        });
                    });



            }
        });

        return $.extend.refundWarranty;
    });