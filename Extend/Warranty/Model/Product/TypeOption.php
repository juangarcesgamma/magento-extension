<?php


namespace Extend\Warranty\Model\Product;


class TypeOption extends \Magento\Catalog\Model\Product\Type
{
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            if($typeId != 'warranty') {
                $options[$typeId] = (string)$type['label'];
            }
        }
        return $options;
    }
}