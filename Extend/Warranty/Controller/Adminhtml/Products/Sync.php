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
    const ADMIN_RESOURCE = 'Extend_Warranty::product_manual_sync';

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
     * @var bool
     */
    protected $resetTotal;

    /**
     * @var TimeUpdaterInterface
     */
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
        if ($this->totalBatches === null) {
            $this->totalBatches = $this->sync->getBatchesToProcess();
            $this->resetTotal = false;
        }

        $currentBatch = (int)$this->getRequest()->getParam('currentBatchesProcessed');

        $productsBatch = $this->sync->getProducts($currentBatch);

        try {
            $this->syncProcess->sync($productsBatch, $currentBatch);

            if ($currentBatch == $this->totalBatches) {
                $data['msg'] = $this->timeUpdater->updateLastSync();
                $this->resetTotal = true;
            }

            $currentBatch++;

            $data = [
                'totalBatches' => (int)$this->totalBatches,
                'currentBatchesProcessed' => (int)$currentBatch,
            ];

            if ($this->resetTotal) {
                unset($this->totalBatches);
            }

            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setHttpResponseCode(200)
                ->setData($data);
        } catch (\Exception $e) {
            $data = ['msg' => $e->getMessage()];

            $this->logger->error($e->getMessage(), ['exception' => $e]);

            return $this->resultFactory
                ->create(ResultFactory::TYPE_JSON)
                ->setHttpResponseCode(500)
                ->setData($data);
        }
    }
}