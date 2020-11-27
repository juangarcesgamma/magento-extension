define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'Magento_Ui/js/modal/modal',
        'mage/translate'
    ],
    function ($, alert, modal, $t) {
        'use strict';

        function refund(url, contractId, itemId) {
            event.preventDefault();

            $('body').trigger('processStart');

            $.post(url,{
                contractId: contractId,
                itemId: itemId
            })
                .done(function (data) {
                    $('body').trigger('processStop');
                    alert({
                        title: $.mage.__('Refund Successful'),
                        content: $.mage.__('The request was successfully complete.'),
                        actions: {
                            always: function(){
                                location.reload();
                            }
                        },
                        modalClass: 'extend-refund-success'
                    });
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    $('body').trigger('processStop');
                    alert({
                        title: $.mage.__("Refund failed"),
                        content: $.mage.__("An unexpected error, please try again later."),
                    });
                });
        }

        $.widget('extend.refundWarranty', {
            options: {
                url: '',
                contractId: '',
                itemId: '',
                isPartial: '',
                maxRefunds: ''
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
                const itemId = this.options.itemId;

                const isPartial = String(this.options.isPartial);
                const maxRefunds = this.options.maxRefunds;

                if (isPartial) {
                    $("#partial_max_span").text(maxRefunds);
                    $("#partial_max_input").attr({
                        "max" : maxRefunds,
                    });

                    var modalOptions = {
                        modalClass: 'extend-confirm-partial-modal',
                        buttons: [{
                            text: 'Ok',
                            class: 'extend-partial-confirm',
                            click: function() {
                                var selectedRefunds = $("#partial_max_input").val();

                                if (selectedRefunds >= 1 && selectedRefunds <= maxRefunds) {
                                    var reducedContractId = Object.fromEntries(
                                        Object.entries(contractId).slice(0, selectedRefunds)
                                    );
                                    refund(url, reducedContractId, itemId);
                                }
                                this.closeModal();
                                $("#partial_max_span").text('');
                            }
                        }]
                    };
                    var confirmModal = modal(modalOptions, $('#popup-modal-partial'));
                    $('#popup-modal-partial').modal("openModal");
                } else {
                    var modalOptions = {
                        modalClass: 'extend-confirm-modal',
                        buttons: [{
                            text: 'Ok',
                            class: 'extend-confirm',
                            click: function() {
                                refund(url, contractId, itemId);
                                this.closeModal();
                            }
                        }]
                    };
                    var confirmModal = modal(modalOptions, $('#popup-modal'));
                    $('#popup-modal').modal("openModal");
                }

            }
        });



        return $.extend.refundWarranty;
    });