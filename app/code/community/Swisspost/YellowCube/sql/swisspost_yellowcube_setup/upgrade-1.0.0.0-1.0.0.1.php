<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->run("UPDATE ". $installer->getTable('core/config_data') ." SET path = REPLACE(path,'swisspost_yellowcube','yellowcube') WHERE path LIKE '%swisspost_yellowcube%'");