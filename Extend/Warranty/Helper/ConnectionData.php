<?php


namespace Extend\Warranty\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class ConnectionData
{
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getStoreIdCredential()
    {
        if($this->getMode() === "1"){
            $storeId = $this->scopeConfig->getValue('warranty/authentication/store_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $storeId = $this->scopeConfig->getValue('warranty/authentication/sandbox_store_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $storeId;
    }

    public function getApiKey()
    {
        if($this->getMode() === "1"){
            $apiKey = $this->scopeConfig->getValue('warranty/authentication/api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        } else {
            $apiKey = $this->scopeConfig->getValue('warranty/authentication/sandbox_api_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }

        return $apiKey;
    }
    private function getMode()
    {
        $mode = $this->scopeConfig->getValue('warranty/authentication/auth_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $mode;
    }
}