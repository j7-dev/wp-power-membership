<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___CollapsibleSectionTitle extends PowerPlugin_AdminPageFramework_Form_View___SectionTitle {
    public $aArguments = array( 'title' => null, 'tag' => null, 'section_index' => null, 'collapsible' => array(), 'container_type' => 'section', 'sectionset' => array(), );
    public $aFieldsets = array();
    public $aSavedData = array();
    public $aFieldErrors = array();
    public $aFieldTypeDefinitions = array();
    public $oMsg;
    public $aCallbacks = array( 'fieldset_output', 'is_fieldset_visible' => null, );
    public function get()
    {
        if (empty($this->aArguments[ 'collapsible' ])) {
            return '';
        }
        $this->___enqueueScript();
        return $this->___getCollapsibleSectionTitleBlock($this->aArguments[ 'collapsible' ], $this->aArguments[ 'container_type' ], $this->aArguments[ 'section_index' ]);
    }
    private function ___enqueueScript()
    {
        $_oForm = $this->callBack($this->aCallbacks[ 'get_form_object' ], array());
        $_oForm->addResource('src_scripts', array( 'handle_id' => 'power-plugin-script-form-collapsible-sections', ));
    }
    private function ___getCollapsibleSectionTitleBlock(array $aCollapsible, $sContainer='sections', $iSectionIndex=null)
    {
        if ($sContainer !== $aCollapsible[ 'container' ]) {
            return '';
        }
        $_sSectionTitle = $this->_getSectionTitle($this->aArguments[ 'title' ], $this->aArguments[ 'tag' ], $this->aFieldsets, $iSectionIndex, $this->aFieldTypeDefinitions, $aCollapsible);
        $_aSectionset = $this->aArguments[ 'sectionset' ];
        $_sSectionTitleTagID = str_replace('|', '_', $_aSectionset[ '_section_path' ]) . '_' . $iSectionIndex;
        return "<div " . $this->getAttributes(array( 'id' => $_sSectionTitleTagID, 'class' => $this->getClassAttribute('power-plugin-section-title', $this->getAOrB('box' === $aCollapsible[ 'type' ], 'accordion-section-title', ''), 'power-plugin-collapsible-title', $this->getAOrB('sections' === $aCollapsible[ 'container' ], 'power-plugin-collapsible-sections-title', 'power-plugin-collapsible-section-title'), $this->getAOrB($aCollapsible[ 'is_collapsed' ], 'collapsed', ''), 'power-plugin-collapsible-type-' . $aCollapsible[ 'type' ]), ) + $this->getDataAttributeArray($aCollapsible)) . ">" . $_sSectionTitle . "</div>";
    }
}
