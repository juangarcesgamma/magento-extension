<?php


namespace Extend\Warranty\Model;


use Extend\Warranty\Api\Data\UrlBuilderInterface;
use Extend\Catalog\Helper\Data as Config;

class UrlBuilder implements UrlBuilderInterface
{

    const DS = DIRECTORY_SEPARATOR;

    protected $config;

    protected $uri;


    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function build()
    {
        $baseUrl = $this->config->getValue('auth_mode') ?
            static::LIVE_URL :
            static::SANDBOX_URL;

        return rtrim($baseUrl, static::DS) .
            static::DS .
            ltrim($this->uri, static::DS);
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return (string)$this->uri;
    }

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }
}