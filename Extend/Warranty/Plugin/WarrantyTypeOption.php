<?php


namespace Extend\Warranty\Plugin;

use \Magento\Catalog\Model\Product\Type;

class WarrantyTypeOption
{
    public function afterGetOptionArray(Type $subject, $result)
    {
        $warrantyKey = array_key_exists(\Extend\Warranty\Model\Product\Type::TYPE_CODE, $result);
        if($warrantyKey !== false)
        {
            unset($result[\Extend\Warranty\Model\Product\Type::TYPE_CODE]);
        }
        return $result;
    }
}