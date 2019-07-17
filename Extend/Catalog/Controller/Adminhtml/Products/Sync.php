<?php

namespace Extend\Catalog\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\ResultFactory;
use Extend\Catalog\Model\SyncProcess;
use Magento\Framework\Filesystem\File\Read;
use Psr\Log\LoggerInterface;
use Extend\Catalog\Model\ProductsCollection;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\File\Csv;

class Sync extends Action
{
    const MAX_PRODUCTS_BATCH = 20;
    protected $_publicActions = ['extend/products/sync'];
    protected $resultFactory;
    protected $logger;
    protected $syncProcess;
    protected $productsCollection;
    protected $fileCsv;
    protected $moduleReader;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        ProductsCollection $productsCollection,
        Csv $fileCsv,
        Reader $moduleReader

    )
    {
        $this->productsCollection = $productsCollection;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        $this->fileCsv = $fileCsv;
        $this->moduleReader = $moduleReader;

        parent::__construct($context);
    }

    public function execute()
    {
        $storeProducts = $this->productsCollection->getProducts();

        try {
            $totalBatches = ceil(sizeof($storeProducts) / self::MAX_PRODUCTS_BATCH);
            $currentBatch = (int)$this->getRequest()->getParam('currentBatchesProcessed');

            if ($currentBatch === $totalBatches) {
                $productsInBatch = array_slice($storeProducts, $currentBatch * self::MAX_PRODUCTS_BATCH);
            } else {
                $productsInBatch = array_slice($storeProducts, $currentBatch * self::MAX_PRODUCTS_BATCH, self::MAX_PRODUCTS_BATCH);
            }
            $this->syncProcess->sync($productsInBatch);

            $currentBatch++;

            $data = [
                'totalBatches' => (int)$totalBatches,
                'currentBatchesProcessed' => (int)$currentBatch
                ];
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(200)->setData($data);

        } catch (Exception $e) {
            $data = ['msg' => $e->getMessage()];
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(500)->setData($data);
        }
    }
}