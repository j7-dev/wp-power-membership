<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___CSS_widget extends PowerPlugin_AdminPageFramework_Form_View___CSS_Base {
    protected function _get()
    {
        return $this->_getWidgetRules();
    }
    private function _getWidgetRules()
    {
        return <<<CSSRULES
.widget .power-plugin-section .form-table>tbody>tr>td,.widget .power-plugin-section .form-table>tbody>tr>th{display:inline-block;width:100%;padding:0;float:right;clear:right}.widget .power-plugin-field,.widget .power-plugin-input-label-container{width:100%}.widget .sortable .power-plugin-field{padding:4% 4.4% 3.2% 4.4%;width:91.2%}.widget .power-plugin-field input{margin-bottom:.1em;margin-top:.1em}.widget .power-plugin-field input[type=text],.widget .power-plugin-field textarea{width:100%}@media screen and (max-width:782px){.widget .power-plugin-fields{width:99.2%}.widget .power-plugin-field input[type='checkbox'],.widget .power-plugin-field input[type='radio']{margin-top:0}}
CSSRULES;
    }
    protected function _getVersionSpecific()
    {
        $_sCSSRules = '';
        if (version_compare($GLOBALS[ 'wp_version' ], '3.8', '<')) {
            $_sCSSRules .= <<<CSSRULES
.widget .power-plugin-section table.mceLayout{table-layout:fixed}
CSSRULES;
        }
        if (version_compare($GLOBALS[ 'wp_version' ], '3.8', '>=')) {
            $_sCSSRules .= <<<CSSRULES
.widget .power-plugin-section .form-table th{font-size:13px;font-weight:400;margin-bottom:.2em}.widget .power-plugin-section .form-table{margin-top:1em}
CSSRULES;
        }
        return $_sCSSRules;
    }
}
