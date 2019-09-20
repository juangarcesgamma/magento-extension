<?php


namespace Extend\Warranty\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\ProductFactory;
use Extend\Warranty\Model\Product\Type;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;


class InstallData implements InstallDataInterface
{
    protected $productFactory;
    protected $eavSetupFactory;
    protected $state;
    protected $storeManager;

    public function __construct
    (
        ProductFactory $productFactory,
        EavSetupFactory $eavSetupFactory,
        State $state,
        StoreManagerInterface $storeManager
    )
    {
        $this->productFactory = $productFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->state = $state;
        $this->storeManager = $storeManager;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        //ADD WARRANTY PRODUCT TO THE DB
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $warranty = $this->productFactory->create();

        $websites = $this->storeManager->getWebsites();

        $warranty->setSku('WARRANTY-1')
            ->setName('Extend Warranty')
            ->setWebsiteIds(array_keys($websites))
            ->setAttributeSetId(4) //Default
            ->setStatus(1) //Enable
            ->setVisibility(1) //Not visible individually
            ->setTaxClassId(0) //None
            ->setTypeId(Type::TYPE_CODE)
            ->setPrice(0)
            ->setStockData([
                    'manage_stock' => 0,
                    'qty' => 1
                ]);

        $warranty->save();

        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create();

        //ADD SYNCED DATE ATTRIBUTE FOR PRODUCTS
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
                'visible' => true,
                'apply_to'=> 'simple,virtual,configurable,downloadable,bundle'
            ]
        );

        //MAKE PRICE ATTRIBUTE AVAILABLE FOR WARRANTY PRODUCT TYPE
        $field = 'price';

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
}