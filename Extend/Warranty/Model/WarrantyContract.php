<?php

namespace Extend\Warranty\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Extend\Warranty\Model\Api\Sync\Contract\ContractsRequest;
use Extend\Warranty\Model\Api\Request\ContractBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class WarrantyContract
{
    /**
     * @var ContractsRequest
     */
    protected $contractsRequest;

    /**
     * @var ContractBuilder
     */
    protected $contractBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct
    (
        ContractsRequest $contractsRequest,
        ContractBuilder $contractBuilder,
        LoggerInterface $logger
    )
    {
        $this->contractsRequest = $contractsRequest;
        $this->contractBuilder = $contractBuilder;
        $this->logger = $logger;
    }

    /**
     * @param OrderInterface $order
     * @param $warranties
     */
    public function createContract($order, $warranties)
    {

        try {
            $contracts = $this->contractBuilder->prepareInfo($order, $warranties);

            foreach ($contracts as $contract) {
                $contractId = $this->contractsRequest->create($contract);
                if (!empty($contractId)) {

                    foreach ($order->getItems() as $item) {
                        if ($item->getSku() == $contract['product']['referenceId'] && empty($item->getContractId())) {
                            $item->setContractId($contractId);
                            $item->save();
                        }
                    }
                }
            }

        } catch (NoSuchEntityException $exception) {
            $this->logger->error('Error while creating warranty contract');
        }

    }

}