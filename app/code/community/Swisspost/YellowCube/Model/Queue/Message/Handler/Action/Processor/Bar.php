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
        foreach ($stockItems as $article)
        {
            $articleNo = $article->getArticleNo();
            $articleLot = $article->getLot();

            //todo @psa make sure this gives NULL if empty
            if (!is_null($article->getLot()))
            {
                $lotSummary[$articleNo]['qty'] = $article->getQuantityUOM()->get();
                $lotSummary[$articleNo]['lotInfo'] = 'Lot: ' . $articleLot . " Quantity: " . (int)$article->getQuantityUOM()->get() . ' ExpDate: ' . $this->convertYCDate($article->getBestBeforeDate()) . PHP_EOL;
                $lotSummary[$articleNo]['recentExpDate'] = $article->getBestBeforeDate();

                foreach ($stockItems as $article2)
                {
                    $article2No = $article2->getArticleNo();
                    $article2Lot = $article2->getLot();
                    //only do this if its not the lot already iterating
                    if ($articleNo == $article2No && $articleLot != $article2Lot)
                    {
                        $lotSummary[$articleNo]['qty'] = $lotSummary[$articleNo]['qty'] + $article2->getQuantityUOM()->get();
                        $lotSummary[$articleNo]['lotInfo'] = $lotSummary[$articleNo]['lotInfo']  . 'Lot: ' . $article2Lot . " Quantity: " . (int)$article2->getQuantityUOM()->get() . ' ExpDate: ' . $this->convertYCDate($article2->getBestBeforeDate()) . PHP_EOL;
                        $lotSummary[$articleNo]['recentExpDate'] = $article2->getBestBeforeDate() < $lotSummary[$articleNo]['recentExpDate'] ? $article2->getBestBeforeDate() : $lotSummary[$articleNo]['recentExpDate'];
                    }
                }
            }
            else
            {
                $lotSummary[$articleNo]['qty'] = $article->getQuantityUOM()->get();
                $lotSummary[$articleNo]['lotInfo'] = null;
                $lotSummary[$articleNo]['recentExpDate'] = null;
            }
        }

        foreach ($lotSummary as $articleNo => $articleData)
        {
            //todo do the update here
            $this->update($articleNo, $articleData);
        }

        Mage::log(print_r($lotSummary, true),Zend_Log::INFO, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);

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
         * YellowCube lot - Handle the Lot information for the product
         */
        if (!is_null($data['recentExpDate'])) //only do lot info if there is lot info available
        {
            $action = Mage::getModel('catalog/resource_product_action');
            $action->updateAttributes(array($productId), array(
                'yc_lot_info' => $data['lotInfo'],
                'yc_most_recent_expiration_date' => $this->convertYCDate($data['recentExpDate'])
            ), Mage::app()->getStore(0)->getId());
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

    /**
     * @param $date
     * @return date
     */
    protected function convertYCDate($date)
    {
        return date('d.m.Y',strtotime($date));
    }
}
