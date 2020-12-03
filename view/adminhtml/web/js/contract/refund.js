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

                const url        = this.options.url;
                const contractId = this.options.contractId;
                const itemId     = this.options.itemId;
                const isPartial  = String(this.options.isPartial);

                if (isPartial) {
                    $("div#partial-contracts-list").html('');

                    for (const property in contractId) {
                        if (contractId[property]) {
                            let contractItem = '<input type="checkbox" id="pl-contract' + property + '" name="pl-contract' + property + '" value="' + contractId[property] + '">' +
                                '<label for="pl-contract' + property +'">' + contractId[property] + '</label><br>';
                            $("div#partial-contracts-list").append(contractItem);
                        }
                    }

                    let modalOptions = {
                        modalClass: 'extend-confirm-partial-modal',
                        buttons: [{
                            text: 'Ok',
                            class: 'extend-partial-confirm',
                            click: function() {
                                let selectedRefundsArr = [];
                                $.each($("input[name^='pl-contract']:checked"), function(){
                                    selectedRefundsArr.push($(this).val());
                                });

                                let selectedRefundsObj = Object.assign({}, selectedRefundsArr);
                                this.closeModal();

                                if (selectedRefundsArr.length >= 1) {
                                    let confirmationModalOptions = {
                                        modalClass: 'extend-confirm-modal',
                                        buttons: [{
                                            text: 'Ok',
                                            class: 'extend-confirm',
                                            click: function() {
                                                refund(url, selectedRefundsObj, itemId);
                                                this.closeModal();
                                            }
                                        }]
                                    };
                                    let confirmModal = modal(confirmationModalOptions, $('#popup-modal'));
                                    $('#popup-modal').modal("openModal");
                                }
                            }
                        }]
                    };
                    let confirmModal = modal(modalOptions, $('#popup-modal-partial'));
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
                    let confirmModal = modal(modalOptions, $('#popup-modal'));
                    $('#popup-modal').modal("openModal");
                }

            }
        });

        return $.extend.refundWarranty;
    });