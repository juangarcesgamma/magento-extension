<?php

namespace Extend\Catalog\Cron;

use Psr\Log\LoggerInterface;
use Extend\Catalog\Model\ProductsCollection;

class Sync
{
    protected $logger;
    protected $productsCollection;

    public function __construct
    (
        LoggerInterface $logger,
        ProductsCollection $productsCollection
    )
    {
        $this->logger = $logger;
        $this->productsCollection = $productsCollection;
    }

    public function execute(){
        $products = $this->productsCollection->getProducts();

        $data = [];

        foreach ($products as $product){
            $data[] = "{$product['sku']},{$product['name']},{$product['qty']},{$product['price']}";
        }

        $currentDateTime = date("Y_m_d_H.i");
        $logName = 'sync_extend_' . $currentDateTime;

        $this->logger->info($logName,$data);
    }
}