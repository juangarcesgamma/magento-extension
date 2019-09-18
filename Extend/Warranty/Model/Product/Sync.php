<?php

namespace Extend\Warranty\Model\Product;

use Extend\Warranty\Api\SyncInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Sync implements SyncInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var int
     */
    protected $batchSize;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        $batchSize = self::MAX_PRODUCTS_BATCH
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->batchSize = $batchSize;
    }

    public function getProducts(int $batchNumber): array
    {
        //Get batches of products
        $this->searchCriteriaBuilder
            ->setPageSize($this->batchSize)
            ->setCurrentPage($batchNumber);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->productRepository->getList($searchCriteria);

        return $searchResults->getTotalCount() ?
            $searchResults->getItems() :
            [];
    }

    public function getTotalOfProducts(): int
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResults = $this->productRepository->getList($searchCriteria);

        return $searchResults->getTotalCount();
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function getBatchesToProcess(): int
    {
        return (int) ceil($this->getTotalOfProducts() / $this->getBatchSize());
    }
}