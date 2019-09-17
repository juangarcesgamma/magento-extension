<?php

namespace Extend\Warranty\Model\Api\Order\Contract;

use Extend\Warranty\Model\Api\Request\ContractBuilder;
use Psr\Log\LoggerInterface;
use Extend\Warranty\Api\ConnectorInterface;


class ContractsRequest
{
    const URI = '/stores/%s/contracts';

    protected $connector;
    protected $contractBuilder;
    protected $logger;

    public function __construct(
        ConnectorInterface $connector,
        ContractBuilder $contractBuilder,
        LoggerInterface $logger
    )
    {
        $this->connector = $connector;
        $this->contractBuilder = $contractBuilder;
        $this->logger = $logger;
    }

    /**
     * @param $order
     * @param $warranty
     * @return string|null
     * @throws \Braintree\Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function create($order, $warranty)
    {
        $contract = $this->contractBuilder->build($order, $warranty);

        $endpoint = '/contracts';
        $response = $this->connector->call($endpoint, 'POST', $contract);

        if ($response->isError()) {
            $res = json_decode($response->getBody(), true);
            $this->logger->error($res['message']);
            return null;

        } elseif ($response->getStatus() === 201) {
            $this->logger->info('Create product request successful');
            $res = json_decode($response->getBody(), true);
            return $res['id'];
        }
        return null;
    }

}