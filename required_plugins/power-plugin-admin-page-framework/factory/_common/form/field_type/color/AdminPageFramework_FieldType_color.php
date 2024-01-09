<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_FieldType_color extends PowerPlugin_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array( 'color' );
    protected $aDefaultKeys = array( 'attributes' => array( 'size' => 10, 'maxlength' => 400, 'value' => 'transparent', ), );
    protected function setUp()
    {
        if (version_compare($GLOBALS[ 'wp_version' ], '3.5', '>=')) {
            $this->___enqueueWPColorPicker();
        } else {
            wp_enqueue_style('farbtastic');
            wp_enqueue_script('farbtastic');
        }
    }
    private function ___enqueueWPColorPicker()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        if (! is_admin()) {
            wp_enqueue_script('iris', admin_url('js/iris.min.js'), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ), false, 1);
            wp_enqueue_script('wp-color-picker', admin_url('js/color-picker.min.js'), array( 'iris' ), false, 1);
            wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array( 'clear' => __('Clear'), 'defaultString' => __('Default'), 'pick' => __('Select Color'), 'current' => __('Current Color'), ));
        }
    }
    protected function getEnqueuingScripts()
    {
        return array( array( 'handle_id' => 'power-plugin-field-type-color', 'src' => dirname(__FILE__) . '/js/color.bundle.js', 'in_footer' => true, 'dependencies' => array( 'jquery', 'power-plugin-script-form-main' ), 'translation_var' => 'PowerPlugin_AdminPageFrameworkColorFieldType', 'translation' => array( 'fieldTypeSlugs' => $this->aFieldTypeSlugs, ), ), );
    }
    protected function getField($aField)
    {
        $aField[ 'value' ] = is_null($aField[ 'value' ]) ? 'transparent' : $aField[ 'value' ];
        $aField[ 'attributes' ] = $this->___getInputAttributes($aField);
        return $aField[ 'before_label' ] . "<div class='power-plugin-input-label-container'>" . ($aField[ 'label' ] && ! $aField[ 'repeatable' ] ? "<label><span " . $this->getLabelContainerAttributes($aField, 'power-plugin-input-label-string') . ">" . $aField[ 'label' ] . "</span></label>" : "") . "<label for='{$aField[ 'input_id' ]}'>" . $aField[ 'before_input' ] . "<input " . $this->getAttributes($aField[ 'attributes' ]) . " />" . $aField[ 'after_input' ] . "</label>" . "<div class='colorpicker power-plugin-field-color-picker' id='color_" . esc_attr($aField[ 'input_id' ]) . "' data-input_id='" . esc_attr($aField[ 'input_id' ]) . "'></div>" . "<div class='repeatable-field-buttons'></div>" . "</div>" . $aField['after_label'];
    }
    private function ___getInputAttributes(array $aField)
    {
        return array( 'color' => $aField['value'], 'value' => $aField['value'], 'data-default' => isset($aField[ 'default' ]) ? $aField[ 'default' ] : 'transparent', 'type' => 'text', 'class' => trim('input_color ' . $aField['attributes']['class']), ) + $aField[ 'attributes' ];
    }
}
