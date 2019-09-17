<?php

namespace Extend\Warranty\Model\Product;

use Extend\Warranty\Api\SyncInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Sync implements SyncInterface
{
    protected $productRepository;
    protected $searchCriteriaBuilder;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getProducts(int $batchNumber): array
    {
        //Get batches of products
        $this->searchCriteriaBuilder
            ->setPageSize(self::MAX_PRODUCTS_BATCH)
            ->setCurrentPage($batchNumber);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $products = $this->productRepository->getList($searchCriteria);

        return $products->getItems();
    }

    public function getTotalOfProducts(): int
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $products = $this->productRepository->getList($searchCriteria);

        return count($products->getItems());
    }
}