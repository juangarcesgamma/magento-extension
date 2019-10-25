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
    private $liveKey;

    /**
     * @var null|string
     */
    private $liveStoreID;

    /**
     * @var null|string
     */
    private $sandboxKey;

    /**
     * @var null|string
     */
    private $sandboxStoreID;

    public function __construct(
        Config $config
    )
    {
        $this->config = $config;
    }

    public function getKeys(): array
    {
        return $this->config->getValue('auth_mode') ?
            $this->getLiveAccessKeys() :
            $this->getSandboxAccessKeys();
    }

    /**
     * @return array
     */

    private function getLiveAccessKeys(): array
    {
        if ($this->liveKey === null || $this->liveStoreID === null) {
            $this->liveKey = $this->config->getValue('api_key');
            $this->liveStoreID = $this->config->getValue('store_id');
        }

        return [
            'api_key' => $this->liveKey,
            'store_id' => $this->liveStoreID
        ];
    }

    private function getSandboxAccessKeys(): array
    {
        if ($this->sandboxKey === null || $this->sandboxStoreID === null) {
            $this->sandboxKey = $this->config->getValue('sandbox_api_key');
            $this->sandboxStoreID = $this->config->getValue('sandbox_store_id');
        }

        return [
            'api_key' => $this->sandboxKey,
            'store_id' => $this->sandboxStoreID
        ];
    }
}