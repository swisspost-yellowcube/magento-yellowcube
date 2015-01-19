<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$ycGroupName = 'Yellow Cube';

$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', $ycGroupName, 6);

$entityTypeId     = $installer->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, $ycGroupName);

$attributesToAdd = array(
    'yc_sync_with_yellowcube' => array(
        'group'             => $ycGroupName,
        'type'              => 'int',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Sync With YellowCube',
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
    ),
    'yc_dimension_length' => array(
        'group'             => $ycGroupName,
        'type'              => 'decimal',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Length',
        'input'             => 'text',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => ''
    ),
    'yc_dimension_width' => array(
        'group'             => $ycGroupName,
        'type'              => 'decimal',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Width',
        'input'             => 'text',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => ''
    ),
    'yc_dimension_height' => array(
        'group'             => $ycGroupName,
        'type'              => 'decimal',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Height',
        'input'             => 'text',
        'class'             => '',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => true,
        'user_defined'      => false,
        'searchable'        => false,
        'filterable'        => false,
        'comparable'        => false,
        'visible_on_front'  => false,
        'unique'            => false,
        'apply_to'          => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
        'is_configurable'   => false,
        'default'           => ''
    ),
    'yc_dimension_uom' => array(
        'group'             => $ycGroupName,
        'type'              => 'varchar',
        'backend'           => '',
        'frontend'          => '',
        'label'             => 'Units of Measure',
        'input'             => 'select',
        'class'             => '',
        'source'            => 'swisspost_yellowcube/dimension_uom_attribute_source',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
        'visible'           => true,
        'required'          => true,
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


$table = $installer->getConnection()
    ->newTable('queue')
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true
    ))
    ->addColumn('queue_name', Varien_Db_Ddl_Table::TYPE_TEXT, 100, array(
        'nullable'  => false
    ))
    ->addColumn('timeout', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '30'
    ))
    ->setComment('Zend Queue Table');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable('message')
    ->addColumn('message_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true
    ))
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false
    ))
    ->addColumn('handle', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'default' => null,
    ))
    ->addColumn('body', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
        'nullable' => false,
    ))
    ->addColumn('md5', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable' => false,
    ))
    ->addColumn('timeout', Varien_Db_Ddl_Table::TYPE_DECIMAL, '14,4', array(
        'unsigned' => true,
        'default'  => null
    ))
    ->addColumn('created', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
        'nullable' => false,
    ))
    ->addIndex('message_handle', array('handle'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        'message_ibfk_1',
        'queue_id',
        'queue',
        'queue_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Zend Queue Message Table');
$installer->getConnection()->createTable($table);
