<?php

namespace Extend\Warranty\Controller\Adminhtml\Contract;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Extend\Warranty\Model\Api\Sync\Contract\ContractsRequest;

class Refund extends Action
{
    const ADMIN_RESOURCE = 'Extend_Warranty::refund_warranty';

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    protected $contractsRequest;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        ContractsRequest $contractsRequest
    )
    {
        parent::__construct($context);

        $this->resultFactory = $resultFactory;
        $this->contractsRequest = $contractsRequest;
    }


    public function execute()
    {
        $contractId = (string)$this->getRequest()->getParam('contractId');

        $res = $this->contractsRequest->refund($contractId);

        $response = $this->resultFactory
            ->create(ResultFactory::TYPE_JSON);

        $response->setHttpResponseCode(500);

        if ($res) {
            $response->setHttpResponseCode(200);
        }

        return $response;
    }
}