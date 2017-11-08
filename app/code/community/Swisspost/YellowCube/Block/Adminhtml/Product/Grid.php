<?php

/**
 * Alters default admin product table to add YellowCube column.
 *
 * Adds column that displays the YellowCube column on the product admin table.
 */
class Swisspost_YellowCube_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    /**
     * Overrides setCollection().
     *
     * We would ideally override _prepareCollection() but the collection gets
     * loaded before we can alter it. In order to avoid this problem we
     * override this function which will be eventually called by
     * _prepareCollection() before the items will be loaded.
     */
    public function setCollection($collection)
    {
        $collection->addAttributeToSelect('yc_sync_with_yellowcube');
        return parent::setCollection($collection);
    }

    /**
     * Overrides _prepareColumns().
     *
     * Adds YellowCube status column to the products admin table.
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();
        $this->addColumn('yellowcube', array(
            'header'=> Mage::helper('catalog')->__('YellowCube status'),
            'width' => '40px',
            'index' => 'yc_sync_with_yellowcube',
            'type'  => 'options',
            'options' => [
                0 => Mage::helper('catalog')->__('Disabled'),
                1 => Mage::helper('catalog')->__('Enabled'),
            ],
        ));

        return $this;
    }
}
