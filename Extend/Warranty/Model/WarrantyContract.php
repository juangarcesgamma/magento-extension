<?php

namespace Extend\Warranty\Model;

use Braintree\Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Extend\Warranty\Model\Api\Sync\Contract\ContractsRequest;
use Extend\Warranty\Model\Api\Request\ContractBuilder;

class WarrantyContract
{
    protected $contractsRequest;
    protected $contractBuilder;

    public function __construct
    (
        ContractsRequest $contractsRequest,
        ContractBuilder $contractBuilder
    )
    {
        $this->contractsRequest = $contractsRequest;
        $this->contractBuilder = $contractBuilder;
    }

    public function createContract($order, $warranties)
    {

        try {
            $contracts = $this->contractBuilder->prepareInfo($order, $warranties);
            foreach ($contracts as $contract) {
                $contractId = $this->contractsRequest->create($contract);

                if(!empty($contractId)){
                    //Save contract id implementation
                }
            }

        } catch (NoSuchEntityException $exception) {

        } catch (Exception $exception) {

        }

    }

}