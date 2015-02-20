<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Inventory
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

        Mage::log($this->getHelper()->__('YellowCube reports %d products with a stock level', count($stockItems)), Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);

        foreach ($stockItems as $product) {
            $this->update($product->getArticleNo(), array('qty' => $product->getQuantityUOM()->get()));
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

        $product->setStoreId($this->_getStoreId())
            ->load($productId);

        if (!$product->getId()) {
            Mage::log($this->getHelper()->__('Product %s inventory cannot be synchronized from YellowCube into Magento because it does not exist.', $productId));
            return $this;
        }

        /** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = $product->getStockItem();
        $stockData = array_replace($stockItem->getData(), (array)$data);
        $stockItem->setData($stockData);

        try {
            $stockItem->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * @return Swisspost_YellowCube_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('swisspost_yellowcube');
    }
}
