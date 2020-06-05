<?php

namespace Extend\Warranty\Api;

interface SyncInterface
{
    const MAX_PRODUCTS_BATCH = 100;

    public function getProducts(int $batchNumber): array;

    public function getTotalOfProducts(): int;

    public function getBatchSize(): int;

    public function getBatchesToProcess(): int;

    public function setBatchSize(int $batchSize): void;
}