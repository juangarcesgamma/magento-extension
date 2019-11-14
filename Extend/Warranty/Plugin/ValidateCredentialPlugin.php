<?php


namespace Extend\Warranty\Plugin;

use Extend\Warranty\Api\ConnectorInterface;
use Magento\Framework\Message\ManagerInterface;
use Extend\Warranty\Helper\Api\Data;

class ValidateCredentialPlugin
{
    /**
     * @var ConnectorInterface
     */
    protected $connection;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        ConnectorInterface $connection,
        ManagerInterface $messageManager,
        Data $helper
    )
    {
        $this->connection = $connection;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }

    public function afterSave(\Magento\Config\Model\Config $subject, $result)
    {
        if ($this->helper->isExtendEnabled()) {
            if ($this->connection->testConnection()) {
                $this->messageManager->addSuccessMessage(
                    __("Connection to Extend Api successful.")
                );
            } else {
                $this->messageManager->addNoticeMessage(
                    __("Unable to connect to Extend Api with the credentials provided.")
                );
            }
        }
    }
}