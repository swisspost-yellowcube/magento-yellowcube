<?php

class Swisspost_YellowCube_Model_Queue
{
    const DEFAULT_NAME = 'default';

    public function getInstance()
    {
        $adapter = Mage::getModel('core/resource')->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        return new Zend_Queue('Db', array(
            'dbAdapter' => $adapter,
            'name' => self::DEFAULT_NAME
        ));
    }
}
