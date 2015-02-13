<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Insert
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    protected $_changeFlag = \YellowCube\ART\ChangeFlag::INSERT;

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function process(array $data)
    {
        $uom = $data['product_uom'] === Swisspost_YellowCube_Model_Dimension_Uom_Attribute_Source::VALUE_MTR
            ? \YellowCube\ART\UnitsOfMeasure\ISO::MTR
            : \YellowCube\ART\UnitsOfMeasure\ISO::CMT;

        $uomq = ($uom == \YellowCube\ART\UnitsOfMeasure\ISO::MTR) ? \YellowCube\ART\UnitsOfMeasure\ISO::MTQ : \YellowCube\ART\UnitsOfMeasure\ISO::CMQ;

        $article = new \YellowCube\ART\Article;
        $article
            ->setChangeFlag($this->_changeFlag)
            ->setPlantID($data['plant_id'])
            ->setDepositorNo($data['deposit_number'])
            ->setBaseUOM(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setAlternateUnitISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setArticleNo($this->formatSku($data['product_sku']))
            ->setNetWeight($this->formatUom($data['product_weight']), \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->setGrossWeight($this->formatUom($data['product_weight'] * $data['tara_factor']), \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->setLength($this->formatUom($data['product_length']), $uom)
            ->setWidth($this->formatUom($data['product_width']), $uom)
            ->setHeight($this->formatUom($data['product_height']), $uom)
            ->setVolume($this->formatUom($data['product_volume']), $uomq)
            ->addArticleDescription($this->formatDescription($data['product_name']), 'de'); // @todo provide the language of the current description (possible values de|fr|it|en)

        $response = $this->getYellowCubeService()->insertArticleMasterData($article);

        if (!is_object($response) || !$response->isSuccess()) {
            $message = Mage::helper('swisspost_yellowcube')->__('%s has an error with the insertArticleMasterData() Service', $data['product_sku']);
            Mage::log($message . print_r($response, true), Zend_Log::ERR, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE, true);
            Mage::throwException($message);
        } else if (Mage::helper('swisspost_yellowcube')->getDebug()) {
            Mage::log(print_r($response, true), Zend_Log::DEBUG, Swisspost_YellowCube_Helper_Data::YC_LOG_FILE);
        }

        return $this;
    }
}
