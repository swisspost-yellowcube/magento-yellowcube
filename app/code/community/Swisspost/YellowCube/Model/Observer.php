<?php

class Swisspost_YellowCube_Model_Observer
{
    const CONFIG_PATH_PSR0NAMESPACES = 'global/psr0_namespaces';

    static $shouldAdd = true;

    protected $_attributeProductIds = array();

    /**
     * Event
     * - catalog_product_attribute_update_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function handleAttributeProductSave(Varien_Event_Observer $observer)
    {
        $productIds = $observer->getEvent()->getProductIds();
        $attributesData = $observer->getEvent()->getAttributesData();
        $storeId = $observer->getEvent()->getStoreId();

        $actionResource = Mage::getResourceModel('catalog/product_action');
        $yc = $actionResource->getAttribute('yc_sync_with_yellowcube');
        $ycl = $actionResource->getAttribute('yc_dimension_length');
        $ycw = $actionResource->getAttribute('yc_dimension_width');
        $ych = $actionResource->getAttribute('yc_dimension_height');
        $ycuom = $actionResource->getAttribute('yc_dimension_uom');
        $weight = $actionResource->getAttribute('weight');

        if (!$yc->getId()) {
            return;
        }

        foreach ($productIds as $key => $productId) {
            $productYCSync = $this->getAttributeData($productId, $storeId, $yc->getId());
            $productYCUom = $this->getAttributeData($productId, $storeId, $ycuom->getId(), 'varchar');
            $productYCLength = $this->getAttributeData($productId, $storeId, $ycl->getId(), 'decimal');
            $productYCWidth = $this->getAttributeData($productId, $storeId, $ycw->getId(), 'decimal');
            $productYCHeight = $this->getAttributeData($productId, $storeId, $ych->getId(), 'decimal');
            $productWeight = $this->getAttributeData($productId, $storeId, $weight->getId(), 'decimal');

            /**
             * If length/width/height in product is null => do nothing and doesn't allow to change the value
             */
            if (empty ($productYCLength) && empty($productYCWidth) && empty($productYCHeight)
                && empty($attributesData['yc_dimension_length']) && empty($attributesData['yc_dimension_width']) && empty($attributesData['yc_dimension_height'])
                && !empty($attributesData['yc_sync_with_yellowcube'])
            ) {

                if ((int) $productYCSync['value'] !== (int) $attributesData['yc_sync_with_yellowcube']) {
                    // Prepare to revert the changes - Note: cannot modify $productIds per reference as it is Mage_Catalog_Model_Product_Action::updateAttributes
                    $this->_attributeProductIds[$productId]['yc_sync_with_yellowcube'] = $productYCSync;
                }
                continue;
            }

