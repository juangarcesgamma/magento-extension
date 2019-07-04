<?php

namespace Extend\Catalog\Model;

use Extend\Catalog\Api\ProductsCollectionInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductsCollection implements ProductsCollectionInterface
{
    protected $productCollectionFactory;

    public function __construct
    (
        CollectionFactory $productCollectionFactory
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function getProducts(): array
    {
        //Collection Factory get only products in stock
        $collection = $this->productCollectionFactory->create();
        $collection->joinField('qty',
            'cataloginventory_stock_item',
            'qty',
            'product_id=entity_id',
            '{{table}}.stock_id=1',
            'left')
            ->addAttributeToSelect(['*'])
            ->addAttributeToFilter('type_id',['eq' => Type::TYPE_SIMPLE]);

       return  $collection->getItems();
    }
}