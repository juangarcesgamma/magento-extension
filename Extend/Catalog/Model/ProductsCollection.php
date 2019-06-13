<?php

namespace Extend\Catalog\Model;

use Extend\Catalog\Api\ProductsCollectionInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockStateInterface;

class ProductsCollection implements ProductsCollectionInterface
{
    protected $productCollectionFactory;

    public function __construct
    (
        CollectionFactory $productCollectionFactory,
        StockStateInterface $stockItem
    )
    {
        $this->stockItem = $stockItem;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function getProducts(): array
    {
        //Collection Factory get only products in stock
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id',['eq' => Type::TYPE_SIMPLE]);

        $data = [];

        foreach ($collection as $product){
            $data[] = [
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'price' => $product->getPrice(),
                'qty' => $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId())
            ];
        }

        return $data;
    }
}