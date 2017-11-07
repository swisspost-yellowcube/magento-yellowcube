<?php

/**
 * Model for the "Send All Product Data to YellowCube" button.
 *
 * Button is displayed on the bottom of the YellowCube configuration form and
 * allows admins to manually trigger update of the stock information.
 */
class Swisspost_YellowCube_Block_Adminhtml_System_Config_Art
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Url of the AJAX callback the button should execute.
     *
     * @var string
     */
    protected $ajaxUrl = '*/yellowcube_system_config_sync/art';

    /**
     * Sets button's template.
     *
     * @return Swisspost_YellowCube_Block_Adminhtml_System_Config_Art
     *   This object.
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('yellowcube/system/config/synchronize.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *   Element being rendered.
     *
     * @return string
     *   Element's HTML represenation.
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *   Element being rendered.
     *
     * @return string
     *   Element's HTML representation.
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => Mage::helper('swisspost_yellowcube')->__($originalData['button_label']),
            'html_id' => $element->getHtmlId(),
            'ajax_url' => Mage::getSingleton('adminhtml/url')->getUrl($this->ajaxUrl),
        ));

        return $this->_toHtml();
    }
}
