<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_Model___Modifier_SortInput extends PowerPlugin_AdminPageFramework_Form_Model___Modifier_Base {
    public $aInput = array();
    public $aFieldAddresses = array();
    public function __construct()
    {
        $_aParameters = func_get_args() + array( $this->aInput, $this->aFieldAddresses, );
        $this->aInput = $_aParameters[ 0 ];
        $this->aFieldAddresses = $_aParameters[ 1 ];
    }
    public function get()
    {
        foreach ($this->_getFormattedDimensionalKeys($this->aFieldAddresses) as $_sFlatFieldAddress) {
            $_aDimensionalKeys = explode('|', $_sFlatFieldAddress);
            $_aDynamicElements = $this->getElement($this->aInput, $_aDimensionalKeys);
            if (! is_array($_aDynamicElements)) {
                continue;
            }
            $this->setMultiDimensionalArray($this->aInput, $_aDimensionalKeys, array_values($_aDynamicElements));
        }
        return $this->aInput;
    }
    private function _getFormattedDimensionalKeys($aFieldAddresses)
    {
        $aFieldAddresses = $this->getAsArray($aFieldAddresses);
        $aFieldAddresses = array_unique($aFieldAddresses);
        arsort($aFieldAddresses);
        return $aFieldAddresses;
    }
}
