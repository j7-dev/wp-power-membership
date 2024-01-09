<?php
/**
 * Admin Page Framework
 *
 * http://power-plugin.michaeluno.jp/
 * Copyright (c) 2013-2022, Michael Uno; Licensed MIT
 *
 */

/**
 * Provides methods for text messages.
 *
 * @since    2.0.0
 * @since    2.1.6       Multiple instances of this class are disallowed.
 * @since    3.2.0       Multiple instances of this class are allowed but the instantiation is restricted to per text domain basis.
 * @package  PowerPlugin_AdminPageFramework/Common/Factory/Property
 * @internal
 * @remark   When adding a new framework translation item,
 * Step 1: add a key and the default value to the `$aDefaults` property array.
 * Step 2: add a dummy function call in the `___doDummy()` method so that parser programs can catch it.
 */
class PowerPlugin_AdminPageFramework_Message {

    /**
     * Stores the framework's messages.
     *
     * @since  2.0.0
     * @since  3.1.3       No item is defined by default but done on the fly per request. The below array structure is kept for backward compatibility.
     * @remark The user may modify this property directly.
     */
    public $aMessages = array();

    /**
     * Stores default translated items.
     *
     * @remark      These items should be accessed only when its label needs to be displayed.
     * So the translation method `__()` only gets executed for one file.
     *
     * Consider the difference between the two.
     * <code>
     * $_aTranslations = array(
     *      'foo'  => __( 'Foo', 'power-plugin' ),
     *      'bar'  => __( 'Bar', 'power-plugin' ),
     *       ... more 100 items
     * )
     * return isset( $_aTranslations[ $sKey ] ) ? $_aTranslations[ $sKey ] : '';
     * </code>
     *
     * <code>
     * $_aTranslations = array(
     *      'foo'  => 'Foo',
     *      'bar'  => 'Bar',
     *       ... more 100 items
     * )
     * return isset( $_aTranslations[ $sKey ] )
     *      ? __( $_aTranslations[ $sKey ], $sUserSetTextdomain )
     *      : '';
     * </code>
     * @since       3.5.3
     */
    public $aDefaults = array(

        // PowerPlugin_AdminPageFramework
        'option_updated'                        => 'The options have been updated.',
        'option_cleared'                        => 'The options have been cleared.',
        'export'                                => 'Export',
        'export_options'                        => 'Export Options',
        'import'                                => 'Import',
        'import_options'                        => 'Import Options',
        'submit'                                => 'Submit',
        'import_error'                          => 'An error occurred while uploading the import file.',
        'uploaded_file_type_not_supported'      => 'The uploaded file type is not supported: %1$s',
        'could_not_load_importing_data'         => 'Could not load the importing data.',
        'imported_data'                         => 'The uploaded file has been imported.',
        'not_imported_data'                     => 'No data could be imported.',
        'upload_image'                          => 'Upload Image',
        'use_this_image'                        => 'Use This Image',
        'insert_from_url'                       => 'Insert from URL',
        'reset_options'                         => 'Are you sure you want to reset the options?',
        'confirm_perform_task'                  => 'Please confirm your action.',
        'specified_option_been_deleted'         => 'The specified options have been deleted.',
        'nonce_verification_failed'             => 'A problem occurred while processing the form data. Please try again.',
        'check_max_input_vars'                  => 'Not all form fields could not be sent. '
            . 'Please check your server settings of PHP <code>max_input_vars</code> and consult the server administrator to increase the value. '
            . '<code>max input vars</code>: %1$s. <code>$_POST</code> count: %2$s',  // 3.5.11+ // sanitization unnecessary as it is just a literal string
        'send_email'                            => 'Is it okay to send the email?',     // 3.3.0+
        'email_sent'                            => 'The email has been sent.',  // 3.3.0+, 3.3.5+ deprecated, 3.8.32 Re-added
        'email_scheduled'                       => 'The email has been scheduled.', // 3.3.5+, 3.8.32 deprecated
        'email_could_not_send'                  => 'There was a problem sending the email',     // 3.3.0+

        // PowerPlugin_AdminPageFramework_PostType
        'title'                                 => 'Title',
        'author'                                => 'Author',
        'categories'                            => 'Categories',
        'tags'                                  => 'Tags',
        'comments'                              => 'Comments',
        'date'                                  => 'Date',
        'show_all'                              => 'Show All',
        'show_all_authors'                      => 'Show all Authors', // 3.5.10+

        // PowerPlugin_AdminPageFramework_Link_Base
        'powered_by'                            => 'Thank you for creating with',
        'and'                                   => 'and',

        // PowerPlugin_AdminPageFramework_Link_admin_page
        'settings'                              => 'Settings',

        // PowerPlugin_AdminPageFramework_Link_post_type
        'manage'                                => 'Manage',

        // PowerPlugin_AdminPageFramework_FieldType_{...}
        'select_image'                          => 'Select Image',
        'upload_file'                           => 'Upload File',
        'use_this_file'                         => 'Use This File',
        'select_file'                           => 'Select File',
        'remove_value'                          => 'Remove Value',  // 3.2.0+
        'select_all'                            => 'Select All',    // 3.3.0+
        'select_none'                           => 'Select None',   // 3.3.0+
        'no_term_found'                         => 'No term found.', // 3.3.2+

        // PowerPlugin_AdminPageFramework_Form_View___Script_{...}
        'select'                                => 'Select', // 3.4.2+
        'insert'                                => 'Insert',  // 3.4.2+
        'use_this'                              => 'Use This', // 3.4.2+
        'return_to_library'                     => 'Return to Library', // 3.4.2+

        // PowerPlugin_AdminPageFramework_PageLoadInfo_Base
        'queries_in_seconds'                    => '%1$s queries in %2$s seconds.',
        'out_of_x_memory_used'                  => '%1$s out of %2$s (%3$s) memory used.',
        'peak_memory_usage'                     => 'Peak memory usage %1$s.',
        'initial_memory_usage'                  => 'Initial memory usage  %1$s.',

        // Repeatable sections & fields
        'repeatable_section_is_disabled'        => 'The ability to repeat sections is disabled.', // 3.8.13+
        'repeatable_field_is_disabled'          => 'The ability to repeat fields is disabled.',   // 3.8.13+
        'warning_caption'                       => 'Warning',   // 3.8.13+

        // PowerPlugin_AdminPageFramework_FormField
        'allowed_maximum_number_of_fields'      => 'The allowed maximum number of fields is {0}.',
        'allowed_minimum_number_of_fields'      => 'The allowed minimum number of fields is {0}.',
        'add'                                   => 'Add',
        'remove'                                => 'Remove',

        // PowerPlugin_AdminPageFramework_FormPart_Table
        'allowed_maximum_number_of_sections'    => 'The allowed maximum number of sections is {0}',
        'allowed_minimum_number_of_sections'    => 'The allowed minimum number of sections is {0}',
        'add_section'                           => 'Add Section',
        'remove_section'                        => 'Remove Section',
        'toggle_all'                            => 'Toggle All',
        'toggle_all_collapsible_sections'       => 'Toggle all collapsible sections',

        // PowerPlugin_AdminPageFramework_FieldType_reset 3.3.0+
        'reset'                                 => 'Reset',

        // PowerPlugin_AdminPageFramework_FieldType_system 3.5.3+
        'yes'                                   => 'Yes',
        'no'                                    => 'No',
        'on'                                    => 'On',
        'off'                                   => 'Off',
        'enabled'                               => 'Enabled',
        'disabled'                              => 'Disabled',
        'supported'                             => 'Supported',
        'not_supported'                         => 'Not Supported',
        'functional'                            => 'Functional',
        'not_functional'                        => 'Not Functional',
        'too_long'                              => 'Too Long',
        'acceptable'                            => 'Acceptable',
        'no_log_found'                          => 'No log found.',

        // 3.7.0+ - accessed from `PowerPlugin_AdminPageFramework_Form`
        'method_called_too_early'               => 'The method is called too early.',

        // 3.7.0+  - accessed from `PowerPlugin_AdminPageFramework_Form_View___DebugInfo`
        'debug_info'                            => 'Debug Info',
        // 3.8.5+
        'debug'                                 => 'Debug',
        // 'field_arguments'                       => 'Field Arguments', // @deprecated 3.8.22
        'debug_info_will_be_disabled'           => 'This information will be disabled when <code>WP_DEBUG</code> is set to <code>false</code> in <code>wp-config.php</code>.',

        // 'section_arguments'                     => 'Section Arguments', // 3.8.8+   // @deprecated 3.8.22

        'click_to_expand'                       => 'Click here to expand to view the contents.',
        'click_to_collapse'                     => 'Click here to collapse the contents.',

        // 3.7.0+ - displayed while the page laods
        'loading'                               => 'Loading...',
        'please_enable_javascript'              => 'Please enable JavaScript for better user experience.',

        'submit_confirmation_label'             => 'Submit the form.',
        'submit_confirmation_error'             => 'Please check this box if you want to proceed.',
        'import_no_file'                        => 'No file is selected.',

        // 3.9.0
        'please_fill_out_this_field'            => 'Please fill out this field.',

    );

