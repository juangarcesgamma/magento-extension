<?php

namespace Extend\Catalog\Api\Data;

interface UrlBuilderInterface
{
    const SANDBOX_URL = 'https://api-stage.helloextend.com/';

    const LIVE_URL = 'https://api.helloextend.com/';

    /**
     * @return string
     */
    public function build();

    /**
     * @return string
     */
    public function getUri();

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri);

}