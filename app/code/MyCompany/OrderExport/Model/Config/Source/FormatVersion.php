<?php
namespace MyCompany\OrderExport\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class FormatVersion implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'standard', 'label' => __('Standard (V1)')],
            ['value' => 'detailed', 'label' => __('Detailed (V2) - with Items')],
        ];
    }
}
