<?php


namespace Extend\Catalog\Model;


use Extend\Catalog\Gateway\Request\ProductsRequest;

class SyncProcess
{
    protected $productsRequest;

    public function __construct(
        ProductsRequest $productsRequest
    )
    {
        $this->productsRequest = $productsRequest;
    }

    public function sync($storeProducts){

        $productsToCreate = $this->processProducts($storeProducts);

        $this->productsRequest->create($productsToCreate);

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