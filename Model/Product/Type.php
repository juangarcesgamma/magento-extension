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
    const TERM = 'warranty_term';
    const BUY_REQUEST = 'info_buyRequest';

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
        $price = $this->helper->removeFormatPrice($buyRequest->getPrice());

        $buyRequest->setData('custom_price', $price);

        $product->addCustomOption(self::WARRANTY_ID, $buyRequest->getData('planId'));
        $product->addCustomOption(self::ASSOCIATED_PRODUCT, $buyRequest->getProduct());
        $product->addCustomOption(self::TERM, $buyRequest->getTerm());
        $product->addCustomOption(self::BUY_REQUEST, $this->serializer->serialize($buyRequest->getData()));

        if ($this->_isStrictProcessMode($processMode)) {
            $product->setCartQty($buyRequest->getQty());
        }
        $product->setQty($buyRequest->getQty());

        return $product;
    }

    public function getOrderOptions($product)
    {
        $options = parent::getOrderOptions($product);

        if ($warrantyId = $product->getCustomOption(self::WARRANTY_ID)) {
            $options[self::WARRANTY_ID] = $warrantyId->getValue();
        }

        if ($associatedProduct = $product->getCustomOption(self::ASSOCIATED_PRODUCT)) {
            $options[self::ASSOCIATED_PRODUCT] = $associatedProduct->getValue();
        }

        if ($term = $product->getCustomOption(self::TERM)) {
            $options[self::TERM] = $term->getValue();
        }
        return $options;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getWarrantyInfo($product)
    {
        $warrantyProperties = [
            self::ASSOCIATED_PRODUCT => 'Product',
            self::TERM => 'Term'
        ];

        $options = [];

        foreach ($warrantyProperties as $property => $label) {

            if ($attributesOption = $product->getCustomOption($property)) {
                $data = $attributesOption->getValue();
                if (!$data) {
                    continue;
                }

                if ($property == self::TERM) {
                    $data = ((int)$data) / 12;

                    $data .= $data > 1 ? ' years' : ' year';
                }
                $options[] = [
                    'label' => $label,
                    'value' => $data
                ];
            }
        }

        return $options;
    }
}