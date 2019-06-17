<?php

namespace Extend\Catalog\Cron;

use Psr\Log\LoggerInterface;
use Extend\Catalog\Api\ProductsCollectionInterface;

class Sync
{
    protected $logger;
    protected $productsCollection;

    public function __construct
    (
        LoggerInterface $logger,
        ProductsCollectionInterface $productsCollection
    )
    {
        $this->logger = $logger;
        $this->productsCollection = $productsCollection;
    }

    public function execute(){
        $products = $this->productsCollection->getProducts();

        $data = [];

        foreach ($products as $product){
            $data[] = $product->getSku().', '.$product->getName().', '.$product->getQty().', '.$product->getPrice();
        }

        $currentDateTime = date("Y-m-d_H.i");
        $logName = 'sync_extend_' . $currentDateTime;

        $this->logger->info($logName,$data);
    }
}