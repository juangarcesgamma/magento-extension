<?php


namespace Extend\Catalog\Model;


use Extend\Catalog\Gateway\Request\ProductsRequest;

class SyncProcess
{
    const MAX_PRODUCTS_BATCH = 250;
    protected $productsRequest;

    public function __construct(
        ProductsRequest $productsRequest
    )
    {
        $this->productsRequest = $productsRequest;
    }

    public function sync($storeProducts){

        $productsToCreate = $this->processProducts($storeProducts);

        $numOfBatches = ceil(sizeof($productsToCreate)/self::MAX_PRODUCTS_BATCH);

        for ($i = 0 ; $i < $numOfBatches ; $i++){
            if($i === ($numOfBatches-1)){
                $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH);
            }else{
                $productsInBatch = array_slice($productsToCreate,$i*self::MAX_PRODUCTS_BATCH,self::MAX_PRODUCTS_BATCH);
            }
            $this->productsRequest->create($productsInBatch);
            sleep(0.75);
        }
    }

    private function processProducts($storeProducts){

        foreach ($storeProducts as $key => $product){
            $identifier = $product->getSku();
            $alreadyCreated = $this->productsRequest->get($identifier);
            if($alreadyCreated){
                unset($storeProducts[$key]);
            }
        }
        return $storeProducts;
    }
}