<?php

namespace MyCompany\Warranty\Block;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\ObjectManager;

class FeaturedProduct extends Template
{
    protected $productRepository;
    protected $configurableType;
    protected $mediaConfig;

    public function __construct(
        Template\Context $context,
        ProductRepositoryInterface $productRepository,
        Configurable $configurableType,
        Config $mediaConfig,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->mediaConfig = $mediaConfig;
    }

    public function getProductData($sku){

        try{
            $product = $this->productRepository->get($sku);

            if($product->getTypeId() !== Configurable::TYPE_CODE){
                return null;
            }

            $children = $this->configurableType->getUsedProducts($product);
            $variants = [];

            foreach($children as $child){
                $sizeAttr = $child->getResource()->getAttribute('size');
                $sizeLabel = $sizeAttr ? $sizeAttr->getFrontend()->getValue($child) : 'N/A';

                $childImage = $child->getSmallImage();

                if ($childImage && $childImage !== 'no_selection') {
                    $imageUrl = $this->mediaConfig->getMediaUrl($childImage);
                } else {
                    $imageUrl = $this->mediaConfig->getMediaUrl($product->getSmallImage());
                }

                $variants[] = [
                    'id' => $child->getId(),
                    'sku' => $child->getSku(),
                    'size_label' => $sizeLabel,
                    'price' => $child->getPrice(),
                    'image' => $imageUrl
                ];
            }

            return [
                'name' => $product->getName(),
                'base_image' => $this->mediaConfig->getMediaUrl($product->getSmallImage()),
                'variants' => $variants,
            ];
        } catch (\Exception $e) {
            ObjectManager::getInstance()
                ->get(\Psr\Log\LoggerInterface::class)
                ->error('FeaturedProduct Widget Error: ' . $e->getMessage());

            return null;
        }
    }
}
