<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View__Resource__Head extends PowerPlugin_AdminPageFramework_FrameworkUtility {
    public $oForm;
    public function __construct($oForm, $sHeadActionHook='admin_head')
    {
        $this->oForm = $oForm;
        if (in_array($this->oForm->aArguments[ 'structure_type' ], array( 'widget' ))) {
            return;
        }
        add_action($sHeadActionHook, array( $this, '_replyToInsertRequiredInternalScripts' ));
    }
    public function _replyToInsertRequiredInternalScripts()
    {
        if (! $this->oForm->isInThePage()) {
            return;
        }
        if ($this->hasBeenCalled(__METHOD__)) {
            return;
        }
        echo "<script type='text/javascript' class='power-plugin-form-script-required-in-head'>" . '/* <![CDATA[ */ ' . $this->_getScripts_RequiredInHead() . ' /* ]]> */' . "</script>";
    }
    private function _getScripts_RequiredInHead()
    {
        return 'document.write( "<style class=\'power-plugin-js-embedded-internal-style\'>' . str_replace('\\n', '', esc_js($this->_getInternalCSS())) . '</style>" );';
    }
    private function _getInternalCSS()
    {
        $_oLoadingCSS = new PowerPlugin_AdminPageFramework_Form_View___CSS_Loading;
        $_oLoadingCSS->add($this->_getScriptElementConcealerCSSRules());
        return $_oLoadingCSS->get();
    }
    private function _getScriptElementConcealerCSSRules()
    {
        return <<<CSSRULES
.power-plugin-form-js-on{visibility:hidden}.widget .power-plugin-form-js-on{visibility:visible}
CSSRULES;
    }
}
