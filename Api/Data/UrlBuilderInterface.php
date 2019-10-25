<?php

namespace Extend\Warranty\Api\Data;

interface UrlBuilderInterface
{
    const SANDBOX_URL = 'https://api-demo.helloextend.com/';

    const LIVE_URL = 'https://api.helloextend.com/';

    /**
     * @return string
     */
    public function build(): string;

    /**
     * @return string
     */
    public function getUri(): string;

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri(string $uri): UrlBuilderInterface;

}