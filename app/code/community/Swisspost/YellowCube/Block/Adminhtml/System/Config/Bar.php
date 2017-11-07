<?php

/**
 * Model for the "Get Current Stock Data from YellowCube" button.
 *
 * Button is displayed on the bottom of the YellowCube configuration form and
 * allows admins to manually trigger update of the stock information.
 */
class Swisspost_YellowCube_Block_Adminhtml_System_Config_Bar
    extends Swisspost_YellowCube_Block_Adminhtml_System_Config_Art
{

    /**
     * Url of the AJAX callback the button should execute.
     *
     * @var string
     */
    protected $ajaxUrl = '*/yellowcube_system_config_sync/bar';

}
