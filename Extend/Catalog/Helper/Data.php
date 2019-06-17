<?php

namespace Extend\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    protected $scopeConfig;

    public function __construct
    (
        Context $context,
        ScopeConfigInterface $config
    )
    {
        $this->scopeConfig = $config;
        parent::__construct($context);
    }

    public function getExtendApiKey(){
        $mode = $this->scopeConfig->getValue('warranty/authentication/auth_mode');

        if($mode){
            return $this->scopeConfig->getValue('warranty/authentication/api_key');
        }else{
            return $this->scopeConfig->getValue('warranty/authentication_sandbox/api_key');
        }
    }

    public function getExtendStoreID(){
        $mode = $this->scopeConfig->getValue('warranty/authentication/auth_mode');

        if($mode){
            return $this->scopeConfig->getValue('warranty/authentication/store_id');
        }else{
            return $this->scopeConfig->getValue('warranty/authentication_sandbox/store_id');
        }
    }


}