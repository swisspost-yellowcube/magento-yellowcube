<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler_Action_Processor_Insert
    extends Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorAbstract
    implements Swisspost_YellowCube_Model_Queue_Message_Handler_Action_ProcessorInterface
{
    /**
     * @param array $data
     * @throws Exception
     */
    public function process(array $data)
    {
        $uom = $data['product_uom'] === Swisspost_YellowCube_Model_Dimension_Uom_Attribute_Source::VALUE_MTR
            ? \YellowCube\ART\UnitsOfMeasure\ISO::MTR
            : \YellowCube\ART\UnitsOfMeasure\ISO::CMT;

        $article = new \YellowCube\ART\Article;
        $article
            ->setChangeFlag(\YellowCube\ART\ChangeFlag::INSERT)
            ->setPlantID('Y006')
            ->setDepositorNo('321654687')
            ->setBaseUOM(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setAlternateUnitISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setArticleNo($data['product_sku'])
            ->setNetWeight((int)$data['product_weight'], \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->setLength((int)$data['product_length'], $uom)
            ->setWidth((int)$data['product_width'], $uom)
            ->setHeight((int)$data['product_height'], $uom)
            ->addArticleDescription($data['product_description'], 'de');

        $response = $this->getYellowCubeService()->insertArticleMasterData($article);
        if (!is_object($response) || !$response->isSuccess()) {
            throw new Exception($data['product_sku'] . ' not synced :(');
        }
    }
}
