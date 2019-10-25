<?php

namespace Extend\Warranty\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;

class CustomAttributeLastSyncPatch implements DataPatchInterface
{
    protected $setup;
    protected $eavSetupFactory;

    public function __construct
    (
        ModuleDataSetupInterface $setup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->setup = $setup;
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
        $this->setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create();

        $eavSetup->addAttribute(
            Product::ENTITY,
            'product_synced_date',
            [
                'type' => 'datetime',
                'label' => 'Is Product Synced',
                'input' => 'date',
                'required' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true
            ]
        );


        $this->setup->endSetup();

        return $this;
    }
}