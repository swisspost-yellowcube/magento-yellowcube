<?php

class Swisspost_YellowCube_Model_Synchronizer
{
    const SYNC_ACTION_INSERT = 'insert';
    const SYNC_ACTION_UPDATE = 'update';
    const SYNC_ACTION_DEACTIVATE = 'deactivate';

    /**
     * @var Zend_Queue
     */
    protected $_queue;

    public function updateAll()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect('yc_sync_with_yellowcube');
        $collection->addFieldToFilter('yc_sync_with_yellowcube', 1);

        foreach ($collection as $product) {
            /** @var Mage_Catalog_Model_Product $product */
            $data = array(
                'action' => self::SYNC_ACTION_INSERT,
                'product_id' => $product->getId(),
                'product_sku' => $product->getSku(),
                'product_weight' => $product->getWeight(),
                'product_description' => $product->getDescription(),
                'product_length' => $product->getData('yc_dimension_length'),
                'product_width' => $product->getData('yc_dimension_width'),
                'product_height' => $product->getData('yc_dimension_height'),
                'product_uom' => $product->getData('yc_dimension_uom'),
            );
            $this->getQueue()->send(Zend_Json::encode($data));
        }
    }

    public function update(Mage_Catalog_Model_Product $product)
    {
        $data = array(
            'action' => self::SYNC_ACTION_UPDATE,
            'product_id' => $product->getId()
        );
        $this->getQueue()->send(Zend_Json::encode($data));
    }

    public function deactivate(Mage_Catalog_Model_Product $product)
    {
        $data = array(
            'action' => self::SYNC_ACTION_DEACTIVATE,
            'product_id' => $product->getId()
        );
        $this->getQueue()->send(Zend_Json::encode($data));
    }

    /**
     * @return Zend_Queue
     */
    public function getQueue()
    {
        if (null === $this->_queue) {
            $this->_queue = Mage::getModel('swisspost_yellowcube/queue')->getInstance();
        }
        return $this->_queue;
    }
}
