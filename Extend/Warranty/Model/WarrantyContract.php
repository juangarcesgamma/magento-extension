<?php

namespace Extend\Warranty\Model;

use Braintree\Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Extend\Warranty\Model\Api\Order\Contract\ContractsRequest;

class WarrantyContract
{
    protected $contractsRequest;

    public function __construct(ContractsRequest $contractsRequest)
    {
        $this->contractsRequest = $contractsRequest;
    }

    public function createContract($order, $warranty){

        try {
            $contractId = $this->contractsRequest->create($order, $warranty);

        }catch (NoSuchEntityException $exception){

        }catch (Exception $exception){

        }

    }

}