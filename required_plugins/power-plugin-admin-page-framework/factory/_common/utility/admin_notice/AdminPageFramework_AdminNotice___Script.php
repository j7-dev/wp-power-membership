<?php
/*
 * Admin Page Framework v3.9.1 by Michael Uno
 * Compiled with Admin Page Framework Compiler <https://github.com/michaeluno/power-plugin-compiler>
 * <https://en.michaeluno.jp/power-plugin>
 * Copyright (c) 2013-2022, Michael Uno; Licensed under MIT <https://opensource.org/licenses/MIT>
 */

class PowerPlugin_AdminPageFramework_AdminNotice___Script extends PowerPlugin_AdminPageFramework_Factory___Script_Base {
    public function load()
    {
        wp_enqueue_script('jquery');
    }
    public static function getScript()
    {
        return <<<JAVASCRIPTS
(function(jQuery){jQuery(document).ready(function(){var _oAdminNotices=jQuery('.power-plugin-settings-notice-message');if(_oAdminNotices.length){var _oContainer=jQuery(_oAdminNotices).css('margin','0').wrap("<div class='power-plugin-admin-notice-animation-container'></div>");_oContainer.css('margin-top','1em');_oContainer.css('margin-bottom','1em');jQuery(_oAdminNotices).css('visibility','hidden').slideDown(800).css({opacity:0,visibility:'visible'}).animate({opacity:1},400)}})}(jQuery))
JAVASCRIPTS;
    }
}
