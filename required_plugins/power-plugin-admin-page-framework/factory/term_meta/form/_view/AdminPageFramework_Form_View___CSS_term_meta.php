<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___CSS_term_meta extends PowerPlugin_AdminPageFramework_Form_View___CSS_Base {
    protected function _get()
    {
        return $this->_getRules();
    }
    private function _getRules()
    {
        return <<<CSSRULES
.power-plugin-form-table-outer-row-term_meta,.power-plugin-form-table-outer-row-term_meta>td{margin:0;padding:0}.power-plugin-form-table-term_meta>tbody>tr>td{margin-left:0;padding-left:0}.power-plugin-form-table-term_meta .power-plugin-sectionset,.power-plugin-form-table-term_meta .power-plugin-section{margin-bottom:0}.power-plugin-form-table-term_meta.add-new-term .title-colon{margin-left:.2em}.power-plugin-form-table-term_meta.add-new-term .power-plugin-section .form-table>tbody>tr>td,.power-plugin-form-table-term_meta.add-new-term .power-plugin-section .form-table>tbody>tr>th{display:inline-block;width:100%;padding:0;float:right;clear:right}.power-plugin-form-table-term_meta.add-new-term .power-plugin-field{width:auto}.power-plugin-form-table-term_meta.add-new-term .power-plugin-field{max-width:100%}.power-plugin-form-table-term_meta.add-new-term .sortable .power-plugin-field{width:auto}.power-plugin-form-table-term_meta.add-new-term .power-plugin-section .form-table>tbody>tr>th{font-size:13px;line-height:1.5;margin:0;font-weight:700}.power-plugin-form-table-term_meta .power-plugin-section-title h3{border:none;font-weight:700;font-size:1.12em;margin:0;padding:0;font-family:'Open Sans',sans-serif;cursor:inherit;-webkit-user-select:inherit;-moz-user-select:inherit;user-select:inherit}.power-plugin-form-table-term_meta .power-plugin-collapsible-title h3{margin:0}.power-plugin-form-table-term_meta h4{margin:1em 0;font-size:1.04em}.power-plugin-form-table-term_meta .power-plugin-section-tab h4{margin:0}
CSSRULES;
    }
}
