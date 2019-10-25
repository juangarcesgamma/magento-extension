<?php

namespace Extend\Warranty\Api;

interface SyncInterface
{
    const MAX_PRODUCTS_BATCH = 250;

    public function getProducts(int $batchNumber): array;

    public function getTotalOfProducts(): int;

    public function getBatchSize(): int;

    public function getBatchesToProcess(): int;
}