define(['jquery'],
    function ($) {

        return function (param) {
            Extend.buttons.renderSimpleOffer(
                '#extend-offer-' + param.itemId,
                {
                    referenceId: param.productSku,
                    onAddToCart: function (opts) {

                        const plan = opts.plan;

                        if (plan) {
                            plan.product = param.productSku;
                            $.post(param.url, {
                                warranty: plan,
                                option: param.parentId
                            })
                                .done(function (data) {
                                    location.reload(false);
                                });
                        }
                    }
                }
            );
        }
    });