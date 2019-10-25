<?php


namespace Extend\Warranty\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ProductFactory;
use Extend\Warranty\Model\Product\Type;
use Magento\Framework\App\State;

class AddWarrantyProductPatch implements DataPatchInterface
{
    protected $productFactory;
    protected $state;

    public function __construct
    (
        ProductFactory $productFactory,
        State $state
    )
    {
        $this->productFactory = $productFactory;
        $this->state = $state;
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
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $warranty = $this->productFactory->create();

        $warranty->setSku('WARRANTY-1')
            ->setName('Extend Warranty')
            ->setAttributeSetId(4) //Default
            ->setStatus(1) //Enable
            ->setVisibility(1) //Not visible individually
            ->setTaxClassId(0) //None
            ->setTypeId(Type::TYPE_CODE)
            ->setPrice(0)
            ->setStockData([
                'use_config_manage_stock' => 0,
                'manage_stock' => 1,
                'is_in_stock' => 1,
                'qty' => 1
            ]
        );

        $warranty->save();

        return $this;
    }
}