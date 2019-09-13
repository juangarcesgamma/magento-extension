<?php

namespace Extend\Catalog\Cron;

use Psr\Log\LoggerInterface;
use Extend\Warranty\Api\SyncInterface;

class Sync
{
    protected $logger;
    protected $productsCollection;

    public function __construct
    (
        LoggerInterface $logger,
        SyncInterface $productsCollection
    )
    {
        $this->logger = $logger;
        $this->productsCollection = $productsCollection;
    }

    public function execute(){
        $products = $this->productsCollection->getProducts();

        $data = [];

        foreach ($products as $product){
            $data[] = $product->getSku().', '.$product->getName().', '.$product->getPrice();
        }

        $currentDateTime = date("Y-m-d_H.i");
        $logName = 'sync_extend_' . $currentDateTime;

        $this->logger->info($logName,$data);
    }
}