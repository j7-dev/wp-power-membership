<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___Attribute_SectionsTablesContainer extends PowerPlugin_AdminPageFramework_Form_View___Attribute_Base {
    public $aSectionset = array();
    public $sSectionsID = '';
    public $sSectionTabSlug = '';
    public $aCollapsible = array();
    public $iSubSectionCount = 0;
    public function __construct()
    {
        $_aParameters = func_get_args() + array( $this->aSectionset, $this->sSectionsID, $this->sSectionTabSlug, $this->aCollapsible, $this->iSubSectionCount, );
        $this->aSectionset = $_aParameters[ 0 ];
        $this->sSectionsID = $_aParameters[ 1 ];
        $this->sSectionTabSlug = $_aParameters[ 2 ];
        $this->aCollapsible = $_aParameters[ 3 ];
        $this->iSubSectionCount = $_aParameters[ 4 ];
    }
    protected function _getAttributes()
    {
        return array( 'id' => $this->sSectionsID, 'class' => $this->getClassAttribute('power-plugin-sections', $this->getAOrB(! $this->sSectionTabSlug || '_default' === $this->sSectionTabSlug, null, 'power-plugin-section-tabs-contents'), $this->getAOrB(empty($this->aCollapsible), null, 'power-plugin-collapsible-sections-content' . ' ' . 'power-plugin-collapsible-content' . ' ' . 'accordion-section-content'), $this->getAOrB(empty($this->aSectionset[ 'repeatable' ]), null, 'repeatable-section'), $this->getAOrB(empty($this->aSectionset[ 'sortable' ]), null, 'sortable-section')), 'data-seciton_id' => $this->aSectionset[ 'section_id' ], 'data-section_address' => $this->aSectionset[ 'section_id' ], 'data-section_address_model' => $this->aSectionset[ 'section_id' ] . '|' . '___i___', ) + $this->_getDynamicElementArguments($this->aSectionset);
    }
    private function _getDynamicElementArguments($aSectionset)
    {
        if (empty($aSectionset[ 'repeatable' ]) && empty($aSectionset[ 'sortable' ])) {
            return array();
        }
        $aSectionset[ '_index' ] = null;
        $_oSectionNameGenerator = new PowerPlugin_AdminPageFramework_Form_View___Generate_SectionName($aSectionset, $aSectionset[ '_caller_object' ]->aCallbacks[ 'hfSectionName' ]);
        return array( 'data-largest_index' => max(( int ) $this->iSubSectionCount - 1, 0), 'data-section_id_model' => $aSectionset[ 'section_id' ] . '__' . '___i___', 'data-flat_section_name_model' => $aSectionset[ 'section_id' ] . '|___i___', 'data-section_name_model' => $_oSectionNameGenerator->getModel(), );
    }
}
