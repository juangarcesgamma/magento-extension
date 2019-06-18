<?php

namespace Extend\Catalog\Helper;

use BaconQrCode\Common\Mode;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data extends AbstractHelper
{
    CONST MODE = 'warranty/authentication/auth_mode';
    CONST APIKEY = 'warranty/authentication/api_key';
    CONST SAND_APIKEY = 'warranty/authentication_sandbox/api_key';
    CONST STOREID = 'warranty/authentication/store_id';
    CONST SAND_STOREID = 'warranty/authentication_sandbox/store_id';

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
        $mode = $this->scopeConfig->getValue(self::MODE);

        if($mode){
            return $this->scopeConfig->getValue(self::APIKEY);
        }else{
            return $this->scopeConfig->getValue(self::SAND_APIKEY);
        }
    }

    public function getExtendStoreID(){
        $mode = $this->scopeConfig->getValue(self::MODE);

        if($mode){
            return $this->scopeConfig->getValue(self::STOREID);
        }else{
            return $this->scopeConfig->getValue(self::SAND_STOREID);
        }
    }


}