            if (count($productYCSync) > 0) {
                if (isset($attributesData['yc_sync_with_yellowcube']) && (int) $productYCSync['value'] !== (int) $attributesData['yc_sync_with_yellowcube']) {
                    switch ((int) $attributesData['yc_sync_with_yellowcube']) {
                        case 0:
                            $this->getSynchronizer()->deactivate(Mage::getModel('catalog/product')->load($productId));
                            break;
                        case 1:
                            $this->getSynchronizer()->insert(Mage::getModel('catalog/product')->load($productId));
                            break;
                    }
                } else if ((int) $productYCSync['value']) {
                    // We handle size and weight changes if YC is enabled
                    if ((isset($attributesData['yc_dimension_length']) && $productYCLength['value'] != $attributesData['yc_dimension_length'])
                        || (isset($attributesData['yc_dimension_width']) && $productYCWidth['value'] != $attributesData['yc_dimension_width'])
                        || (isset($attributesData['yc_dimension_height']) && $productYCHeight['value'] != $attributesData['yc_dimension_height'])
                        || (isset($attributesData['yc_dimension_uom']) && $productYCUom['value'] != $attributesData['yc_dimension_uom'])
                        || (isset($attributesData['weight']) && $productWeight['value'] != $attributesData['weight'])
                    ) {
                        $this->getSynchronizer()->update(Mage::getModel('catalog/product')->load($productId));
                    }
                }
            }
        }
    }

    /**
     * Event
     * - catalog_product_attribute_update_after
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleAttributeProductSaveAfter(Varien_Event_Observer $observer)
    {
        if (count($this->_attributeProductIds) > 0) {
            $actionResource = Mage::getResourceModel('catalog/product_action');
            foreach ($this->_attributeProductIds as $productId => $attributes) {
                foreach ($attributes as $key => $attribute) {
                    if ($key == 'yc_sync_with_yellowcube') {
                        // Revert the changes done during the mass update of product attributes only if products doesn't have length/width/height
                        $actionResource->updateAttributes(array($productId), array($key => $attribute['value']), $attribute['store_id']);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $productId
     * @param $storeId
     * @param $attributeId
     * @param string $type
     * @return array
     */
    public function getAttributeData($productId, $storeId, $attributeId, $type = 'int')
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('catalog_read');

        $select = $read->select()
            ->from($resource->getTableName('catalog_product_entity_' . $type))
            ->where('attribute_id = ?', $attributeId)
            ->where('entity_id = ?', $productId)
            ->where('store_id = ?', $storeId);

        return $read->fetchRow($select);
    }

    /**
     * Event
     * - catalog_product_save_before
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Exception
     */
    public function handleBeforeProductSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getDataObject();
        $helper = Mage::helper('swisspost_yellowcube');

        if ((bool)$product->getData('yc_sync_with_yellowcube') && $product->getWeight() > 30) {
            Mage::throwException($helper->__('The weight cannot be higher than 30 kilograms if YellowCube is enabled.'));
        }
    }

    /**
     * Event
     * - catalog_product_save_after
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleProductSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getDataObject();

        // @todo Make it work with multiple websites. Note: value of yc_sync_with_yellowcube on product save doesn't reflect the value of the default view if "Use default Value" is checked

        $helper = Mage::helper('swisspost_yellowcube');
        if (!$helper->isConfigured(/* $storeId */) && (bool)$product->getData('yc_sync_with_yellowcube')) {
            Mage::throwException($helper->__('Please, configure YellowCube before to save the product having YellowCube option enabled.'));
        } else if (!$helper->isConfigured(/* $storeId */)) {
            return $this;
        }

        /**
         * Scenario
         *
         * - product is disabled or enabled => no change
         * - yc_sync_with_yellowcube is Yes/No
         *   - From No to Yes => insert into YC
         *   - From No to No => no change
         *   - From Yes to No => deactivate from YC
         *
         * - if duplicate, we do nothing as the attribute 'yc_sync_with_yellowcube' = 0
         */

        if ((bool)$product->getData('yc_sync_with_yellowcube') && $this->hasDataChangedFor($product, array('yc_sync_with_yellowcube'))) {
            Mage::log('Insert product ' . $product->getId());
            $this->getSynchronizer()->insert($product);
            return $this;
        }

        if (!(bool)$product->getData('yc_sync_with_yellowcube') && $this->hasDataChangedFor($product, array('yc_sync_with_yellowcube'))) {
            Mage::log('Deactivate product ' . $product->getId());
            $this->getSynchronizer()->deactivate($product);
            return $this;
        }

        if (!(bool)$product->getData('yc_sync_with_yellowcube')) {
            Mage::log('Product ignored ' . $product->getId());
            return $this;
        }

        if ($this->hasDataChangedFor($product, array('name', 'weight', 'yc_dimension_length', 'yc_dimension_width', 'yc_dimension_height', 'yc_dimension_uom'))) {
            Mage::log('Updated product ' . $product->getId());
            $this->getSynchronizer()->update($product);
            return $this;
        }
    }

    /**
     * Event
     * - catalog_product_delete_before
     *
     * @param Varien_Event_Observer $observer
     */
    public function handleProductDelete(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getDataObject();
        $this->getSynchronizer()->deactivate($product);
    }

    /**
     * Event:
     * - catalog_model_product_duplicate
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleProductDuplicate(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $newProduct */
        $newProduct = $observer->getEvent()->getNewProduct();
        $newProduct->setData('yc_sync_with_yellowcube', 0);

        return $this;
    }

    /**
     * Event
     * - sales_order_shipment_save_before
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function handleShipmentSaveBefore(Varien_Event_Observer $observer)
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $observer->getShipment();
        $carrier = $shipment->getOrder()->getShippingCarrier();

        if ($carrier instanceof Swisspost_YellowCube_Model_Shipping_Carrier_Rate && $shipment->getOrder()->getIsInProcess()) {
            Mage::getModel('shipping/shipping')->requestToShipment($shipment);
        }

        return $this;
    }

    /**
     * Add a message to the queue to sync the YellowCube Inventory with Magento Products
     *
     * @return $this
     */
    public function handleInventory()
    {
        $this->getSynchronizer()->syncInventoryWithYC();
        return $this;
    }

    /**
     * @return Swisspost_YellowCube_Model_Synchronizer
     */
    public function getSynchronizer()
    {
        return Mage::getSingleton('swisspost_yellowcube/synchronizer');
    }

    /**
     * Check whether specified attribute has been changed for given entity
     *
     * @param Mage_Core_Model_Abstract $entity
     * @param string|array $key
     * @return bool
     */
    public function hasDataChangedFor(Mage_Core_Model_Abstract $entity, $key)
    {
        if (is_array($key)) {
            foreach ($key as $code) {
                if ($entity->getOrigData($code) !== $entity->getData($code)) {
                    return true;
                }
            }
            return false;
        }
        return $entity->getOrigData($key) !== $entity->getData($key);
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isProductNew(Mage_Catalog_Model_Product $product)
    {
        return $product->isObjectNew()
        || (($product->getOrigData('sku') == '') && (strlen($product->getData('sku')) > 0));
    }

    /**
     * @return array
     */
    protected function _getNamespacesToRegister()
    {
        $namespaces = array();
        $node = Mage::getConfig()->getNode(self::CONFIG_PATH_PSR0NAMESPACES);
        if ($node && is_array($node->asArray())) {
            $namespaces = array_keys($node->asArray());
        }
        return $namespaces;
    }

    /**
     * Add PSR-0 Autoloader for our Yellowcube library
     *
     * Event
     * - resource_get_tablename
     * - add_spl_autoloader
     */
    public function addAutoloader()
    {
        if (!self::$shouldAdd) {
            return;
        }

        foreach ($this->_getNamespacesToRegister() as $namespace) {
            $namespace = str_replace('_', '/', $namespace);
            if (is_dir(Mage::getBaseDir('lib') . DS . $namespace)) {
                $args = array($namespace, Mage::getBaseDir('lib') . DS . $namespace);
                $autoloader = Mage::getModel("swisspost_yellowcube/splAutoloader", $args);
                $autoloader->register();
            }
        }

        self::$shouldAdd = false;
        return $this;
    }

    /**
     * @return Mage_Core_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('core/session');
    }
}
