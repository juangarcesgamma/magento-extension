<?php

namespace Extend\Warranty\Api;

interface ConnectionInterface
{

    public function testConnection($storeId, $apiKey, $isLive): string;

}