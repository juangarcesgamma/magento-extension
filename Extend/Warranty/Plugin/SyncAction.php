<?php

namespace Extend\Warranty\Plugin;

use Extend\Warranty\Controller\Adminhtml\Products\Sync;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SyncAction
{
    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder
    )
    {
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
    }
    public function aroundExecute(Sync $subject, callable $proceed)
    {
        if ($this->scopeConfig->isSetFlag('warranty/enableExtend/enable')) {
            return $proceed();
        }

        $redirect = $this->redirectFactory->create();

        $redirect
            ->setHttpResponseCode(403)
            ->setUrl($this->urlBuilder->getUrl('noroute'));

        return $redirect;
    }
}