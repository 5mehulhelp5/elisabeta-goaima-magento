<?php
namespace MyCompany\LegalPerson\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

class AddAddressDetailsAttributes implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;
    private $attributeRepository;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeRepository = $attributeRepository;
    }

    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributes = [
            'legal_cui'     => 'CUI',
            'legal_company' => 'Company Name',
            'street_number' => 'Number',
            'building'      => 'Building',
            'floor'         => 'Floor',
            'apartment'     => 'Apartment',
        ];

        $attributeSetId = $customerSetup->getDefaultAttributeSetId('customer_address');
        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId('customer_address', $attributeSetId);

        $sortOrder = 80;

        foreach ($attributes as $code => $label) {
            $customerSetup->addAttribute('customer_address', $code, [
                'label' => $label,
                'input' => 'text',
                'type' => 'varchar',
                'required' => false,
                'visible' => true,
                'system' => 0,
                'user_defined' => true,
                'position' => $sortOrder++,
            ]);

            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', $code);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            ]);
            $this->attributeRepository->save($attribute);
        }
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
