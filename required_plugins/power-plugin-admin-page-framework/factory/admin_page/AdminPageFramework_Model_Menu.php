<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_Model_Menu extends PowerPlugin_AdminPageFramework_Controller_Page {
    public function __construct($sOptionKey=null, $sCallerPath=null, $sCapability='manage_options', $sTextDomain='power-plugin')
    {
        parent::__construct($sOptionKey, $sCallerPath, $sCapability, $sTextDomain);
        new PowerPlugin_AdminPageFramework_Model_Menu__RegisterMenu($this);
    }
}
