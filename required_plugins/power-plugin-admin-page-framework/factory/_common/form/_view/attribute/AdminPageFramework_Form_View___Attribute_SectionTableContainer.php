<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_Form_View___Attribute_SectionTableContainer extends PowerPlugin_AdminPageFramework_Form_View___Attribute_Base {
    protected function _getAttributes()
    {
        $_aSectionAttributes = $this->uniteArrays($this->dropElementsByType($this->aArguments[ 'attributes' ]), array( 'id' => $this->aArguments[ '_tag_id' ], 'class' => $this->getClassAttribute('power-plugin-section', $this->getAOrB($this->aArguments[ 'section_tab_slug' ], 'power-plugin-tab-content', null), $this->getAOrB($this->aArguments[ '_is_collapsible' ], 'is_subsection_collapsible', null)), ));
        $_aSectionAttributes[ 'class' ] = $this->getClassAttribute($_aSectionAttributes[ 'class' ], $this->dropElementsByType($this->aArguments[ 'class' ]));
        $_aSectionAttributes[ 'style' ] = $this->getStyleAttribute($_aSectionAttributes[ 'style' ], $this->getAOrB($this->aArguments[ 'hidden' ], 'display:none', null));
        return $_aSectionAttributes;
    }
}
