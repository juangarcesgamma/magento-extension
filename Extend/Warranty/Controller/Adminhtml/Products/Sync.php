<?php

namespace Extend\Warranty\Controller\Adminhtml\Products;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Extend\Warranty\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use Extend\Warranty\Api\SyncInterface;
use Extend\Warranty\Api\TimeUpdaterInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Sync extends Action
{
    const ADMIN_RESOURCE = 'Extend_Warranty::product_manual_sync';
    const BATCH_SIZE_PATH = 'warranty/products/batch_size';

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var SyncProcess
     */
    protected $syncProcess;

    /**
     * @var SyncInterface
     */
    protected $sync;

    /**
     * @var int
     */
    protected $totalBatches;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @var bool
     */
    protected $resetTotal;

    /**
     * @var TimeUpdaterInterface
     */
    protected $timeUpdater;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        LoggerInterface $logger,
        SyncProcess $syncProcess,
        SyncInterface $sync,
        TimeUpdaterInterface $timeUpdater,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->sync = $sync;
        $this->resultFactory = $resultFactory;
        $this->logger = $logger;
        $this->syncProcess = $syncProcess;
        $this->timeUpdater = $timeUpdater;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $currentBatch = (int)$this->getRequest()->getParam('currentBatchesProcessed');

        if ($this->batchSize === null) {
            $batchSize = $this->scopeConfig->getValue(self::BATCH_SIZE_PATH);

            if ($batchSize) {
                $this->batchSize = $batchSize;
                $this->sync->setBatchSize((int)$batchSize);
            }
        }

        if ($this->totalBatches === null) {
            $this->totalBatches = $this->sync->getBatchesToProcess();
            $this->resetTotal = false;
        }

        $productsBatch = $this->sync->getProducts($currentBatch);

        $data = [];

        try {
            $this->syncProcess->sync($productsBatch, $currentBatch);
            $data['status'] = 'SUCCESS';
        } catch (\Exception $e) {
            $this->logger->info('Error found in products batch ' . $currentBatch, ['Exception' => $e->getMessage()]);
            $data['status'] = 'FAIL';
        }

        if ($currentBatch == $this->totalBatches) {
            $data['msg'] = $this->timeUpdater->updateLastSync();
            $this->resetTotal = true;
        }

        $currentBatch++;

        $data['totalBatches'] = (int)$this->totalBatches;
        $data['currentBatchesProcessed'] = (int)$currentBatch;

        if ($this->resetTotal) {
            unset($this->totalBatches);
        }

        return $this->resultFactory
            ->create(ResultFactory::TYPE_JSON)
            ->setHttpResponseCode(200)
            ->setData($data);
    }
}