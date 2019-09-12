<?php


namespace Extend\Warranty\Model;


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

    public function sync($storeProducts)
    {

        $productsToCreate = $storeProducts;
        $this->productsRequest->create($productsToCreate);

    }

    private function processProducts($storeProducts)
    {
        //Logic for remove products already in the api
        foreach ($storeProducts as $key => $product) {
            $identifier = $product->getSku();
            $alreadyCreated = $this->productsRequest->get($identifier);
            if ($alreadyCreated) {
                unset($storeProducts[$key]);
            }
        }
        return $storeProducts;
    }
}