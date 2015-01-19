<?php

class Swisspost_YellowCube_Model_Queue_Message_Handler
{
    public function process(array $data)
    {
        switch ($data['action']) {
            case Swisspost_YellowCube_Model_Synchronizer::SYNC_ACTION_INSERT:
                $this->_processInsert($data);
                break;
            case Swisspost_YellowCube_Model_Synchronizer::SYNC_ACTION_UPDATE:
                $this->_processUpdate($data);
                break;
            case Swisspost_YellowCube_Model_Synchronizer::SYNC_ACTION_DEACTIVATE:
                $this->_processDeactivate($data);
                break;
        }
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function _processInsert(array $data)
    {
        throw new Exception('not implemented');
        $uom = $data['product_uom'] === Swisspost_YellowCube_Model_Dimension_Uom_Attribute_Source::VALUE_MTR
            ? \YellowCube\ART\UnitsOfMeasure\ISO::MTR
            : \YellowCube\ART\UnitsOfMeasure\ISO::CMT;

        $article = new \YellowCube\ART\Article;
        $article
            ->setChangeFlag(\YellowCube\ART\ChangeFlag::INSERT)
            ->setPlantID('Y006')
            ->setDepositorNo('DEPOSITOR_NO')
            ->setBaseUOM(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setAlternateUnitISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setArticleNo($data['product_sku'])
            ->setNetWeight($data['product_weight'], \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->setLength($data['product_length'], $uom)
            ->setWidth($data['product_width'], $uom)
            ->setHeight($data['product_height'], $uom)
            ->addArticleDescription($data['product_description'], 'de');

        $service = new YellowCube\Service(YellowCube\Config::testConfig());
        $response = $service->insertArticleMasterData($article);
        if (!$response->isSuccess()) {
            throw new Exception($data['product_sku'] . ' not synced :(');
        }
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function _processUpdate(array $data)
    {
        throw new Exception('not implemented');
        $uom = $data['product_uom'] === Swisspost_YellowCube_Model_Dimension_Uom_Attribute_Source::VALUE_MTR
            ? \YellowCube\ART\UnitsOfMeasure\ISO::MTR
            : \YellowCube\ART\UnitsOfMeasure\ISO::CMT;

        $article = new \YellowCube\ART\Article;
        $article
            ->setChangeFlag(\YellowCube\ART\ChangeFlag::UPDATE)
            ->setPlantID('Y006')
            ->setDepositorNo('DEPOSITOR_NO')
            ->setBaseUOM(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setAlternateUnitISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setArticleNo($data['product_sku'])
            ->setNetWeight($data['product_weight'], \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->setLength($data['product_length'], $uom)
            ->setWidth($data['product_width'], $uom)
            ->setHeight($data['product_height'], $uom)
            ->addArticleDescription($data['product_description'], 'de');

        $service = new YellowCube\Service(YellowCube\Config::testConfig());
        $response = $service->insertArticleMasterData($article);
        if (!$response->isSuccess()) {
            throw new Exception($data['product_sku'] . ' not synced :(');
        }
    }

    /**
     * @param array $data
     * @throws Exception
     */
    protected function _processDeactivate(array $data)
    {
        throw new Exception('not implemented');
        $article = new \YellowCube\ART\Article;
        $article
            ->setChangeFlag(\YellowCube\ART\ChangeFlag::DEACTIVATE)
            ->setPlantID('Y006')
            ->setDepositorNo('DEPOSITOR_NO')
            ->setBaseUOM(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setAlternateUnitISO(\YellowCube\ART\UnitsOfMeasure\ISO::PCE)
            ->setArticleNo($data['product_sku'])
            ->setNetWeight($data['product_weight'], \YellowCube\ART\UnitsOfMeasure\ISO::KGM)
            ->addArticleDescription($data['product_description'], 'de');

        $service = new YellowCube\Service(YellowCube\Config::testConfig());
        $response = $service->insertArticleMasterData($article);
        if (!$response->isSuccess()) {
            throw new Exception($data['product_sku'] . ' not synced :(');
        }
    }
}
