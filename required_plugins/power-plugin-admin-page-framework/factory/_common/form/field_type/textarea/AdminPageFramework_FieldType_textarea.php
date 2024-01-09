<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_FieldType_textarea extends PowerPlugin_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array( 'textarea' );
    protected $aDefaultKeys = array( 'rich' => false, 'attributes' => array( 'autofocus' => null, 'cols' => 60, 'disabled' => null, 'formNew' => null, 'maxlength' => null, 'placeholder' => null, 'readonly' => null, 'required' => null, 'rows' => 4, 'wrap' => null, ), );
    protected function getEnqueuingScripts()
    {
        return array( array( 'handle_id' => 'power-plugin-field-type-textarea', 'src' => dirname(__FILE__) . '/js/textarea.bundle.js', 'in_footer' => true, 'dependencies' => array( 'jquery', 'power-plugin-script-form-main' ), 'translation_var' => 'PowerPlugin_AdminPageFrameworkFieldTypeTextArea', 'translation' => array( 'fieldTypeSlugs' => $this->aFieldTypeSlugs, 'label' => array(), ), ), );
    }
    protected function getField($aField)
    {
        $_aOutput = array();
        foreach (( array ) $aField[ 'label' ] as $_sKey => $_sLabel) {
            $_aOutput[] = $this->_getFieldOutputByLabel($_sKey, $_sLabel, $aField);
        }
        $_aOutput[] = "<div class='repeatable-field-buttons'></div>";
        return implode('', $_aOutput);
    }
    private function _getFieldOutputByLabel($sKey, $sLabel, $aField)
    {
        $_bIsArray = is_array($aField[ 'label' ]);
        $_sClassSelector = $_bIsArray ? 'power-plugin-field-textarea-multiple-labels' : '';
        $_sLabel = $this->getElementByLabel($aField[ 'label' ], $sKey, $aField[ 'label' ]);
        $aField[ 'value' ] = $this->getElementByLabel($aField[ 'value' ], $sKey, $aField[ 'label' ]);
        $aField[ 'rich' ] = $this->getElementByLabel($aField[ 'rich' ], $sKey, $aField[ 'label' ]);
        $aField[ 'attributes' ] = $_bIsArray ? array( 'name' => $aField[ 'attributes' ][ 'name' ] . "[{$sKey}]", 'id' => $aField[ 'attributes' ][ 'id' ] . "_{$sKey}", 'value' => $aField[ 'value' ], ) + $aField[ 'attributes' ] : $aField[ 'attributes' ];
        $_aOutput = array( $this->getElementByLabel($aField['before_label'], $sKey, $aField[ 'label' ]), "<div class='power-plugin-input-label-container {$_sClassSelector}'>", "<label for='" . $aField[ 'attributes' ][ 'id' ] . "'>", $this->getElementByLabel($aField['before_input'], $sKey, $aField[ 'label' ]), $_sLabel ? "<span " . $this->getLabelContainerAttributes($aField, 'power-plugin-input-label-string') . ">" . $_sLabel . "</span>" : '', $this->_getEditor($aField), $this->getElementByLabel($aField['after_input'], $sKey, $aField[ 'label' ]), "</label>", "</div>", $this->getElementByLabel($aField['after_label'], $sKey, $aField[ 'label' ]), );
        return implode('', $_aOutput);
    }
    private function _getEditor($aField)
    {
        unset($aField[ 'attributes' ][ 'value' ]);
        if (empty($aField[ 'rich' ]) || ! $this->isTinyMCESupported()) {
            return "<textarea " . $this->getAttributes($aField[ 'attributes' ]) . " >" . esc_textarea($aField[ 'value' ]) . "</textarea>";
        }
        ob_start();
        wp_editor($aField[ 'value' ], $aField[ 'attributes' ][ 'id' ], $this->uniteArrays(( array ) $aField[ 'rich' ], array( 'wpautop' => true, 'media_buttons' => true, 'textarea_name' => $aField[ 'attributes' ][ 'name' ], 'textarea_rows' => $aField[ 'attributes' ][ 'rows' ], 'tabindex' => '', 'tabfocus_elements' => ':prev,:next', 'editor_css' => '', 'editor_class' => $aField[ 'attributes' ][ 'class' ], 'teeny' => false, 'dfw' => false, 'tinymce' => true, 'quicktags' => true )));
        $_sContent = ob_get_contents();
        ob_end_clean();
        return $_sContent . "<input type='hidden' class='power-plugin-textarea-data-input' data-tinymce-textarea='" . esc_attr($aField[ 'attributes' ][ 'id' ]) . "' />";
    }
}
