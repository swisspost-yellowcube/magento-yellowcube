<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$ycGroupName = 'Yellow Cube';

$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', $ycGroupName, 6);

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $ycGroupName);

$attributesToAdd = [
    'yc_requires_lot_management' => [
        'group'             => $ycGroupName,
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Requires Lot Management',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'eav/entity_attribute_source_boolean',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => false
    ],
    'yc_lot_info' => [
        'group'             => $ycGroupName,
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Lot Information',
        'input'             => 'textarea',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => ''
    ],
    'yc_most_recent_expiration_date' => [
        'group'             => $ycGroupName,
        'type'              => 'text',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Most recent expiration date',
        'input'             => 'text',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => false,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => ''
    ]

];

foreach ($attributesToAdd as $attributeCode => $attributeData) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode, $attributeData);
    $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeCode);
}

