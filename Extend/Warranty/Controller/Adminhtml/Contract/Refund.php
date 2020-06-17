<?php

namespace Extend\Warranty\Controller\Adminhtml\Contract;

use Extend\Warranty\Helper\Api\Data as ExtendData;
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

    /**
     * @var ExtendData
     */
    protected $extendHelper;

    public function __construct(
        Action\Context $context,
        ResultFactory $resultFactory,
        ContractsRequest $contractsRequest,
        OrderItemRepositoryInterface $orderItemRepository,
        ExtendData $extendHelper
    )
    {
        parent::__construct($context);

        $this->resultFactory = $resultFactory;
        $this->contractsRequest = $contractsRequest;
        $this->orderItemRepository = $orderItemRepository;
        $this->extendHelper = $extendHelper;
    }


    public function execute()
    {
        $response = $this->resultFactory
            ->create(ResultFactory::TYPE_JSON);

        if (!$this->extendHelper->isExtendEnabled() || !$this->extendHelper->isRefundEnabled()) {
            $response->setData(
                [
                    'error' => 'Extend module or refunds are not enabled'
                ]);
            $response->setHttpResponseCode(403); //Forbidden error
            return $response;
        }

        $contractId = (string)$this->getRequest()->getPost('contractId');
        $itemId = (string)$this->getRequest()->getPost('itemId');

        $res = $this->contractsRequest->refund($contractId);

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