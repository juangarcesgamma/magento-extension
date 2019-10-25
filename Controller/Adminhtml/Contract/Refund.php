<?php

namespace Extend\Warranty\Controller\Adminhtml\Contract;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Extend\Warranty\Model\Api\Sync\Contract\ContractsRequest;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class Refund extends Action
{
    const ADMIN_RESOURCE = 'Extend_Warranty::refund_warranty';

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var ContractsRequest
     */
    protected $contractsRequest;

    /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        ContractsRequest $contractsRequest,
        OrderItemRepositoryInterface $orderItemRepository
    )
    {
        parent::__construct($context);

        $this->resultFactory = $resultFactory;
        $this->contractsRequest = $contractsRequest;
        $this->orderItemRepository = $orderItemRepository;
    }


    public function execute()
    {
        $contractId = (string)$this->getRequest()->getPost('contractId');
        $itemId = (string)$this->getRequest()->getPost('itemId');

        $res = $this->contractsRequest->refund($contractId);

        $response = $this->resultFactory
            ->create(ResultFactory::TYPE_JSON);

        if ($res) {
            $item = $this->orderItemRepository->get($itemId);
            $options = $item->getProductOptions();
            $options['refund'] = true;
            $item->setProductOptions($options);
            $item->save();
            $response->setHttpResponseCode(200);
        } else {
            $response->setHttpResponseCode(500);
        }

        return $response;
    }
}