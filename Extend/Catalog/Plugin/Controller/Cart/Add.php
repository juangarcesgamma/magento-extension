<?php

namespace Extend\Catalog\Plugin\Controller\Cart;

use Magento\Checkout\Controller\Cart\Add as SuperAdd;
use \Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Cart as CustomerCart;
use Extend\Catalog\ViewModel\Warranty;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Add
{
    protected $request;
    protected $cart;
    protected $warrantyViewModel;
    protected $logger;

    public function __construct
    (
        Http $request,
        CustomerCart $cart,
        Warranty $warrantyViewModel,
        LoggerInterface $logger
    )
    {
        $this->request = $request;
        $this->cart = $cart;
        $this->warrantyViewModel = $warrantyViewModel;
        $this->logger = $logger;
    }

    public function afterExecute(SuperAdd $subject, $result)
    {
        $productId = $this->request->getPost('product');
        $this->logger->info($productId);
        $warrantyData = $this->request->getPost('warranty');
        $selectedWarrantyId = key($warrantyData);
        $warranties = $this->warrantyViewModel->getWarranties($productId);
        try{
            $this->logger->info($warranties[$selectedWarrantyId]->getName());
            $this->cart->addProduct($warranties[$selectedWarrantyId],1);
            $this->cart->save();
        }catch (LocalizedException $e){
            $this->logger->info($e->getMessage());
        }

        return $result;
    }


}