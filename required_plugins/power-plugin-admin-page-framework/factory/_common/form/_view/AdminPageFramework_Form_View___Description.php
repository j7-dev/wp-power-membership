<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___Description extends PowerPlugin_AdminPageFramework_FrameworkUtility {
    public $aDescriptions = array();
    public $sClassAttribute = 'power-plugin-form-element-description';
    public function __construct()
    {
        $_aParameters = func_get_args() + array( $this->aDescriptions, $this->sClassAttribute, );
        $this->aDescriptions = $this->getAsArray($_aParameters[ 0 ]);
        $this->sClassAttribute = $_aParameters[ 1 ];
    }
    public function get()
    {
        if (empty($this->aDescriptions)) {
            return '';
        }
        $_aOutput = array();
        foreach ($this->aDescriptions as $_sDescription) {
            $_aOutput[] = "<p class='" . esc_attr($this->sClassAttribute) . "'>" . "<span class='description'>" . $_sDescription . "</span>" . "</p>";
        }
        return implode(PHP_EOL, $_aOutput);
    }
}
