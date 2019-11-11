<?php

namespace Extend\Warranty\Plugin\CustomerData;

use Magento\Checkout\CustomerData\AbstractItem as SuperAbstractItem;
use Magento\Quote\Model\Quote\Item;

class AbstractItem
{
    public function afterGetItemData(SuperAbstractItem $subject, $result, Item $item)
    {
        if ($result['product_type'] === 'warranty') {
            $result['product_image']['isWarranty'] = true;
        } else {
            $result['product_image']['isWarranty'] = false;
        }

        return $result;
    }
}