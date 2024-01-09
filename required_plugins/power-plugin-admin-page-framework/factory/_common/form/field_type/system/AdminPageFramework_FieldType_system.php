<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_FieldType_system extends PowerPlugin_AdminPageFramework_FieldType {
    public $aFieldTypeSlugs = array( 'system', );
    protected $aDefaultKeys = array( 'data' => array(), 'print_type' => 1, 'attributes' => array( 'rows' => 60, 'autofocus' => null, 'disabled' => null, 'formNew' => null, 'maxlength' => null, 'placeholder' => null, 'readonly' => 'readonly', 'required' => null, 'wrap' => null, 'style' => null, ), );
    protected function getField($aField)
    {
        $_aInputAttributes = $aField[ 'attributes' ];
        $_aInputAttributes[ 'class' ] .= ' system';
        unset($_aInputAttributes[ 'value' ]);
        return $aField[ 'before_label' ] . "<div class='power-plugin-input-label-container'>" . "<label for='{$aField[ 'input_id' ]}'>" . $aField[ 'before_input' ] . ($aField[ 'label' ] && ! $aField[ 'repeatable' ] ? "<span " . $this->getLabelContainerAttributes($aField, 'power-plugin-input-label-string') . ">" . $aField[ 'label' ] . "</span>" : "") . "<textarea " . $this->getAttributes($_aInputAttributes) . " >" . esc_textarea($this->___getSystemInformation($aField[ 'value' ], $aField[ 'data' ], $aField[ 'print_type' ])) . "</textarea>" . $aField[ 'after_input' ] . "</label>" . "</div>" . $aField[ 'after_label' ];
    }
    private function ___getSystemInformation($asValue=null, $asCustomData=null, $iPrintType=1)
    {
        if (isset($asValue)) {
            return $asValue;
        }
        $_aOutput = array();
        foreach ($this->___getFormattedSystemInformation($asCustomData) as $_sSection => $_aInfo) {
            $_aOutput[] = $this->_____getSystemInfoBySection($_sSection, $_aInfo, $iPrintType);
        }
        return implode(PHP_EOL, $_aOutput);
    }
    private function ___getFormattedSystemInformation($asCustomData)
    {
        $_aData = $this->getAsArray($asCustomData);
        $_aData = $_aData + array( 'WordPress' => $this->___getSiteInfoWithCache(! isset($_aData[ 'WordPress' ])), 'PHP' => $this->_getPHPInfo(! isset($_aData[ 'PHP' ])), 'PHP Error Log' => $this->___getErrorLogByType('php', ! isset($_aData[ 'PHP Error Log' ])), 'MySQL' => isset($_aData[ 'MySQL' ]) ? null : $this->getMySQLInfo(), 'MySQL Error Log' => $this->___getErrorLogByType('mysql', ! isset($_aData[ 'MySQL Error Log' ])), 'Server' => $this->___getWebServerInfo(! isset($_aData[ 'Server' ])), 'Browser' => $this->___getClientInfo(! isset($_aData[ 'Browser' ])), 'Admin Page Framework' => isset($_aData[ 'Admin Page Framework' ]) ? null : PowerPlugin_AdminPageFramework_Registry::getInfo(), );
        return array_filter($_aData);
    }
    private function _____getSystemInfoBySection($sSectionName, $aData, $iPrintType)
    {
        switch ($iPrintType) { default: case 1: return $this->getReadableArrayContents($sSectionName, $aData, 32) . PHP_EOL; case 2: return "[{$sSectionName}]" . PHP_EOL . print_r($aData, true) . PHP_EOL; }
    }
    private function ___getClientInfo($bGenerateInfo=true)
    {
        if (! $bGenerateInfo) {
            return '';
        }
        $_aBrowser = @ini_get('browscap') ? get_browser($_SERVER[ 'HTTP_USER_AGENT' ], true) : array();
        unset($_aBrowser[ 'browser_name_regex' ]);
        return empty($_aBrowser) ? __('No browser information found.', 'power-plugin') : $_aBrowser;
    }
    private function ___getErrorLogByType($sType, $bGenerateInfo=true)
    {
        if (! $bGenerateInfo) {
            return '';
        }
        switch ($sType) { default: case 'php': $_sLog = $this->getPHPErrorLog(200); break; case 'mysql': $_sLog = $this->getMySQLErrorLog(200); break; }
        return empty($_sLog) ? $this->oMsg->get('no_log_found') : $_sLog;
    }
    private static $_aSiteInfo;
    private function ___getSiteInfoWithCache($bGenerateInfo=true)
    {
        if (! $bGenerateInfo || isset(self::$_aSiteInfo)) {
            return self::$_aSiteInfo;
        }
        self::$_aSiteInfo = self::___getSiteInfo();
        return self::$_aSiteInfo;
    }
    private function ___getSiteInfo()
    {
        global $wpdb;
        return array( __('Version', 'power-plugin') => $GLOBALS[ 'wp_version' ], __('Language', 'power-plugin') => $this->getSiteLanguage(), __('Memory Limit', 'power-plugin') => $this->getReadableBytes($this->getNumberOfReadableSize(WP_MEMORY_LIMIT)), __('Multi-site', 'power-plugin') => $this->getAOrB(is_multisite(), $this->oMsg->get('yes'), $this->oMsg->get('no')), __('Permalink Structure', 'power-plugin') => get_option('permalink_structure'), __('Active Theme', 'power-plugin') => $this->___getActiveThemeName(), __('Registered Post Statuses', 'power-plugin') => implode(', ', get_post_stati()), 'WP_DEBUG' => $this->getAOrB($this->isDebugMode(), $this->oMsg->get('enabled'), $this->oMsg->get('disabled')), 'WP_DEBUG_LOG' => $this->getAOrB($this->isDebugLogEnabled(), $this->oMsg->get('enabled'), $this->oMsg->get('disabled')), 'WP_DEBUG_DISPLAY' => $this->getAOrB($this->isDebugDisplayEnabled(), $this->oMsg->get('enabled'), $this->oMsg->get('disabled')), __('Table Prefix', 'power-plugin') => $wpdb->prefix, __('Table Prefix Length', 'power-plugin') => strlen($wpdb->prefix), __('Table Prefix Status', 'power-plugin') => $this->getAOrB(strlen($wpdb->prefix) > 16, $this->oMsg->get('too_long'), $this->oMsg->get('acceptable')), 'wp_remote_post()' => $this->___getWPRemotePostStatus(), 'wp_remote_get()' => $this->___getWPRemoteGetStatus(), __('Multibite String Extension', 'power-plugin') => $this->getAOrB(function_exists('mb_detect_encoding'), $this->oMsg->get('enabled'), $this->oMsg->get('disabled')), __('WP_CONTENT_DIR Writeable', 'power-plugin') => $this->getAOrB(is_writable(WP_CONTENT_DIR), $this->oMsg->get('yes'), $this->oMsg->get('no')), __('Active Plugins', 'power-plugin') => PHP_EOL . $this->___getActivePlugins(), __('Network Active Plugins', 'power-plugin') => PHP_EOL . $this->___getNetworkActivePlugins(), __('Constants', 'power-plugin') => $this->___getDefinedConstants('user'), );
    }
    private function ___getDefinedConstants($asCategories=null, $asRemovingCategories=null)
    {
        $_asConstants = $this->getDefinedConstants($asCategories, $asRemovingCategories);
        if (! is_array($_asConstants)) {
            return $_asConstants;
        }
        if (isset($_asConstants[ 'user' ])) {
            $_asConstants[ 'user' ] = array( 'AUTH_KEY' => '__masked__', 'SECURE_AUTH_KEY' => '__masked__', 'LOGGED_IN_KEY' => '__masked__', 'NONCE_KEY' => '__masked__', 'AUTH_SALT' => '__masked__', 'SECURE_AUTH_SALT' => '__masked__', 'LOGGED_IN_SALT' => '__masked__', 'NONCE_SALT' => '__masked__', 'COOKIEHASH' => '__masked__', 'USER_COOKIE' => '__masked__', 'PASS_COOKIE' => '__masked__', 'AUTH_COOKIE' => '__masked__', 'SECURE_AUTH_COOKIE' => '__masked__', 'LOGGED_IN_COOKIE' => '__masked__', 'TEST_COOKIE' => '__masked__', 'DB_USER' => '__masked__', 'DB_PASSWORD' => '__masked__', 'DB_HOST' => '__masked__', ) + $_asConstants[ 'user' ];
        }
        return $_asConstants;
    }
    private function ___getActiveThemeName()
    {
        if (version_compare($GLOBALS[ 'wp_version' ], '3.4', '<')) {
            $_aThemeData = get_theme_data(get_stylesheet_directory() . '/style.css');
            return $_aThemeData[ 'Name' ] . ' ' . $_aThemeData[ 'Version' ];
        }
        $_oThemeData = wp_get_theme();
        return $_oThemeData->Name . ' ' . $_oThemeData->Version;
    }
    private function ___getActivePlugins()
    {
        $_aPluginList = array();
        $_aActivePlugins = get_option('active_plugins', array());
        foreach (get_plugins() as $_sPluginPath => $_aPlugin) {
            if (! in_array($_sPluginPath, $_aActivePlugins)) {
                continue;
            }
            $_aPluginList[] = '    ' . $_aPlugin[ 'Name' ] . ': ' . $_aPlugin[ 'Version' ];
        }
        return implode(PHP_EOL, $_aPluginList);
    }
    private function ___getNetworkActivePlugins()
    {
        if (! is_multisite()) {
            return '';
        }
        $_aPluginList = array();
        $_aActivePlugins = get_site_option('active_sitewide_plugins', array());
        foreach (wp_get_active_network_plugins() as $_sPluginPath) {
            if (! array_key_exists(plugin_basename($_sPluginPath), $_aActivePlugins)) {
                continue;
            }
            $_aPlugin = get_plugin_data($_sPluginPath);
            $_aPluginList[] = '    ' . $_aPlugin[ 'Name' ] . ' :' . $_aPlugin[ 'Version' ];
        }
        return implode(PHP_EOL, $_aPluginList);
    }
    private function ___getWPRemotePostStatus()
    {
        $_vResponse = $this->getTransient('apf_rp_check');
        $_vResponse = false === $_vResponse ? wp_remote_post(add_query_arg($this->getHTTPQueryGET(array(), array()), admin_url($GLOBALS[ 'pagenow' ])), array( 'sslverify' => false, 'timeout' => 60, 'body' => array( 'apf_remote_request_test' => '_testing', 'cmd' => '_notify-validate' ), )) : $_vResponse;
        $this->setTransient('apf_rp_check', $_vResponse, 60);
        return $this->getAOrB($this->___isHttpRequestError($_vResponse), $this->oMsg->get('not_functional'), $this->oMsg->get('functional'));
    }
    private function ___getWPRemoteGetStatus()
    {
        $_aoResponse = $this->getTransient('apf_rg_check');
        $_aoResponse = false === $_aoResponse ? wp_remote_get(add_query_arg($this->getHTTPQueryGET(array(), array()) + array( 'apf_remote_request_test' => '_testing' ), admin_url($GLOBALS[ 'pagenow' ])), array( 'sslverify' => false, 'timeout' => 60, )) : $_aoResponse;
        $this->setTransient('apf_rg_check', $_aoResponse, 60);
        return $this->getAOrB($this->___isHttpRequestError($_aoResponse), $this->oMsg->get('not_functional'), $this->oMsg->get('functional'));
    }
    private function ___isHttpRequestError($aoResponse)
    {
        if (is_wp_error($aoResponse)) {
            return true;
        }
        if ($aoResponse[ 'response'][ 'code' ] < 200) {
            return true;
        }
        if ($aoResponse[ 'response' ][ 'code' ] >= 300) {
            return true;
        }
        return false;
    }
    private static $_aPHPInfo;
    private function _getPHPInfo($bGenerateInfo=true)
    {
        if (! $bGenerateInfo || isset(self::$_aPHPInfo)) {
            return self::$_aPHPInfo;
        }
        $_oErrorReporting = new PowerPlugin_AdminPageFramework_ErrorReporting;
        self::$_aPHPInfo = array( __('Version', 'power-plugin') => phpversion(), __('Safe Mode', 'power-plugin') => $this->getAOrB(@ini_get('safe_mode'), $this->oMsg->get('yes'), $this->oMsg->get('no')), __('Memory Limit', 'power-plugin') => @ini_get('memory_limit'), __('Upload Max Size', 'power-plugin') => @ini_get('upload_max_filesize'), __('Post Max Size', 'power-plugin') => @ini_get('post_max_size'), __('Upload Max File Size', 'power-plugin') => @ini_get('upload_max_filesize'), __('Max Execution Time', 'power-plugin') => @ini_get('max_execution_time'), __('Max Input Vars', 'power-plugin') => @ini_get('max_input_vars'), __('Argument Separator', 'power-plugin') => @ini_get('arg_separator.output'), __('Allow URL File Open', 'power-plugin') => $this->getAOrB(@ini_get('allow_url_fopen'), $this->oMsg->get('yes'), $this->oMsg->get('no')), __('Display Errors', 'power-plugin') => $this->getAOrB(@ini_get('display_errors'), $this->oMsg->get('on'), $this->oMsg->get('off')), __('Log Errors', 'power-plugin') => $this->getAOrB(@ini_get('log_errors'), $this->oMsg->get('on'), $this->oMsg->get('off')), __('Error log location', 'power-plugin') => @ini_get('error_log'), __('Error Reporting Level', 'power-plugin') => $_oErrorReporting->getErrorLevel(), __('FSOCKOPEN', 'power-plugin') => $this->getAOrB(function_exists('fsockopen'), $this->oMsg->get('supported'), $this->oMsg->get('not_supported')), __('cURL', 'power-plugin') => $this->getAOrB(function_exists('curl_init'), $this->oMsg->get('supported'), $this->oMsg->get('not_supported')), __('SOAP', 'power-plugin') => $this->getAOrB(class_exists('SoapClient'), $this->oMsg->get('supported'), $this->oMsg->get('not_supported')), __('SUHOSIN', 'power-plugin') => $this->getAOrB(extension_loaded('suhosin'), $this->oMsg->get('supported'), $this->oMsg->get('not_supported')), 'ini_set()' => $this->getAOrB(function_exists('ini_set'), $this->oMsg->get('supported'), $this->oMsg->get('not_supported')), ) + $this->getPHPInfo() + array( __('Constants', 'power-plugin') => $this->___getDefinedConstants(null, 'user') ) ;
        return self::$_aPHPInfo;
    }
    private function ___getWebServerInfo($bGenerateInfo=true)
    {
        return $bGenerateInfo ? array( __('Web Server', 'power-plugin') => $_SERVER['SERVER_SOFTWARE'], 'SSL' => $this->getAOrB(is_ssl(), $this->oMsg->get('yes'), $this->oMsg->get('no')), __('Session', 'power-plugin') => $this->getAOrB(isset($_SESSION), $this->oMsg->get('enabled'), $this->oMsg->get('disabled')), __('Session Name', 'power-plugin') => esc_html(@ini_get('session.name')), __('Session Cookie Path', 'power-plugin') => esc_html(@ini_get('session.cookie_path')), __('Session Save Path', 'power-plugin') => esc_html(@ini_get('session.save_path')), __('Session Use Cookies', 'power-plugin') => $this->getAOrB(@ini_get('session.use_cookies'), $this->oMsg->get('on'), $this->oMsg->get('off')), __('Session Use Only Cookies', 'power-plugin') => $this->getAOrB(@ini_get('session.use_only_cookies'), $this->oMsg->get('on'), $this->oMsg->get('off')), ) + $_SERVER : '';
    }
}
