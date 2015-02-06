<?php

class Swisspost_YellowCube_Model_Synchronizer
{
    const SYNC_ACTION_INSERT = 'insert';
    const SYNC_ACTION_UPDATE = 'update';
    const SYNC_ACTION_DEACTIVATE = 'deactivate';
    const SYNC_ACTION_DOWNLOAD_INVENTORY = 'inventorySync';

    /**
     * @var Zend_Queue
     */
    protected $_queue;

    public function insert(Mage_Catalog_Model_Product $product)
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => self::SYNC_ACTION_INSERT,
            'plant_id' => $this->getHelper()->getPlantId(),
            'deposit_number' => $this->getHelper()->getDepositorNumber(),
            'product_id' => $product->getId(),
            'product_sku' => $product->getSku(),
            'product_weight' => $product->getWeight(),
            'product_description' => mb_strcut($product->getDescription(), 0 , 40),
            'product_length' => $product->getData('yc_dimension_length'),
            'product_width' => $product->getData('yc_dimension_width'),
            'product_height' => $product->getData('yc_dimension_height'),
            'product_uom' => $product->getData('yc_dimension_uom'),
        )));
    }

    public function updateAll()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect(array(
            'description',
            'weight',
            'yc_sync_with_yellowcube',
            'yc_dimension_length',
            'yc_dimension_width',
            'yc_dimension_height',
            'yc_dimension_uom',
        ));
        $collection->addFieldToFilter('yc_sync_with_yellowcube', 1);

        foreach ($collection as $product) {
            $this->insert($product);
        }
    }

    public function update(Mage_Catalog_Model_Product $product)
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => self::SYNC_ACTION_UPDATE,
            'product_id' => $product->getId()
        )));
    }

    public function deactivate(Mage_Catalog_Model_Product $product)
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => self::SYNC_ACTION_DEACTIVATE,
            'product_id' => $product->getId()
        )));
    }

    public function syncInventoryWithYC()
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => self::SYNC_ACTION_DOWNLOAD_INVENTORY
        )));
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

    public function getHelper()
    {
        return Mage::helper('swisspost_yellowcube');
    }
}
