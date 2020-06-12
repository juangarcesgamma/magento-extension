<?php

namespace Extend\Warranty\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Extend\Warranty\Api\SyncInterface as ProductBatch;
use Magento\Framework\App\State;
use Extend\Warranty\Model\SyncProcess;
use Psr\Log\LoggerInterface;
use \Extend\Warranty\Api\TimeUpdaterInterface;
use Symfony\Component\Console\Input\InputOption;

class SyncProducts extends Command
{
    const ARGUMENT_BATCH_SIZE = 'batch';
    /**
     * @var State
     */
    private $state;
    /**
     * @var ProductBatch
     */
    private $productBatch;

    /**
     * @var SyncProcess
     */
    private $syncProcess;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimeUpdaterInterface
     */
    private $timeUpdater;

    public function __construct(
        State $state,
        ProductBatch $productBatch,
        SyncProcess $syncProcess,
        LoggerInterface $logger,
        TimeUpdaterInterface $timeUpdater,
        $name = null
    )
    {
        parent::__construct($name);
        $this->state = $state;
        $this->productBatch = $productBatch;
        $this->syncProcess = $syncProcess;
        $this->logger = $logger;
        $this->timeUpdater = $timeUpdater;
    }

    protected function configure()
    {
        $options = [
            new InputOption(
                self::ARGUMENT_BATCH_SIZE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Set Product Batch Size'
            )
        ];

        $this->setName('extend:sync:products');
        $this->setDescription('Sync products from Magneto 2 to Extend');
        $this->setDefinition($options);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting Sync Process... ");
        $this->state->setAreaCode('adminhtml');

        if ($size = $input->getOption(self::ARGUMENT_BATCH_SIZE)) {
            $batchSize = (int)$size;

            if ($batchSize > 100 || $batchSize <= 0) {
                $output->writeln('<error>Invalid batch size, value must be between 1-100.</error>');
                return $this;
            }
            $this->productBatch->setBatchSize($batchSize);
        } else {
            $output->writeln('<info>Setting product batch to 100.</info>');
            $this->productBatch->setBatchSize(100);
        }

        $totalBatches = $this->productBatch->getBatchesToProcess();
        $noError = true;

        for ($i = 1; $i <= $totalBatches; $i++) {
            try {
                $productsBatch = $this->productBatch->getProducts($i);
                $this->syncProcess->sync($productsBatch, $i);
            } catch (\Exception $e) {
                if ($noError) {
                    $noError = false;
                }

                $this->logger->error('Error found in products batch ' . $i, ['Exception' => $e->getMessage()]);
            }
        }

        if ($noError) {
            $this->timeUpdater->updateLastSync();
        } else {
            $output->writeln('<error>Some batches have not sync correctly, unable to save last time sync time.</error>');
        }

        return $this;
    }
}