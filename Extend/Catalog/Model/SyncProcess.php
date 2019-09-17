<?php


namespace Extend\Catalog\Model;


use Extend\Catalog\Controller\Adminhtml\Products\Sync;
use Extend\Catalog\Gateway\Request\ProductsRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncProcess
{
    protected $productsRequest;

    protected $scopeConfig;

    public function __construct(
        ProductsRequest $productsRequest,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->productsRequest = $productsRequest;
        $this->scopeConfig = $scopeConfig;
    }

    public function sync($storeProducts)
    {

        $productsToSync = $this->processProducts($storeProducts);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(count($productsToSync['productsToUpdate']));
        $logger->info(count($productsToSync['productsToCreate']));
        if(!empty($productsToSync['productsToCreate'])){
            $this->productsRequest->create($productsToSync['productsToCreate']);
        }

        if(!empty($productsToSync['productsToUpdate'])){
            $this->productsRequest->update($productsToSync['productsToUpdate']);
        }
    }

    private function processProducts($storeProducts)
    {
        $productsOutdated = [];

        //Logic for remove products already in the api
        foreach ($storeProducts as $key => $product) {
            $lastModifiedDate = $product->getUpdatedAt();
            $lastGlobalSyncDate = $this->scopeConfig->getValue(Sync::LAST_SYNC_PATH);
            $productIsSynced = (bool)$product->getCustomAttribute('is_product_synced')->getValue();

            //If product has a sync flag
            if (!is_null($productIsSynced)) {
                //If product is already synced and it is up to date then no sync
                if ($productIsSynced && (!is_null($lastModifiedDate) && $lastModifiedDate < $lastGlobalSyncDate)) {
                    unset($storeProducts[$key]);
                    //If product is already synced but it is oudated then update and not create
                } else if ($productIsSynced && (!is_null($lastModifiedDate) && $lastModifiedDate >= $lastGlobalSyncDate)) {
                    $productsOutdated[] = $product;
                    unset($storeProducts[$key]);
                }
            }
        }

        return [
            'productsToCreate' => $storeProducts,
            'productsToUpdate' => $productsOutdated
        ];
    }
}