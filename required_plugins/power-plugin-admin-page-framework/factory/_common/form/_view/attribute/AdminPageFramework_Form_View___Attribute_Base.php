<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_Form_View___Attribute_Base extends PowerPlugin_AdminPageFramework_Form_Utility {
    public $sContext = '';
    public $aArguments = array();
    public $aAttributes = array();
    public function __construct()
    {
        $_aParameters = func_get_args() + array( $this->aArguments, $this->aAttributes, );
        $this->aArguments = $_aParameters[ 0 ];
        $this->aAttributes = $_aParameters[ 1 ];
    }
    public function get()
    {
        return $this->getAttributes($this->_getFormattedAttributes());
    }
    protected function _getFormattedAttributes()
    {
        return $this->aAttributes + $this->_getAttributes();
    }
    protected function _getAttributes()
    {
        return array();
    }
}
