<?php

namespace Extend\Warranty\Model\Api\Sync\Contract;

use Extend\Warranty\Model\Keys;
use Magento\Framework\HTTP\ZendClient;
use Extend\Warranty\Api\Data\UrlBuilderInterface;
use Extend\Catalog\Helper\Data as Config;
use Psr\Log\LoggerInterface;

class ContractsRequest
{
    const URI = '/stores/%s/contracts';

    protected $keys;
    protected $urlBuilder;
    protected $client;
    protected $config;
    protected $logger;

    public function __construct(
        Keys $keys,
        UrlBuilderInterface $urlBuilder,
        ZendClient $client,
        Config $config,
        LoggerInterface $logger
    )
    {
        $this->keys = $keys;
        $this->urlBuilder = $urlBuilder;
        $this->client = $client;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function prepareClient()
    {

        $accessKeys = $this->config->getValue('auth_mode') ?
            $this->keys->getLiveAccessKeys() :
            $this->keys->getSandboxAccessKeys();

        $uriWithStore = sprintf(self::URI, $accessKeys['storeID']);

        $uri = $this->urlBuilder->setUri($uriWithStore)->build();

        $this->client
            ->setUri($uri)
            ->setHeaders([
                'Accept' => ' application/json',
                'Content-Type' => ' application/json',
                'X-Extend-Access-Token' => $accessKeys['api_key']
            ]);

    }

    public function create($contract)
    {
        $this->createRequest($contract);
    }

    private function createRequest($contract)
    {
        try {
            $this->client
                ->setMethod(ZendClient::POST)
                ->setRawData(json_encode($contract), 'application/json');
            $response = $this->client->request();
            $this->processCreateResponse($response, $contract);

        } catch (\Zend_Http_Client_Exception $e) {

            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

    private function processCreateResponse(\Zend_Http_Response $response, $contract)
    {
        if ($response->isError()) {
            $res = json_decode($response->getBody(), true);
            $this->logger->error('Contract Request Fail',$res);

        } elseif ($response->getStatus() === 201) {
            $res = json_decode($response->getBody(), true);
            $this->logger->info(__('Contract #%1 request successful', $res['id']));
        }
    }

}