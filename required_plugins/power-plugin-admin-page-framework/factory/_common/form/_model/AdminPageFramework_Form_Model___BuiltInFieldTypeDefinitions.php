<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_Model___BuiltInFieldTypeDefinitions {
    protected static $_aDefaultFieldTypeSlugs = array( 'default', 'text', 'number', 'textarea', 'radio', 'checkbox', 'select', 'hidden', 'file', 'submit', 'import', 'export', 'image', 'media', 'color', 'taxonomy', 'posttype', 'size', 'section_title', 'system', 'inline_mixed', '_nested', 'contact', 'table' );
    public $sCallerID = '';
    public $oMsg;
    public function __construct($sCallerID, $oMsg)
    {
        $this->sCallerID = $sCallerID;
        $this->oMsg = $oMsg;
    }
    public function get()
    {
        $_aFieldTypeDefinitions = array();
        foreach (self::$_aDefaultFieldTypeSlugs as $_sFieldTypeSlug) {
            $_sFieldTypeClassName = "PowerPlugin_AdminPageFramework_FieldType_{$_sFieldTypeSlug}";
            $_oFieldType = new $_sFieldTypeClassName($this->sCallerID, null, $this->oMsg, false);
            foreach ($_oFieldType->aFieldTypeSlugs as $_sSlug) {
                $_aFieldTypeDefinitions[ $_sSlug ] = $_oFieldType->getDefinitionArray();
            }
        }
        return $_aFieldTypeDefinitions;
    }
}
