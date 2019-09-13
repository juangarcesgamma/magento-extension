<?php

namespace Extend\Warranty\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Extend\Warranty\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use Extend\Warranty\Api\SyncInterface;
use Extend\Warranty\Api\TimeUpdaterInterface;

class Sync extends Action
{
    protected $_publicActions = ['extend/products/sync'];

    protected $resultFactory;
    protected $logger;
    protected $syncProcess;
    protected $sync;

    protected $totalBatches;

    protected $timeUpdater;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        SyncInterface $sync,
        TimeUpdaterInterface $timeUpdater
    )
    {
        $this->sync = $sync;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        $this->timeUpdater = $timeUpdater;

        parent::__construct($context);
    }

    public function execute()
    {
        if (!isset($this->totalBatches)) {
            $this->totalBatches = ceil($this->sync->getTotalOfProducts() / SyncInterface::MAX_PRODUCTS_BATCH);
        }

        $currentBatch = (int)$this->getRequest()->getParam('currentBatchesProcessed');

        $productsBatch = $this->sync->getProducts($currentBatch);

        try {

            $this->syncProcess->sync($productsBatch);
            $currentBatch++;

            $data = [
                'totalBatches' => (int)$this->totalBatches,
                'currentBatchesProcessed' => (int)$currentBatch,
            ];

            if ($currentBatch == $this->totalBatches) {
                $data['msg'] = $this->timeUpdater->updateLastSync();
            }

            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(200)->setData($data);
        } catch (\Exception $e) {
            $data = ['msg' => $e->getMessage()];
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setHttpResponseCode(500)->setData($data);
        }
    }
}