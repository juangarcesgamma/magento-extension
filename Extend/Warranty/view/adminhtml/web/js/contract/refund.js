define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function ($, alert, $t) {
        'use strict';

        function refund(url, contractId) {
            event.preventDefault();

            $('body').trigger('processStart');

            console.log(url);

            $.get({
                showLoader: true,
                url: url,
                data: {
                    contractId: contractId
                }
            })
                .done(function (data) {
                    $('body').trigger('processStop');
                    alert({
                        title: $.mage.__("Refund complete!"),
                        content: $.mage.__("The refund was successful"),
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

                const url = this.options.url;
                const contractId = this.options.contractId;

                alert({
                    title: 'Are you sure?',
                    buttons: [{
                        text: $.mage.__('Yes'),
                        class: 'action primary accept',

                        click: function () {
                            this.closeModal(true);
                            refund(url, contractId);

                        }
                    }, {
                        text: $.mage.__('No'),
                        class: 'action',

                        click: function () {
                            this.closeModal(true);
                        }
                    }]
                });

            }
        });

        return $.extend.refundWarranty;
    });