    /**
     * Stores the text domain.
     * @since 3.x
     * @since 3.5.0       Declared as a default property.
     */
    protected $_sTextDomain = 'power-plugin';

    /**
     * Stores the self instance by text domain.
     * @internal
     * @since    3.2.0
     */
    static private $_aInstancesByTextDomain = array();

    /**
     * Ensures that only one instance of this class object exists. ( no multiple instances of this object )
     *
     * @since       2.1.6
     * @since       3.2.0       Changed it to create an instance per text domain basis.
     * @param       string      $sTextDomain
     * @remark      This class should be instantiated via this method.
     * @return      PowerPlugin_AdminPageFramework_Message
     */
    public static function getInstance( $sTextDomain='power-plugin' ) {

        $_oInstance = isset( self::$_aInstancesByTextDomain[ $sTextDomain ] ) && ( self::$_aInstancesByTextDomain[ $sTextDomain ] instanceof PowerPlugin_AdminPageFramework_Message )
            ? self::$_aInstancesByTextDomain[ $sTextDomain ]
            : new PowerPlugin_AdminPageFramework_Message( $sTextDomain );
        self::$_aInstancesByTextDomain[ $sTextDomain ] = $_oInstance;
        return self::$_aInstancesByTextDomain[ $sTextDomain ];

    }
        /**
         * Ensures that only one instance of this class object exists. ( no multiple instances of this object )
         * @deprecated  3.2.0
         */
        public static function instantiate( $sTextDomain='power-plugin' ) {
            return self::getInstance( $sTextDomain );
        }

