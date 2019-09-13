<?php

namespace Extend\Warranty\Api;
use Zend_Http_Response;

interface ConnectorInterface
{
    public function initClient(): void;

    public function call($endpoint, $method, $data = null): Zend_Http_Response;

    //Call for plugin of Cesar
    public function testConnection($storeId, $apiKey, $isLive): string;

}