<?php

namespace Extend\Warranty\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use Extend\Catalog\Model\ProductsCollection;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\App\Cache\Manager;


class Sync extends Action
{
    const LAST_SYNC_PATH = 'warranty/products/lastSync';
    const MAX_PRODUCTS_BATCH = 250;
    protected $_publicActions = ['extend/products/sync'];
    protected $resultFactory;
    protected $logger;
    protected $syncProcess;
    protected $productsCollection;

    protected $configWriter;
    protected $timezone;
    private $cacheManager;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        ProductsCollection $productsCollection,
        Writer $configWriter,
        TimezoneInterface $timezone,
        Manager $cacheManager
    )
    {
        $this->productsCollection = $productsCollection;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
        $this->cacheManager = $cacheManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeProducts = $this->productsCollection->getProducts();

        try {
            $numOfBatches = ceil(sizeof($storeProducts) / self::MAX_PRODUCTS_BATCH);
            $i = 0;
            while ($numOfBatches > 0) {
                if ($numOfBatches === 1) {
                    $productsInBatch = array_slice($storeProducts, $i * self::MAX_PRODUCTS_BATCH);
                } else {
                    $productsInBatch = array_slice($storeProducts, $i * self::MAX_PRODUCTS_BATCH, self::MAX_PRODUCTS_BATCH);
                }
                $this->syncProcess->sync($productsInBatch);
                $numOfBatches--;
                $i++;
                sleep(0.75);
            }
            $this->logger->info('Products Successfully Synchronized');
            $date = $this->timezone->formatDate(null, 1, true);
            $data = ['msg' => $date];
            $this->configWriter->save(self::LAST_SYNC_PATH, $date);
            $this->cacheManager->clean($this->cacheManager->getAvailableTypes());
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(200)->setData($data);
        } catch (\Exception $e) {
            $data = ['msg' => $e->getMessage()];
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(500)->setData($data);
        }
    }
}