    /**
     * Sets up properties.
     * @param string $sTextDomain
     */
    public function __construct( $sTextDomain='power-plugin' ) {

        $this->_sTextDomain = $sTextDomain;

        // Fill the $aMessages property with the keys extracted from the $aDefaults property
        // with the value of null.  The null is set to let it trigger the __get() method
        // so that each translation item gets processed individually.
        $this->aMessages    = array_fill_keys(
            array_keys( $this->aDefaults ),
            null
        );

    }

    /**
     * Returns the set text domain string.
     *
     * This is used from field type and input classes to display deprecated admin errors/
     *
     * @since 3.3.3
     */
    public function getTextDomain() {
        return $this->_sTextDomain;
    }

    /**
     * Sets a message for the given key.
     * @since       3.7.0
     */
    public function set( $sKey, $sValue ) {
        $this->aMessages[ $sKey ] = $sValue;
    }

    /**
     * Returns the framework system message by key.
     *
     * @remark An alias of the __() method.
     * @since  3.2.0
     * @since  3.7.0        If no key is specified, return the entire mesage array.
     * @param  string       $sKey
     * @return string|array
     */
    public function get( $sKey='' ) {
        if ( ! $sKey ) {
            return $this->_getAllMessages();
        }
        return isset( $this->aMessages[ $sKey ] )
            ? __( $this->aMessages[ $sKey ], $this->_sTextDomain )
            : __( $this->{$sKey}, $this->_sTextDomain );     // triggers __get()
    }
        /**
         * Returns the all registered messag items.
         * By default, no item is set for a performance reason; the message is retuned on the fly.
         * So all the keys must be iterated to get all the values.
         * @since       3.7.0
         * @return      array
         */
        private function _getAllMessages() {
            $_aMessages = array();
            foreach ( $this->aMessages as $_sLabel => $_sTranslation ) {
                $_aMessages[ $_sLabel ] = $this->get( $_sLabel );
            }
            return $_aMessages;
        }

