<?php
/**
 * Created by PhpStorm.
 * User: lazaro
 * Date: 13/05/19
 * Time: 05:13 PM
 */

namespace Extend\Warranty\Model\Product;


use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product;

class Type extends AbstractType
{
    const TYPE_CODE = 'warranty';

    public function deleteTypeSpecificData(Product $product)
    {
        return;
    }

    public function isVirtual($product)
    {
        return true;
    }

    public function hasWeight()
    {
        return false;
    }
}