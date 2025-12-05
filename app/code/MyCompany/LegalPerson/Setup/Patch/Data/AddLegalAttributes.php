<?php
namespace MyCompany\LegalPerson\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;

class AddLegalAttributes implements DataPatchInterface
{
    private $moduleDataSetup;
    private $customerSetupFactory;
    private $addressAttributeRepository;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeRepositoryInterface $addressAttributeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->addressAttributeRepository = $addressAttributeRepository;
    }

    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute('customer_address', 'legal_cui', [
            'label' => 'CUI',
            'input' => 'text',
            'type' => 'varchar',
            'required' => false,
            'visible' => true,
            'system' => 0,
            'user_defined' => true,
            'position' => 150,
        ]);

        $customerSetup->addAttribute('customer_address', 'legal_company', [
            'label' => 'Nume Companie',
            'input' => 'text',
            'type' => 'varchar',
            'required' => false,
            'visible' => true,
            'system' => 0,
            'user_defined' => true, // <--- LINIE NOUA CRITICA
            'position' => 145,
        ]);

        $attributeSetId = $customerSetup->getDefaultAttributeSetId('customer_address');
        $attributeGroupId = $customerSetup->getDefaultAttributeGroupId('customer_address', $attributeSetId);

        $attributes = ['legal_cui', 'legal_company'];

        foreach ($attributes as $attributeCode) {
            $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
            $attribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
            ]);
            $this->addressAttributeRepository->save($attribute);
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
