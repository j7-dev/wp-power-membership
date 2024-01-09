<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_Form_View___CSS_Base extends PowerPlugin_AdminPageFramework_FrameworkUtility {
    public $aAdded = array();
    public function add($sCSSRules)
    {
        $this->aAdded[] = $sCSSRules;
    }
    public function get()
    {
        $_sCSSRules = $this->_get() . PHP_EOL;
        $_sCSSRules .= $this->_getVersionSpecific();
        $_sCSSRules .= implode(PHP_EOL, $this->aAdded);
        return $_sCSSRules;
    }
    protected function _get()
    {
        return '';
    }
    protected function _getVersionSpecific()
    {
        return '';
    }
}
