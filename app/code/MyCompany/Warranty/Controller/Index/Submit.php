<?php

namespace MyCompany\Warranty\Controller\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Psr\Log\LoggerInterface;
use MyCompany\Warranty\Model\WarrantyFactory;

class Submit implements HttpPostActionInterface
{
    protected $resultJsonFactory;
    protected $request;
    protected $logger;
    protected $warrantyFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Http $request,
        LoggerInterface $logger,
        WarrantyFactory $warrantyFactory
    ){
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->logger = $logger;
        $this->warrantyFactory = $warrantyFactory;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try{
            $content = $this->request->getContent();
            $data = json_decode($content, true);

            if(empty($data['email']) || empty($data['order_id'])){
                return $result->setData([
                    'success' => false,
                    'message' => __('Email and order id are required.')
                ]);
            }

            $warrantyModel = $this->warrantyFactory->create();

            $warrantyModel->setData([
                'order_id' => $data['order_id'],
                'email' => $data['email'],
                'description' => $data['description'] ?? '',
            ]);

            $warrantyModel->save();

            $this->logger->info('Warranty saved via DB. ID: ' . $warrantyModel->getId());

            return $result->setData([
                'success' => true,
                'message' => __('Request submitted successfully.')
            ]);

        }
        catch (\Exception $exception){
            return $result->setData([
                'success' => false,
                'message' => 'Error '
            ]);
        }
    }
}
