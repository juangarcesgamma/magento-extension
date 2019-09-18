<?php

namespace Extend\Warranty\Model\Api\Sync\Contract;

use Extend\Warranty\Api\ConnectorInterface;
use Extend\Warranty\Model\Keys;
use Extend\Warranty\Api\Data\UrlBuilderInterface;
use Extend\Warranty\Helper\Api\Data as Config;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class ContractsRequest
{
    const ENDPOINT_URI = 'contracts';

    /**
     * @var Keys
     */
    protected $keys;

    /**
     * @var UrlBuilderInterface
     */
    protected $urlBuilder;

    /**
     * @var ConnectorInterface
     */
    protected $connector;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Keys $keys,
        UrlBuilderInterface $urlBuilder,
        ConnectorInterface $connector,
        Config $config,
        Json $jsonSerializer,
        LoggerInterface $logger
    )
    {
        $this->keys = $keys;
        $this->urlBuilder = $urlBuilder;
        $this->connector = $connector;
        $this->config = $config;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    public function create($contract): string
    {
        $this->createRequest($contract);
    }

    private function createRequest($contract): string
    {
        try {
            $response = $this->connector
                ->call(
                    self::ENDPOINT_URI,
                    \Zend_Http_Client::POST,
                    $contract
                );

            $this->processCreateResponse($response);

        } catch (\Zend_Http_Client_Exception $e) {

            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function processCreateResponse(\Zend_Http_Response $response): string
    {
        if ($response->isError()) {
            $res = $this->jsonSerializer->unserialize($response->getBody());
            $this->logger->error('Contract Request Fail',$res);

        } elseif ($response->getStatus() === 201) {
            $res = $this->jsonSerializer->unserialize($response->getBody());
            $contract_id = $res['id'];
            $this->logger->info(__('Contract #%1 request successful', $contract_id));
            return $contract_id;
        }

        return '';
    }

}