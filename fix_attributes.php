<?php
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();

$state = $obj->get(\Magento\Framework\App\State::class);
try {
    $state->setAreaCode('adminhtml');
} catch (\Exception $e) {
    // Ignoram daca area code este deja setat
}

/** @var \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory */
$eavSetupFactory = $obj->get(\Magento\Eav\Setup\EavSetupFactory::class);
/** @var \Magento\Framework\Setup\ModuleDataSetupInterface $setup */
$setup = $obj->get(\Magento\Framework\Setup\ModuleDataSetupInterface::class);
/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = $obj->get(\Magento\Eav\Model\Config::class);

$eavSetup = $eavSetupFactory->create(['setup' => $setup]);

$entityTypeId = $eavSetup->getEntityTypeId('customer_address');
$attributes = ['legal_cui', 'legal_company'];

echo "Incepem repararea atributelor...\n";

foreach ($attributes as $code) {
    echo "Procesez atributul: $code\n";

    // 1. Verificam daca atributul exista
    $attributeId = $eavSetup->getAttributeId($entityTypeId, $code);

    if (!$attributeId) {
        echo " -> EROARE: Atributul nu a fost gasit in baza de date. Ruleaza intai setup:upgrade.\n";
        continue;
    }

    // 2. Fortam sa fie User Defined (pentru a permite salvarea din Frontend)
    // Nota: Nu folosim is_system deoarece nu exista coloana in M2
    $eavSetup->updateAttribute($entityTypeId, $code, 'is_user_defined', 1);
    $eavSetup->updateAttribute($entityTypeId, $code, 'visible', 1);
    echo " -> Setat ca User Defined si Visible.\n";

    // 3. Adaugam atributul in Setul de Atribute (Default -> General)
    // Asta il face sa apara in Admin si sa fie valid la salvare
    $eavSetup->addAttributeToSet(
        $entityTypeId,
        'Default',
        'General',
        $attributeId
    );
    echo " -> Adaugat in Attribute Set.\n";

    // 4. Setam Formularele (used_in_forms)
    // Fara asta, Magento nu stie ca atributul apartine de 'Address Edit'
    $attributeModel = $eavConfig->getAttribute('customer_address', $code);
    if ($attributeModel && $attributeModel->getId()) {
        $usedInForms = [
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address'
        ];

        $attributeModel->setData('used_in_forms', $usedInForms);
        $attributeModel->save();
        echo " -> Formulare actualizate (used_in_forms).\n";
    } else {
        echo " -> Avertisment: Nu am putut incarca modelul atributului pentru used_in_forms.\n";
    }
}

echo "GATA! Sterge cache-ul si testeaza.\n";
