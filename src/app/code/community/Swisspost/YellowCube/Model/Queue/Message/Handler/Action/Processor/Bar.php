<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Bar
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    /**
     * @param array $data
     * @return $this
     */
    public function process(array $data)
    {
        $stockItems = $this->getYellowCubeService()->getInventory();

        Mage::log($this->getHelper()->__('YellowCube reports %d products with a stock level', count($stockItems)), Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

        /* @var $article \YellowCube\BAR\Article */
        foreach ($stockItems as $article) {
           $this->update($article->getArticleNo(), array('qty' => $article->getQuantityUOM()->get()));
        }

        return $this;
    }

    /**
     * @param $productId
     * @param $data
     * @return $this
     */
    public function update($productId, $data)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        $idBySku = $product->getIdBySku($productId);
        $productId = $idBySku ? $idBySku : $productId;

        $product
            ->setStoreId(Mage::app()->getStore(0)->getId())
            ->load($productId);

        if (!$product->getId()) {
            Mage::log($this->getHelper()->__('Product %s inventory cannot be synchronized from YellowCube into Magento because it does not exist.', $productId), Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
            return $this;
        }

        /**
         * YellowCube stock - qty of products not yet shipped = new stock
         */
        $shipmentItemsCollection = Mage::getResourceModel('sales/order_shipment_item_collection');
        $shipmentItemsCollection
            ->addFieldToFilter('product_id', $product->getId())
            ->addFieldToSelect('additional_data')
            ->addFieldToSelect('qty');

        $qtyToDecrease = 0;
        foreach ($shipmentItemsCollection->getItems() as $shipment) {
            $additionalData = Zend_Json::decode($shipment->getAdditionalData());
            if (isset($additionalData['yc_shipped']) && $additionalData['yc_shipped'] === 0) {
                $qtyToDecrease += $shipment->getQty();
            } else {
                continue;
            }
        }

        $data['qty'] -= $qtyToDecrease;

        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();
        $stockData = array_replace($stockItem->getData(), (array)$data);
        $stockItem->setData($stockData);

        try {
            if ($this->getHelper()->getDebug()) {
                Mage::log($this->getHelper()->__('Product %s with the qty of %s will be saved..', $productId, $stockItem->getQty()), Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
            }
            $stockItem->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
