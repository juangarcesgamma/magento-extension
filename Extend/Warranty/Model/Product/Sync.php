<?php

namespace Extend\Warranty\Model\Product;

use Extend\Warranty\Api\SyncInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;

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

    /**
     * @var ResourceConnection
     */
    protected $connection;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryInterfaceFactory;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $connection,
        ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory,
        $batchSize = self::MAX_PRODUCTS_BATCH
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->batchSize = $batchSize;
        $this->connection = $connection;
        $this->productRepositoryInterfaceFactory = $productRepositoryInterfaceFactory;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    public function getProducts(int $batchNumber): array
    {
        //Get batches of products
        $this->searchCriteriaBuilder
            ->setPageSize($this->batchSize)
            ->setCurrentPage($batchNumber)
            ->addFilter('type_id', WarrantyType::TYPE_CODE, 'neq');

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $this->productRepository = $this->productRepositoryInterfaceFactory->create();
        $searchResults = $this->productRepository->getList($searchCriteria);

        return $searchResults->getTotalCount() ?
            $searchResults->getItems() :
            [];
    }

    public function getTotalOfProducts(): int
    {
        $connection = $this->connection->getConnection();
        $select = $connection->select();
        $tableName = $this->connection->getTableName('catalog_product_entity');

        $select->from($tableName, 'COUNT(*)');

        return (int)$connection->fetchOne($select);
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function getBatchesToProcess(): int
    {
        return (int)ceil($this->getTotalOfProducts() / $this->getBatchSize());
    }
}