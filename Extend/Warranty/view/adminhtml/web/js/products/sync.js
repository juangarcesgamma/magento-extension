define(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'mage/translate'
    ],
    function($, alert, $t) {
        'use strict';

        function restore(button,msg,cancel) {
            button.text('Sync Products');
            button.attr("disabled", false);
            msg.show();
            cancel.hide();
        }

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
                var synMsg = $("#sync-msg");
                var cancelSync = $("#cancel_sync");

                button.text('Sync in progress...');
                button.attr("disabled", true);

                synMsg.hide();
                cancelSync.show();


                var ajaxCall = $.get({
                    url : this.options.url
                })
                    .done(function(data) {
                        restore(button,synMsg,cancelSync);
                        $("#sync-time").text(data.msg);
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        restore(button,synMsg,cancelSync);
                        if(jqXHR.aborted){
                            return;
                        }
                    });

                $(cancelSync).bind( "click", function() {
                    ajaxCall.abort();
                });
            },


        });

        return $.extend.productSync;
    });