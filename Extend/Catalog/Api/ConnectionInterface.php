<?php


namespace Extend\Catalog\Api;


interface ConnectionInterface
{
    public function createProducts($products): array;
}