<?php


namespace Extend\Warranty\Model;

use Extend\Warranty\Model\Api\Sync\Product\ProductsRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Extend\Warranty\Api\TimeUpdaterInterface;
use Psr\Log\LoggerInterface;

class SyncProcess
{
    /**
     * @var ProductsRequest
     */
    protected $productsRequest;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ProductsRequest $productsRequest,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    )
    {
        $this->productsRequest = $productsRequest;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    public function sync(array $storeProducts, $batch)
    {
        $productsToSync = $this->processProducts($storeProducts);

        if (!empty($productsToSync)) {
            $this->productsRequest->create($productsToSync, $batch);
        } else {
            $this->logger->info('Nothing to sync in batch ' . $batch);
        }
    }

    private function processProducts(array $storeProducts): array
    {
        $lastGlobalSyncDate = $this->scopeConfig->getValue(TimeUpdaterInterface::LAST_SYNC_PATH);

        if (empty($lastGlobalSyncDate)) {
            return $storeProducts;
        }

        $lastGlobalSyncDate = new \DateTime($lastGlobalSyncDate);

        foreach ($storeProducts as $key => $product) {
            $lastModifiedDate = new \DateTime($product->getUpdatedAt());

            //If product has not been updated
            if ($lastModifiedDate <= $lastGlobalSyncDate) {
                unset($storeProducts[$key]);
            }
        }

        return $storeProducts;
    }
}