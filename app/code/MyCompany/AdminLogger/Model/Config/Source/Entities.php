<?php

namespace MyCompany\AdminLogger\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

// if we want to populate a dropdown we need to implement OptionSourceInterface
class Entities implements OptionSourceInterface
{
    // this function is called b default by magento when the page will be rendered
    // value is saved in db and label is displayed to the user
    public function toOptionArray()
    {
        return [
            ['value' => 'product', 'label' => __('Product')],
            ['value' => 'category', 'label' => __('Category')],
            ['value' => 'customer', 'label' => __('Customer')],
            ['value' => 'order', 'label' => __('Order')],
            ['value' => 'cms_page', 'label' => __('CMS Page')],
        ];
    }
}
