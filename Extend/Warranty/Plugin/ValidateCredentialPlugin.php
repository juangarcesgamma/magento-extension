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
        if(!$subject->getSection() === 'warranty'){
            return $result;
        }

        if (!$this->helper->isExtendEnabled()) {
            $this->messageManager->addSuccessMessage(
                __("Extend is now disabled in your store. Your customers will be unable to see or buy Extend protection plans in your store until you re-enable the extension.")
            );
            return $result;
        }

        if (!$this->connection->testConnection()) {
            $this->messageManager->addNoticeMessage(
                __("Unable to connect to Extend Api with the credentials provided.")
            );
            return $result;
        }
        $this->messageManager->addSuccessMessage(
            __("Extend is now enabled in your store.")
        );

        $this->messageManager->addSuccessMessage(
            __("Connection to Extend Api successful.")
        );
        return $result;
    }
}