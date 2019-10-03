<?php


namespace Extend\Warranty\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Extend\Warranty\Helper\Api\Data;
use Extend\Warranty\Model\Keys;
use Extend\Warranty\Model\Api\Connector;

class Installation implements  ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var Keys
     */
    protected $keys;

    protected $connection;

    protected $enable;
    protected $storeId;

    public function __construct
    (
        StoreManagerInterface $storeManager,
        Data $data,
        Keys $keys,
        Connector $connection
    )
    {
        $this->storeManager = $storeManager;
        $this->data = $data;
        $this->keys = $keys;
        $this->connection = $connection;
    }

    public function prepareBlockData()
    {
        if ($this->data->isExtendEnabled() && $this->connection->testConnection()) {
            $this->enable = true;
            $keys = $this->keys->getKeys();
            $this->storeId = $keys['store_id'];
        } else {
            $this->enable = false;
        }
    }

    public function getExtendEnable()
    {
        return isset($this->enable) ? $this->enable : false;
    }

    public function getExtendStoreId()
    {
        return isset($this->storeId) ? $this->storeId : null;
    }

}