    /**
     * Echoes the framework system message by key.
     * @remark An alias of the _e() method.
     * @since  3.2.0
     */
    public function output( $sKey ) {
        echo $this->get( $sKey );
    }

        /**
         * Returns the framework system message by key.
         * @since       2.x
         * @deprecated  3.2.0
         */
        public function __( $sKey ) {
            return $this->get( $sKey );
        }
        /**
         * Echoes the framework system message by key.
         * @since       2.x
         * @deprecated  3.2.0
         */
        public function _e( $sKey ) {
            $this->output( $sKey );
        }

    /**
     * Responds to a request to an undefined property.
     *
     * @since  3.1.3
     * @return string
     */
    public function __get( $sPropertyName ) {
        return isset( $this->aDefaults[ $sPropertyName ] ) ? $this->aDefaults[ $sPropertyName ] : $sPropertyName;
    }


    /**
     * A dummy method just lists translation items to be parsed by translation programs such as POEdit.
     *
     * @since 3.5.3
     * @since 3.8.19 Changed the name to avoid false-positives of PHP 7.2 incompatibility by third party tools.
     */
    private function ___doDummy() {

        __( 'The options have been updated.', 'power-plugin' );
        __( 'The options have been cleared.', 'power-plugin' );
        __( 'Export', 'power-plugin' );
        __( 'Export Options', 'power-plugin' );
        __( 'Import', 'power-plugin' );
        __( 'Import Options', 'power-plugin' );
        __( 'Submit', 'power-plugin' );
        __( 'An error occurred while uploading the import file.', 'power-plugin' );
        /* translators: 1: Uploaded file type */
        __( 'The uploaded file type is not supported: %1$s', 'power-plugin' );
        __( 'Could not load the importing data.', 'power-plugin' );
        __( 'The uploaded file has been imported.', 'power-plugin' );
        __( 'No data could be imported.', 'power-plugin' );
        __( 'Upload Image', 'power-plugin' );
        __( 'Use This Image', 'power-plugin' );
        __( 'Insert from URL', 'power-plugin' );
        __( 'Are you sure you want to reset the options?', 'power-plugin' );
        __( 'Please confirm your action.', 'power-plugin' );
        __( 'The specified options have been deleted.', 'power-plugin' );
        __( 'A problem occurred while processing the form data. Please try again.', 'power-plugin' );
        /* translators: 1: The value of max_input_vars set by PHP 2: Actual $_POST element count */
        __( 'Not all form fields could not be sent. Please check your server settings of PHP <code>max_input_vars</code> and consult the server administrator to increase the value. <code>max input vars</code>: %1$s. <code>$_POST</code> count: %2$s', 'power-plugin' ); // sanitization unnecessary as a literal string
        __( 'Is it okay to send the email?', 'power-plugin' );
        __( 'The email has been sent.', 'power-plugin' );
        __( 'The email has been scheduled.', 'power-plugin' );
        __( 'There was a problem sending the email', 'power-plugin' );
        __( 'Title', 'power-plugin' );
        __( 'Author', 'power-plugin' );
        __( 'Categories', 'power-plugin' );
        __( 'Tags', 'power-plugin' );
        __( 'Comments', 'power-plugin' );
        __( 'Date', 'power-plugin' );
        __( 'Show All', 'power-plugin' );
        __( 'Show All Authors', 'power-plugin' );
        __( 'Thank you for creating with', 'power-plugin' );
        __( 'and', 'power-plugin' );
        __( 'Settings', 'power-plugin' );
        __( 'Manage', 'power-plugin' );
        __( 'Select Image', 'power-plugin' );
        __( 'Upload File', 'power-plugin' );
        __( 'Use This File', 'power-plugin' );
        __( 'Select File', 'power-plugin' );
        __( 'Remove Value', 'power-plugin' );
        __( 'Select All', 'power-plugin' );
        __( 'Select None', 'power-plugin' );
        __( 'No term found.', 'power-plugin' );
        __( 'Select', 'power-plugin' );
        __( 'Insert', 'power-plugin' );
        __( 'Use This', 'power-plugin' );
        __( 'Return to Library', 'power-plugin' );
        /* translators: 1: Number of performed database queries 2: Elapsed seconds for page load */
        __( '%1$s queries in %2$s seconds.', 'power-plugin' );
        /* translators: 1: Used memory amount 2: Max memory cap set by WordPress (WP_MEMORY_LIMIT) 3: Percentage of the memory usage */
        __( '%1$s out of %2$s MB (%3$s) memory used.', 'power-plugin' );
        /* translators: 1: Peak memory usage amount */
        __( 'Peak memory usage %1$s MB.', 'power-plugin' );
        /* translators: 1: Initial memory usage amount */
        __( 'Initial memory usage  %1$s MB.', 'power-plugin' );
        __( 'The allowed maximum number of fields is {0}.', 'power-plugin' );
        __( 'The allowed minimum number of fields is {0}.', 'power-plugin' );
        __( 'Add', 'power-plugin' );
        __( 'Remove', 'power-plugin' );
        __( 'The allowed maximum number of sections is {0}', 'power-plugin' );
        __( 'The allowed minimum number of sections is {0}', 'power-plugin' );
        __( 'Add Section', 'power-plugin' );
        __( 'Remove Section', 'power-plugin' );
        __( 'Toggle All', 'power-plugin' );
        __( 'Toggle all collapsible sections', 'power-plugin' );
        __( 'Reset', 'power-plugin' );
        __( 'Yes', 'power-plugin' );
        __( 'No', 'power-plugin' );
        __( 'On', 'power-plugin' );
        __( 'Off', 'power-plugin' );
        __( 'Enabled', 'power-plugin' );
        __( 'Disabled', 'power-plugin' );
        __( 'Supported', 'power-plugin' );
        __( 'Not Supported', 'power-plugin' );
        __( 'Functional', 'power-plugin' );
        __( 'Not Functional', 'power-plugin' );
        __( 'Too Long', 'power-plugin' );
        __( 'Acceptable', 'power-plugin' );
        __( 'No log found.', 'power-plugin' );

        /* translators: 1: Method name */
        __( 'The method is called too early: %1$s', 'power-plugin' );
        __( 'Debug Info', 'power-plugin' );

        __( 'Click here to expand to view the contents.', 'power-plugin' );
        __( 'Click here to collapse the contents.', 'power-plugin' );

        __( 'Loading...', 'power-plugin' );
        __( 'Please enable JavaScript for better user experience.', 'power-plugin' );

        __( 'Debug', 'power-plugin' );
        // __( 'Field Arguments', 'power-plugin' ); @deprecated 3.8.22
        __( 'This information will be disabled when <code>WP_DEBUG</code> is set to <code>false</code> in <code>wp-config.php</code>.', 'power-plugin' );

        // __( 'Section Arguments', 'power-plugin' ); // 3.8.8+ @deprecated 3.8.22

        __( 'The ability to repeat sections is disabled.', 'power-plugin' ); // 3.8.13+
        __( 'The ability to repeat fields is disabled.', 'power-plugin' ); // 3.8.13+
        __( 'Warning.', 'power-plugin' ); // 3.8.13+

        __( 'Submit the form.', 'power-plugin' ); // 3.8.24
        __( 'Please check this box if you want to proceed.', 'power-plugin' ); // 3.8.24
        __( 'No file is selected.', 'power-plugin' ); // 3.8.24

        __( 'Please fill out this field.', 'power-plugin' ); // 3.9.0

    }

}