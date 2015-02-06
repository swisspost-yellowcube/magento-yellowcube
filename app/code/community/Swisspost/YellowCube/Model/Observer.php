<?php

class Swisspost_YellowCube_Model_Observer
{
    const CONFIG_PATH_PSR0NAMESPACES = 'global/psr0_namespaces';

    static $shouldAdd = true;

    /**
     * @param Varien_Event_Observer $observer
     */
    public function handleProductSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getEvent()->getDataObject();

        if (!(bool)$product->getData('yc_sync_with_yellowcube')) {
            return;
        }

        if (!$product->isDisabled()
            && ($this->isProductNew($product) || $this->hasDataChangedFor($product, array('name', 'weight')))
        ) {
            $this->getSynchronizer()->update($product);
        }

        if ($product->hasData('sku') && $this->hasDataChangedFor($product, 'status')) {
            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_DISABLED) {
                $this->getSynchronizer()->deactivate($product);
            } else {
                $this->getSynchronizer()->update($product);
            }
        }
    }

    /**
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
}
