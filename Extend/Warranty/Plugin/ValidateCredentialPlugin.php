<?php


namespace Extend\Warranty\Plugin;

use Extend\Warranty\Api\ConnectorInterface;
use Magento\Framework\Message\ManagerInterface;

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

    public function __construct(
        ConnectorInterface $connection,
        ManagerInterface $messageManager
    )
    {
        $this->connection = $connection;
        $this->messageManager = $messageManager;
    }

    public function afterSave(\Magento\Config\Model\Config $subject, $result)
    {
        if($this->connection->testConnection()){
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