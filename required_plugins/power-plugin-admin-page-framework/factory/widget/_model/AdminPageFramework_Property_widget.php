<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Property_widget extends PowerPlugin_AdminPageFramework_Property_Base {
    public $_sPropertyType = 'widget';
    public $sStructureType = 'widget';
    public $sClassName = '';
    public $sCallerPath = '';
    public $sWidgetTitle = '';
    public $aWidgetArguments = array();
    public $bShowWidgetTitle = true;
    public $oWidget;
    public $sSettingNoticeActionHook = '';
    public $bAssumedAsWPWidget = false;
    public $aWPWidgetMethods = array();
    public $aWPWidgetProperties = array();
    public function __construct($oCaller, $sCallerPath, $sClassName, $sCapability='manage_options', $sTextDomain='power-plugin', $sStructureType='')
    {
        $this->_sFormRegistrationHook = 'load_' . $sClassName;
        $this->sSettingNoticeActionHook = 'load_' . $sClassName;
        parent::__construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sStructureType);
    }
    public function getFormArguments()
    {
        return array( 'caller_id' => $this->sClassName, 'structure_type' => $this->_sPropertyType, 'action_hook_form_registration' => $this->_sFormRegistrationHook, ) + $this->aFormArguments;
    }
}
