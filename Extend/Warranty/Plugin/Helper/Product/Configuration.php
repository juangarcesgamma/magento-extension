<?php

namespace Extend\Warranty\Plugin\Helper\Product;

use Magento\Catalog\Helper\Product\Configuration as SuperConfiguration;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Extend\Warranty\Model\Product\Type as Warranty;

class Configuration
{
    public function aroundGetCustomOptions(SuperConfiguration $subject, \Closure $proceed, ItemInterface $item)
    {
        $product = $item->getProduct();
        $typeId = $product->getTypeId();
        if ($typeId == Warranty::TYPE_CODE) {
            $attributes = $product->getTypeInstance()->getWarrantyInfo($product);
            return array_merge($attributes, $proceed($item));
        }
        return $proceed($item);
    }
}