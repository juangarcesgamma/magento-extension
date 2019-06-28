<?php


namespace Extend\Warranty\Plugin;





use Extend\Warranty\Helper\ConnectionData;
use Extend\Warranty\Model\Connection;
use Magento\Framework\Message\ManagerInterface;

class ValidateCredentialPlugin
{
    protected $connection;
    protected $connectionData;
    protected $messageManager;

    public function __construct(Connection $connection, ConnectionData $connectionData, ManagerInterface $messageManager)
    {
        $this->connection = $connection;
        $this->connectionData = $connectionData;
        $this->messageManager = $messageManager;
    }

    public function afterSave(\Magento\Config\Model\Config $subject, $result)
    {

        $newStoreId = $this->connectionData->getStoreIdCredential();
        $newApiKey = $this->connectionData->getApiKey();

        $statusCode = $this->connection->testConnection($newStoreId, $newApiKey);
        if($statusCode === '200'){
            $this->messageManager->addSuccessMessage(__("Connection to Extend Api successful."));
        } else {
            $this->messageManager->addNoticeMessage(__("Unable to connect to Extend Api with the credentials provided."));
        }

    }
}