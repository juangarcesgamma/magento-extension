<?php

namespace Extend\Warranty\Cron;

use Psr\Log\LoggerInterface;

class Sync
{
    protected $logger;
    protected $productsCollection;

    public function __construct
    (
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    public function execute(){

        $this->logger->info('Cron for product sync, waiting for implementation');
    }
}