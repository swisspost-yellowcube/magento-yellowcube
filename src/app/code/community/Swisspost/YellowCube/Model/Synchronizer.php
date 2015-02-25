<?php

class Swisspost_YellowCube_Model_Synchronizer
{
    const SYNC_ACTION_INSERT                = 'insert';
    const SYNC_ACTION_UPDATE                = 'update';
    const SYNC_ACTION_DEACTIVATE            = 'deactivate';
    const SYNC_ORDER_NEW                    = 'order_new';
    const SYNC_ORDER_UPDATE                 = 'order_update';
    const SYNC_INVENTORY                    = 'inventory';

    /**
     * @var Zend_Queue
     */
    protected $_queue;

    public function action(Mage_Catalog_Model_Product $product, $action = self::SYNC_ACTION_INSERT)
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => $action,
            'website_id' => $product->getWebsiteId(),
            'plant_id' => $this->getHelper()->getPlantId(),
            'deposit_number' => $this->getHelper()->getDepositorNumber(),
            'product_id' => $product->getId(),
            'product_sku' => $product->getSku(),
            'product_weight' => $product->getWeight(),
            'product_name' => $product->getName(),
            'product_length' => $product->getData('yc_dimension_length'),
            'product_width' => $product->getData('yc_dimension_width'),
            'product_height' => $product->getData('yc_dimension_height'),
            'product_uom' => $product->getData('yc_dimension_uom'),
            'product_volume' => $product->getData('yc_dimension_height') * $product->getData('yc_dimension_length') *  $product->getData('yc_dimension_width'),
            'tara_factor' => Mage::getStoreConfig(Swisspost_YellowCube_Helper_Data::CONFIG_TARA_FACTOR, Mage::app()->getWebsite($product->getWebsiteId())->getDefaultStore()->getId()),
        )));

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function insert(Mage_Catalog_Model_Product $product)
    {
        $this->action($product);
        return $this;
    }

    /**
     * @return $this
     */
    public function updateAll()
    {
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToSelect(array(
            'name',
            'weight',
            'yc_sync_with_yellowcube',
            'yc_dimension_length',
            'yc_dimension_width',
            'yc_dimension_height',
            'yc_dimension_uom',
        ));
        $collection->addFieldToFilter('yc_sync_with_yellowcube', 1);

        foreach ($collection as $product) {
            $this->action($product, self::SYNC_ACTION_INSERT);
        }

        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function update(Mage_Catalog_Model_Product $product)
    {
        $this->action($product, self::SYNC_ACTION_UPDATE);
        return $this;
    }

    /**
     * @param Mage_Catalog_Model_Product $product
     * @return $this
     */
    public function deactivate(Mage_Catalog_Model_Product $product)
    {
        $this->action($product, self::SYNC_ACTION_DEACTIVATE);
        return $this;
    }

    /**
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return $this
     */
    public function ship(Mage_Shipping_Model_Shipment_Request $request)
    {
        $order = $request->getOrderShipment();
        $helper = Mage::helper('swisspost_yellowcube');

        $locale = Mage::getStoreConfig('general/locale/code', $request->getStoreId());
        $locale = explode('_', $locale);

        $positionItems = array();
        foreach ($order->getAllItems() as $item) {
            $positionItems[] = array(
                'article_id' => $item->getProductId(),
                'article_number' => $item->getSku(),
                'article_title' => $item->getName(),
                'article_qty' => $item->getQty(),
            );
        }

        $this->getQueue()->send(Zend_Json::encode(array(
            'action'    => self::SYNC_ORDER_NEW,
            'store_id'  => $request->getStoreId(),
            'plant_id'  => $this->getHelper()->getPlantId($request->getStoreId()),

            // Order Header
            'deposit_number'    => $this->getHelper()->getDepositorNumber($request->getStoreId()),
            'order_id'          => $order->getOrderId(),
            'order_date'        => date('Ymd'),

            // Partner Address
            'partner_type'          => Swisspost_YellowCube_Helper_Data::PARTNER_TYPE,
            'partner_number'        => $this->getHelper()->getPartnerNumber($request->getStoreId()),
            'partner_reference'     => $request->getRecipientEmail(),
            'partner_name'          => $request->getRecipientContactPersonName(),
            'partner_street'        => $request->getRecipientAddressStreet(),
            'partner_country_code'  => $request->getRecipientAddressCountryCode(),
            'partner_city'          => $request->getRecipientAddressCity(),
            'partner_zip_code'      => $request->getRecipientAddressPostalCode(),
            'partner_phone'         => $request->getRecipientContactPhoneNumber(),
            'partner_email'         => $request->getRecipientEmail(),
            'partner_language'      => $locale[0], // possible values expected de|fr|it|en ...

            // ValueAddedServices - AdditionalService
            'service_basic_shipping'      => $request->getShippingMethod(),
            'service_additional_shipping' => $helper->getAdditionalShipping($request->getShippingMethod()),

            // Order Positions
            'items' => $positionItems
        )));

        return $this;
    }

    /**
     * @return $this
     */
    public function syncInventoryWithYC()
    {
        $this->getQueue()->send(Zend_Json::encode(array(
            'action' => self::SYNC_INVENTORY
        )));
        return $this;
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

    /**
     * @return Swisspost_YellowCube_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('swisspost_yellowcube');
    }
}