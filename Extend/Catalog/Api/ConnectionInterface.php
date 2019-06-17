<?php


namespace Extend\Catalog\Api;


interface ConnectionInterface
{
    public function createProduct($product): void;
}