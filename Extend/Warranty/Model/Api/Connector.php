<?php


namespace Extend\Warranty\Model\Api;


use Extend\Warranty\Api\ConnectorInterface;
use Extend\Warranty\Api\Data\UrlBuilderInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Zend_Http_Response;
use Extend\Warranty\Model\Keys;
use Extend\Warranty\Helper\Api\Data as Config;
use Magento\Framework\HTTP\ZendClient;


class Connector implements ConnectorInterface
{

    protected $baseUrlSandbox = 'https://api-stage.helloextend.com/stores';

    protected $baseUrlLive = 'https://api.helloextend.com/stores';
    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var CurlFactory
     */
    protected $httpClient;


    protected $client;
    protected $keys;
    protected $urlBuilder;
    protected $config;

    protected $uri;

    public function __construct
    (
        ZendClient $client,
        Keys $keys,
        UrlBuilderInterface $urlBuilder,
        Config $config,
        Json $jsonSerializer,
        CurlFactory $httpClient
    )
    {
        $this->client = $client;
        $this->keys = $keys;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;

        $this->initClient();

        $this->jsonSerializer = $jsonSerializer;
        $this->httpClient = $httpClient;
    }

    public function testConnection($storeId, $apiKey, $isLive): string
    {
        if ($isLive === "1" && !is_null($isLive)) {
            $baseUrl = $this->baseUrlLive;
        } else if ($isLive === "0" && !is_null($isLive)) {
            $baseUrl = $this->baseUrlSandbox;
        }
        $requestPath = "{$baseUrl}/{$storeId}/products";

        $client = $this->httpClient->create();
        $headers = [
            "Accept" => "application/json",
            "Content-Type" => "application/json",
            "X-Extend-Access-Token" => $apiKey
        ];
        $client->setHeaders($headers);

        $client->get($requestPath);

        return strval($client->getStatus());

    }

    public function initClient(): void
    {
        $accessKeys = $this->apiKey = $this->config->getValue('auth_mode') ?
            $this->keys->getLiveAccessKeys() :
            $this->keys->getSandboxAccessKeys();

        $uriWithStore = '/stores/' . $accessKeys['storeID'];

        $this->uri = $this->urlBuilder->setUri($uriWithStore)->build();

        $this->client
            ->setHeaders([
                'Accept' => ' application/json',
                'Content-Type' => ' application/json',
                'X-Extend-Access-Token' => $accessKeys['api_key']
            ]);
    }

    public function call($endpoint, $method, $data = null): Zend_Http_Response
    {
        $finalUri = $this->uri . $endpoint;

        $this->client
            ->setUri($finalUri)
            ->setMethod($method);

        if (isset($data)) {
            $this->client->setRawData(json_encode($data), 'application/json');
        }

        $response = $this->client->request();

        return $response;
    }
}