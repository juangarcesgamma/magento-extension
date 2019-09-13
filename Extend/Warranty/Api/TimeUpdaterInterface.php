<?php


namespace Extend\Warranty\Api;


interface TimeUpdaterInterface
{
    const LAST_SYNC_PATH = 'warranty/products/lastSync';

    public function updateLastSync(): string;
}