<?php

namespace Extend\Warranty\Model;

use Extend\Warranty\Helper\Api\Data as Config;


class Keys
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var null|string
     */
    private $apiKey;

    /**
     * @var null|string
     */
    private $storeID;

    /**
     * @var null|string
     */
    private $sandbox_ApiKey;

    /**
     * @var null|string
     */
    private $sandbox_storeID;

    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    /**
     * @return array
     */

    public function getLiveAccessKeys()
    {
        if ($this->apiKey === null || $this->storeID === null) {
            $this->apiKey = $this->config->getValue('api_key');
            $this->storeID = $this->config->getValue('store_id');
        }

        return [
            'api_key' => $this->apiKey,
            'storeID' => $this->storeID
        ];
    }

    public function getSandboxAccessKeys()
    {
        if ($this->apiKey === null || $this->storeID === null) {
            $this->sandbox_ApiKey = $this->config->getValue('sandbox_api_key');
            $this->sandbox_storeID = $this->config->getValue('sandbox_store_id');
        }

        return [
            'api_key' => $this->sandbox_ApiKey,
            'storeID' => $this->sandbox_storeID
        ];
    }
}