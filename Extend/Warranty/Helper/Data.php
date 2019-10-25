<?php

namespace Extend\Warranty\Helper;

class Data
{
    const NOT_ALLOWED_TYPES = [\Extend\Warranty\Model\Product\Type::TYPE_CODE];

    public function formatPrice($price): float
    {
        if  (empty($price)) {
            return 0;
        }

        $floatPrice = (float) $price;

        $formattedPrice = number_format(
            $floatPrice,
            2,
            '',
            ''
        );

        return (float) $formattedPrice;
    }

    public function removeFormatPrice(int $price): float
    {
        $price = (string)$price;

        $price = substr_replace(
            $price,
            '.',
            strlen($price) - 2,
            0
        );

        return (float) $price;
    }
}