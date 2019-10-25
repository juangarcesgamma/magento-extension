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
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\App\Filesystem\DirectoryList;


class InstallData implements InstallDataInterface
{
    protected $productFactory;
    protected $eavSetupFactory;
    protected $state;
    protected $storeManager;
    protected $file;
    protected $reader;
    protected $directoryList;

    public function __construct
    (
        ProductFactory $productFactory,
        EavSetupFactory $eavSetupFactory,
        State $state,
        StoreManagerInterface $storeManager,
        File $file,
        Reader $reader,
        DirectoryList $directoryList
    )
    {
        $this->productFactory = $productFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->file = $file;
        $this->reader = $reader;
        $this->directoryList = $directoryList;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        //ADD WARRANTY PRODUCT TO THE DB
        $this->addImageToPubMedia();
        $this->createWarrantyProduct();

        $setup->startSetup();

        $eavSetup = $this->eavSetupFactory->create();

        $this->addSyncAttribute($eavSetup);

        $this->enablePriceForWarrantyProducts($eavSetup);

        $setup->endSetup();
    }

    public function addImageToPubMedia()
    {
        $imagePath = $this->reader->getModuleDir(\Magento\Framework\Module\Dir::MODULE_VIEW_DIR, 'Extend_Warranty');
        $imagePath .= '/../Setup/Resource/Extend_icon.png';

        $media = $this->directoryList->getPath('media');
        $media .= '/Extend_icon.png';

        $this->file->cp($imagePath, $media);
    }

    public function createWarrantyProduct()
    {
        $warranty = $this->productFactory->create();

        $websites = $this->storeManager->getWebsites();

        $warranty->setSku('WARRANTY-1')
            ->setName('Extend Protection Plan')
            ->setWebsiteIds(array_keys($websites))
            ->setAttributeSetId(4) //Default
            ->setStatus(1) //Enable
            ->setVisibility(1) //Not visible individually
            ->setTaxClassId(0) //None
            ->setTypeId(Type::TYPE_CODE)
            ->setPrice(0)
            ->setStockData([
                'use_config_manage_stock' => 0,
                'is_in_stock' => 1,
                'qty' => 1,
                'manage_stock' => 0,
                'use_config_notify_stock_qty' => 0
            ]);

        $imagePath = 'Extend_icon.png';
        $warranty->addImageToMediaGallery($imagePath, array('image', 'small_image', 'thumbnail'), false, false);
        $warranty->save();
    }

    public function addSyncAttribute($eavSetup)
    {
        //ADD SYNCED DATE ATTRIBUTE FOR PRODUCTS
        $eavSetup->addAttribute(
            Product::ENTITY,
            'extend_sync',
            [
                'type' => 'int',
                'label' => 'Synced with Extend',
                'input' => 'boolean',
                'required' => false,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => false,
                'apply_to' => 'simple,virtual,configurable,downloadable,bundle'
            ]
        );
    }

    public function enablePriceForWarrantyProducts($eavSetup)
    {
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