<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/admin-page-framework-compiler>
 * <https://en.michaeluno.jp/admin-page-framework>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 * Compiled on 2024-01-09
 * Included Components: Admin Pages, Network Admin Pages, Custom Post Types, Taxonomy Fields, Term Meta, Post Meta Boxes, Page Meta Boxes, Widgets, User Meta, Utilities
 * Custom Field Types: ACE, GitHub Buttons, Path, Toggle, NoUISlider (Range Slider), Select2, Post Type Taxonomy
 */

if (! class_exists('PowerPlugin_AdminPageFramework_Registry', false)) :
abstract class PowerPlugin_AdminPageFramework_Registry_Base {
    const VERSION = '3.9.1';
    const NAME = 'Admin Page Framework';
    const DESCRIPTION = 'Facilitates WordPress plugin and theme development.';
    const URI = 'https://en.michaeluno.jp/admin-page-framework';
    const AUTHOR = 'Michael Uno';
    const AUTHOR_URI = 'https://en.michaeluno.jp/';
    const COPYRIGHT = 'Copyright (c) 2013-2022, Michael Uno';
    const LICENSE = 'MIT <https://opensource.org/licenses/MIT>';
    const CONTRIBUTORS = '';
}
final class PowerPlugin_AdminPageFramework_Registry extends PowerPlugin_AdminPageFramework_Registry_Base {
    const TEXT_DOMAIN = 'admin-page-framework';
    const TEXT_DOMAIN_PATH = '/language';
    public static $bIsMinifiedVersion = true;
    public static $bIsDevelopmentVersion = true;
    public static $sAutoLoaderPath;
    public static $sClassMapPath;
    public static $aClassFiles = array();
    public static $sFilePath = '';
    public static $sDirPath = '';
    public static function setUp($sFilePath=__FILE__)
    {
        self::$sFilePath = $sFilePath;
        self::$sDirPath = dirname(self::$sFilePath);
        self::$sClassMapPath = self::$sDirPath . '/admin-page-framework-class-map.php';
        self::$aClassFiles = include(self::$sClassMapPath);
        self::$sAutoLoaderPath = isset(self::$aClassFiles[ 'PowerPlugin_AdminPageFramework_RegisterClasses' ]) ? self::$aClassFiles[ 'PowerPlugin_AdminPageFramework_RegisterClasses' ] : '';
        self::$bIsMinifiedVersion = class_exists('PowerPlugin_AdminPageFramework_MinifiedVersionHeader', false);
        self::$bIsDevelopmentVersion = isset(self::$aClassFiles[ 'PowerPlugin_AdminPageFramework_ClassMapHeader' ]);
    }
    public static function getVersion()
    {
        if (! isset(self::$sAutoLoaderPath)) {
            trigger_error(self::NAME . ': ' . ' : ' . sprintf('The method, <code>%1$s</code>, is called too early. Perform <code>%2$s</code> earlier.', __METHOD__, 'setUp()'), E_USER_WARNING);
            return self::VERSION;
        }
        $_aMinifiedVersionSuffix = array( 0 => '', 1 => '.min', );
        $_aDevelopmentVersionSuffix = array( 0 => '', 1 => '.dev', );
        return self::VERSION . $_aMinifiedVersionSuffix[ ( integer ) self::$bIsMinifiedVersion ] . $_aDevelopmentVersionSuffix[ ( integer ) self::$bIsDevelopmentVersion ];
    }
    public static function getInfo()
    {
        $_oReflection = new ReflectionClass(__CLASS__);
        return $_oReflection->getConstants() + $_oReflection->getStaticProperties();
    }
}
endif;
if (! class_exists('PowerPlugin_AdminPageFramework_Bootstrap', false)) :
final class PowerPlugin_AdminPageFramework_Bootstrap {
    private static $___bLoaded = false;
    public function __construct($sLibraryPath)
    {
        if (! $this->___isLoadable()) {
            return;
        }
        PowerPlugin_AdminPageFramework_Registry::setUp($sLibraryPath);
        if (PowerPlugin_AdminPageFramework_Registry::$bIsMinifiedVersion) {
            return;
        }
        $this->___include();
    }
    private function ___isLoadable()
    {
        if (self::$___bLoaded) {
            return false;
        }
        self::$___bLoaded = true;
        return defined('ABSPATH');
    }
    private function ___include()
    {
        include(PowerPlugin_AdminPageFramework_Registry::$sAutoLoaderPath);
        new PowerPlugin_AdminPageFramework_RegisterClasses('', array( 'exclude_class_names' => array( 'PowerPlugin_AdminPageFramework_MinifiedVersionHeader', 'PowerPlugin_AdminPageFramework_BeautifiedVersionHeader', ), ), PowerPlugin_AdminPageFramework_Registry::$aClassFiles);
        self::$___bXDebug = isset(self::$___bXDebug) ? self::$___bXDebug : extension_loaded('xdebug');
        if (self::$___bXDebug) {
            new PowerPlugin_AdminPageFramework_Utility;
            new PowerPlugin_AdminPageFramework_WPUtility;
        }
    }
    private static $___bXDebug;
} new PowerPlugin_AdminPageFramework_Bootstrap(__FILE__);
endif;
