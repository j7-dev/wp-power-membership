<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

abstract class PowerPlugin_AdminPageFramework_HelpPane_Base extends PowerPlugin_AdminPageFramework_FrameworkUtility {
    public $oProp;
    protected $_oScreen;
    public function __construct($oProp)
    {
        $this->oProp = $oProp;
        add_action('admin_head', array( $this, '_replyToRegisterHelpTabText' ));
    }
    protected function _setHelpTab($sID, $sTitle, $aContents, $aSideBarContents=array())
    {
        if (empty($aContents)) {
            return;
        }
        $this->_oScreen = isset($this->_oScreen) ? $this->_oScreen : get_current_screen();
        $this->_oScreen->add_help_tab(array( 'id' => $sID, 'title' => $sTitle, 'content' => implode(PHP_EOL, $aContents), ));
        if (! empty($aSideBarContents)) {
            $this->_oScreen->set_help_sidebar(implode(PHP_EOL, $aSideBarContents));
        }
    }
    protected function _formatHelpDescription($sHelpDescription)
    {
        return "<div class='contextual-help-description'>" . $sHelpDescription . "</div>";
    }
    public function _addHelpText($sHTMLContent, $sHTMLSidebarContent="")
    {
        $this->oProp->aHelpTabText[] = "<div class='contextual-help-description'>" . $sHTMLContent . "</div>";
        $this->oProp->aHelpTabTextSide[] = "<div class='contextual-help-description'>" . $sHTMLSidebarContent . "</div>";
    }
    public function _addHelpTextForFormFields($sFieldTitle, $sHelpText, $sHelpTextSidebar="")
    {
        $this->_addHelpText("<span class='contextual-help-tab-title'>" . $sFieldTitle . "</span> - " . PHP_EOL . $sHelpText, $sHelpTextSidebar);
    }
    public function _replyToRegisterHelpTabText()
    {
        if (! $this->oProp->oCaller->isInThePage()) {
            return false;
        }
        $this->_setHelpTab($this->oProp->sClassName, $this->oProp->sTitle, $this->oProp->aHelpTabText, $this->oProp->aHelpTabTextSide);
    }
}
