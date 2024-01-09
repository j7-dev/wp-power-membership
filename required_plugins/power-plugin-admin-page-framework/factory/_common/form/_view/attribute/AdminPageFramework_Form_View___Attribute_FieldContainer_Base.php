<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_Form_View___Attribute_FieldContainer_Base extends PowerPlugin_AdminPageFramework_Form_View___Attribute_Base {
    protected function _getFormattedAttributes()
    {
        $_aAttributes = $this->uniteArrays($this->getElementAsArray($this->aArguments, array( 'attributes', $this->sContext )), $this->aAttributes + $this->_getAttributes());
        $_aAttributes[ 'class' ] = $this->getClassAttribute($this->getElement($_aAttributes, 'class', array()), $this->getElement($this->aArguments, array( 'class', $this->sContext ), array()));
        return $_aAttributes;
    }
}
