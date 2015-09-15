<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$ycGroupName = 'Yellow Cube';

$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', $ycGroupName, 6);

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $ycGroupName);

$attributesToAdd = array(

    'yc_ean_type' => array(
        'group'             => $ycGroupName,
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'EAN Type',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'swisspost_yellowcube/ean_type_source',
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
    ),
    'yc_ean_code' => array(
        'group'             => $ycGroupName,
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'EAN Code',
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
        'default'           => false
    ),
);

foreach ($attributesToAdd as $attributeCode => $attributeData) {
    $installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attributeCode, $attributeData);
    $installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeCode);
}


