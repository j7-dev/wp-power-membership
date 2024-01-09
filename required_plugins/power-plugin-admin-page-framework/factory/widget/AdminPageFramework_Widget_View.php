<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_Widget_View extends PowerPlugin_AdminPageFramework_Widget_Model {
    public function content($sContent, $aArguments, $aFormData)
    {
        return $sContent;
    }
    public function _printWidgetForm()
    {
        echo $this->oForm->get();
    }
}
