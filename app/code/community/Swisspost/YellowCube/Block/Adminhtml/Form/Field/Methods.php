<?php
/**
 * Liip AG
 *
 * @author      Sylvain Rayé <sylvain.raye at diglin.com>
 * @category    yellowcube
 * @package     Swisspost_yellowcube
 * @copyright   Copyright (c) 2015 Liip AG
 */

/**
 * Class Swisspost_YellowCube_Block_Adminhtml_Form_Field_Methods
 */
class Swisspost_YellowCube_Block_Adminhtml_Form_Field_Methods
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    /**
     * @var Swisspost_YellowCube_Block_Adminhtml_Form_Field_Codes
     */
    protected $_methodRenderer;

    /**
     * Retrieve group column renderer
     *
     * @return Swisspost_YellowCube_Block_Adminhtml_Form_Field_Codes
     */
    protected function _getMethodRenderer()
    {
        if (!$this->_methodRenderer) {
            $this->_methodRenderer = $this->getLayout()->createBlock(
                'swisspost_yellowcube/adminhtml_form_field_codes', '',
                array('is_render_to_js_template' => true)
            );
            $this->_methodRenderer->setClass('customer_group_select');
            $this->_methodRenderer->setExtraParams('style="width:120px"');
        }
        return $this->_methodRenderer;
    }

    /**
     * Prepare to render
     */
    protected function _prepareToRender()
    {
        $this->addColumn('allowed_methods', array(
            'label' => Mage::helper('swisspost_yellowcube')->__('Methods'),
            'renderer' => $this->_getMethodRenderer(),
        ));
        $this->addColumn('price', array(
            'label' => Mage::helper('swisspost_yellowcube')->__('Price'),
            'style' => 'width:100px',
        ));
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('swisspost_yellowcube')->__('Add Shipping Method');
    }

    /**
     * Prepare existing row data object
     *
     * @param Varien_Object
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_' . $this->_getMethodRenderer()->calcOptionHash($row->getData('allowed_methods')),
            'selected="selected"'
        );
    }
}
