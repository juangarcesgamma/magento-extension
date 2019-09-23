<?php
/**
 * Created by PhpStorm.
 * User: lazaro
 * Date: 13/05/19
 * Time: 05:13 PM
 */

namespace Extend\Warranty\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Model\Product;
use Extend\Warranty\Helper\Data;

class Type extends AbstractType
{
    const TYPE_CODE = 'warranty';

    const WARRANTY_ID = 'warranty_id';
    const ASSOCIATED_PRODUCT = 'associated_product';

    /**
     * @var Data
     */
    protected $helper;

    public function __construct
    (
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    )
    {
        $this->helper = $helper;
        parent::__construct
        (
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer
        );
    }

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

    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $price = $this->helper->removeFormatPrice($buyRequest->getPrices()['max']);

        $buyRequest->setData('custom_price', $price);

        $product->addCustomOption(self::WARRANTY_ID, $buyRequest->getId());
        $product->addCustomOption(self::ASSOCIATED_PRODUCT, $buyRequest->getProducts());

        $product->setQty(1);

        return $product;
    }

    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);

        if($warrantyId = $product->getCustomOption(self::WARRANTY_ID)){
            $options[self::WARRANTY_ID] = $warrantyId->getValue();
        }

        if($associatedProduct = $product->getCustomOption(self::ASSOCIATED_PRODUCT)){
            $options[self::ASSOCIATED_PRODUCT] = $associatedProduct->getValue();
        }
        return $options;
    }
}