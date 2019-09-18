<?php


namespace Extend\Warranty\Plugin;

use \Magento\Catalog\Model\Product\Type;

class WarrantyTypeOption
{
    public function afterGetOptionArray(Type $subject, $result)
    {
        if (isset($result[\Extend\Warranty\Model\Product\Type::TYPE_CODE])) {
            unset($result[\Extend\Warranty\Model\Product\Type::TYPE_CODE]);
        }

        return $result;
    }
}