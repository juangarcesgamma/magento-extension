<?php

namespace Extend\Warranty\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Extend\Warranty\Model\Product\Type as WarrantyType;

class addPriceAndProductToWarrantyProductType implements DataPatchInterface
{

    protected $moduleDataSetup;
    protected $eavSetupFactory;

    public function __construct
    (
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        //associate these attributes with new product type
        $fieldList = [
            'price',
            'minimal_price',
            'cost',
            'tier_price'
        ];

        // make these attributes applicable to new product type
        foreach ($fieldList as $field) {
            $applyTo = explode(
                ',',
                $eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field, 'apply_to')
            );
            if (!in_array(\Extend\Warranty\Model\Product\Type::TYPE_CODE, $applyTo)) {
                $applyTo[] = \Extend\Warranty\Model\Product\Type::TYPE_CODE;
                $eavSetup->updateAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $field,
                    'apply_to',
                    implode(',', $applyTo)
                );
            }
        }

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'assocProduct',
            [
                'type' => 'int',
                'sort_order' => 50,
                'label' => 'Associated Product',
                'input' => 'text',
                'visible' => true,
                'required' => true,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to'=> WarrantyType::TYPE_CODE
            ]
        );

    }
}