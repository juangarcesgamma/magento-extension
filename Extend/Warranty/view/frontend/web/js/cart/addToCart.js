define(['jquery'],
    function ($) {

        return function (param) {
            Extend.buttons.render('#extend-offer-' + param.itemId, {
                referenceId: param.productSku,
            }, (error, instance) => {
                if (instance.getActiveProduct().name !== undefined) {
                    document.getElementById("warranty-" + param.itemId).removeAttribute("hidden");
                } else {
                    $("warranty-" + param.itemId).remove();
                }
            });

            $('#add-warranty').click(() => {
                event.preventDefault();

                const component = Extend.buttons.instance('#extend-offer-' + param.itemId);

                const plan = component.getPlanSelection();

                if (plan) {
                    plan.product = param.productSku;
                    $.post(param.url, {
                        warranty: plan
                    })
                        .done(function (data) {
                            location.reload(false);
                        });
                }
            });
        }
    });