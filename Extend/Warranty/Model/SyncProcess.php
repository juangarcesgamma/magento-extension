<?php


namespace Extend\Warranty\Model;

use Extend\Warranty\Model\Api\Sync\Product\ProductsRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Extend\Warranty\Api\TimeUpdaterInterface;
use Magento\Catalog\Model\Product;

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

    public function __construct(
        ProductsRequest $productsRequest,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->productsRequest = $productsRequest;
        $this->scopeConfig = $scopeConfig;
    }

    public function sync(array $storeProducts): void
    {
        $productsToSync = $this->processProducts($storeProducts);

        try {
            $this->productsRequest->create($productsToSync);
            $this->setSyncedFlag($productsToSync);
        } catch (\Exception $e) {
            //Fail Request
        }
    }

    private function processProducts(array $storeProducts): array
    {
        $lastGlobalSyncDate = $this->scopeConfig->getValue(TimeUpdaterInterface::LAST_SYNC_PATH);

        if (empty($lastGlobalSyncDate)) {
            return $storeProducts;
        }

        foreach ($storeProducts as $key => $product) {
            $lastModifiedDate = $product->getUpdatedAt();

            $productIsSynced = (bool)$product->getCustomAttribute('extendSync');

            //If product has a sync flag
            if (!is_null($productIsSynced)) {
                //If product is already synced and it is up to date then no sync
                if ($productIsSynced && $lastModifiedDate <= $lastGlobalSyncDate) {
                    unset($storeProducts[$key]);
                }
            }
        }

        return $storeProducts;
    }

    /**
     * @param Product[] $storeProducts
     */
    private function setSyncedFlag(array $storeProducts): void
    {
        foreach ($storeProducts as $key => $product) {
            try {
                $product->setCustomAttrubute('extendSync', true);
                $product->save();
            } catch (\Exception $e) {
                continue;
            }

        }
    }
}