<?php

namespace Extend\Warranty\Block\Adminhtml;

use Magento\Catalog\Block\Adminhtml\Product as SuperBlock;
use Extend\Warranty\Helper\Data;

class Product extends SuperBlock
{
    /**
     * @var \Extend\Warranty\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Catalog\Model\Product\TypeFactory $typeFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $typeFactory, $productFactory, $data);
        $this->helper = $helper;

    }

    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo)
            {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );

        foreach ($types as $typeId => $type) {
            if(!in_array($typeId, $this->helper::NOT_ALLOWED_TYPES)) {
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