<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_FieldType_text extends PowerPlugin_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array( 'text', 'password', 'date', 'datetime', 'datetime-local', 'email', 'month', 'search', 'tel', 'url', 'week', );
    protected $aDefaultKeys = array();
    protected function getField($aField)
    {
        $_aOutput = array();
        foreach (( array ) $aField[ 'label' ] as $_sKey => $_sLabel) {
            $_aOutput[] = $this->___getFieldOutputByLabel($_sKey, $_sLabel, $aField);
        }
        $_aOutput[] = "<div class='repeatable-field-buttons'></div>";
        return implode('', $_aOutput);
    }
    private function ___getFieldOutputByLabel($sKey, $sLabel, $aField)
    {
        $_bIsArray = is_array($aField[ 'label' ]);
        $_sClassSelector = $_bIsArray ? 'power-plugin-field-text-multiple-labels' : '';
        $_sLabel = $this->getElementByLabel($aField[ 'label' ], $sKey, $aField[ 'label' ]);
        $aField[ 'value' ] = $this->getElementByLabel($aField[ 'value' ], $sKey, $aField[ 'label' ]);
        $_aInputAttributes = $_bIsArray ? array( 'name' => $aField[ 'attributes' ][ 'name' ] . "[{$sKey}]", 'id' => $aField[ 'attributes' ][ 'id' ] . "_{$sKey}", 'value' => $aField[ 'value' ], ) + $this->getAsArray($this->getElementByLabel($aField[ 'attributes' ], $sKey, $aField[ 'label' ])) + $aField[ 'attributes' ] : $aField[ 'attributes' ];
        $_aOutput = array( $this->getElementByLabel($aField[ 'before_label' ], $sKey, $aField[ 'label' ]), "<div class='power-plugin-input-label-container {$_sClassSelector}'>", "<label for='" . $_aInputAttributes[ 'id' ] . "'>", $this->getElementByLabel($aField[ 'before_input' ], $sKey, $aField[ 'label' ]), $_sLabel ? "<span " . $this->getLabelContainerAttributes($aField, 'power-plugin-input-label-string') . ">" . $_sLabel . "</span>" : '', "<input " . $this->getAttributes($_aInputAttributes) . " />", $this->getElementByLabel($aField[ 'after_input' ], $sKey, $aField[ 'label' ]), "</label>", "</div>", $this->getElementByLabel($aField[ 'after_label' ], $sKey, $aField[ 'label' ]), );
        return implode('', $_aOutput);
    }
}
