<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___Generate_FieldAddress extends PowerPlugin_AdminPageFramework_Form_View___Generate_FlatFieldName {
    public function get()
    {
        return $this->_getFlatFieldName();
    }
    public function getModel()
    {
        return $this->get() . '|' . $this->sIndexMark;
    }
}
