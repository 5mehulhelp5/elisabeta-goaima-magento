<?php

namespace MyCompany\AdminLogger\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Actions implements OptionSourceInterface
{

    public function toOptionArray()
    {
        return [
            ['value' => 'view', 'label' => __('View')],
            ['value' => 'save', 'label' => __('Save')],
            ['value' => 'delete', 'label' => __('Delete')],
            ['value' => 'edit', 'label' => __('Edit')],
        ];
    }
}
