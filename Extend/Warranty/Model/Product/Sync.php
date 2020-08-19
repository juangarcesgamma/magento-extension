<?php

namespace Extend\Warranty\Model\Product;

use Extend\Warranty\Api\SyncInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Extend\Warranty\Model\Product\Type as WarrantyType;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Model\StoreManagerInterface;

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

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct
    (
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ResourceConnection $connection,
        ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory,
        StoreManagerInterface $storeManager,
        $batchSize = self::MAX_PRODUCTS_BATCH
    )
    {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->batchSize = $batchSize;
        $this->connection = $connection;
        $this->productRepositoryInterfaceFactory = $productRepositoryInterfaceFactory;
        $this->storeManager = $storeManager;
    }

    public function setBatchSize(int $batchSize): void
    {
        $this->batchSize = $batchSize;
    }

    public function getProducts(int $batchNumber = 1): array
    {
        //Get batches of products
        $this->searchCriteriaBuilder
            ->setPageSize($this->batchSize)
            ->setCurrentPage($batchNumber)
            ->addFilter('type_id', WarrantyType::TYPE_CODE, 'neq')
            ->addFilter('status', Status::STATUS_ENABLED, 'eq');

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
        $statusTable = $this->connection->getTableName('catalog_product_entity_int');
        $defaultStore = $this->storeManager->getDefaultStoreView()->getId();

        $eavTable = $this->connection->getTableName('eav_attribute');

        $select->from($eavTable, 'attribute_id')
            ->where('attribute_code = ?', 'status')
            ->where('entity_type_id = ?', 4); //Default product entity id

        $statusId = $connection->fetchOne($select);

        $select = $connection->select();

        $select->from(['main' => $tableName], 'COUNT(*)')
            ->join(['status' => $statusTable],
                $connection->quoteInto('main.entity_id = status.entity_id AND status.attribute_id = ? AND status.store_id = 0', $statusId), '')
            ->joinLeft(['status_store' => $statusTable],
                $connection->quoteInto('main.entity_id = status_store.entity_id AND status_store.attribute_id = ?', $statusId) . ' ' .
                $connection->quoteInto(' AND status_store.store_id = ?', $defaultStore), '')
            ->where('status.value = ?', Status::STATUS_ENABLED)
            ->where('type_id <> ?', WarrantyType::TYPE_CODE)
            ->where('IF(status_store.value_id > 0, status_store.value, status.value) = ?', Status::STATUS_ENABLED);

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