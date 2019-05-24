<?php

namespace Extend\Warranty\Block\Adminhtml;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend;
use \Magento\Catalog\Block\Adminhtml\Product as SuperBlock;

class Product extends SuperBlock
{
    protected function _getAddProductButtonOptions()
    {
        /* var Array $arrOfNotAlowedTypes */
        $arrOfNotAlowedTypesIds = [\Extend\Warranty\Model\Product\Type::TYPE_CODE];
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            if(!in_array($typeId,$arrOfNotAlowedTypesIds)) {
                $splitButtonOptions[$typeId] = [
                    'label' => __($type['label']),
                    'onclick' => "setLocation('" . $this->_getProductCreateUrl($typeId) . "')",
                    'default' => \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE == $typeId,
                ];
            }
        }

        return $splitButtonOptions;
    }
}