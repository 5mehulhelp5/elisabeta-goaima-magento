<?php
namespace MyCompany\LegalPerson\Console\Command;

use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixAttributesCommand extends Command
{
    protected $eavSetupFactory;
    protected $moduleDataSetup;
    protected $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('legalperson:fix:attributes')
            ->setDescription('Fix address attributes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $entityTypeId = $eavSetup->getEntityTypeId('customer_address');
        $attributeSetId = $eavSetup->getDefaultAttributeSetId($entityTypeId);
        $attributeGroupId = $eavSetup->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

        $output->writeln("Entity Type ID: $entityTypeId");
        $output->writeln("Attribute Set ID: $attributeSetId");
        $output->writeln("Attribute Group ID: $attributeGroupId");

        $attributes = ['street_number', 'building', 'floor', 'apartment', 'legal_cui', 'legal_company'];

        foreach ($attributes as $code) {
            $attribute = $this->eavConfig->getAttribute('customer_address', $code);

            if ($attribute && $attribute->getId()) {
                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetId,
                    $attributeGroupId,
                    $code,
                    100
                );

                $attribute->setData('used_in_forms', [
                    'adminhtml_customer_address',
                    'customer_address_edit',
                    'customer_register_address'
                ]);

                $attribute->setData('is_user_defined', 1);
                $attribute->setData('is_system', 0);
                $attribute->setData('is_visible', 1);
                $attribute->save();

            }
        }

        return 0;
    }
}
