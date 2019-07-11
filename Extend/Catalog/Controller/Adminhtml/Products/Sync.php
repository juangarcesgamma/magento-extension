<?php

namespace Extend\Catalog\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use Extend\Catalog\Model\ProductsCollection;
use Magento\Framework\App\Config\Storage\Writer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;


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

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        ProductsCollection $productsCollection
        LoggerInterface $logger,
        Writer $configWriter,
        TimezoneInterface $timezone
    )
    {
        $this->productsCollection = $productsCollection;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        $this->configWriter = $configWriter;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
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
            $code = 200;
            $result = $this->prepareResult($result, $code);
            $this->logger->info('Products Successfully Synchronized');
            $date = $this->timezone->formatDate(null,1, true);
            $this->configWriter->save(self::LAST_SYNC_PATH,$date);
            return $result;
        } catch (Exception $e) {
            $msg = __($e->getMessage());
            $code = 500;
            $result = $this->prepareResult($result, $code, ['msg' => $msg]);
            return $result;
        }
    }

    protected function prepareResult(JsonResult $result, int $code, array $data = [])
    {
        $result->setHttpResponseCode($code);
        $result->setData($data);
    }
}