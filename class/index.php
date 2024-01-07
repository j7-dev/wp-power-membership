<?php

declare (strict_types = 1);
namespace J7\PowerMembership;

require_once __DIR__ . '/class-user-columns.php';
require_once __DIR__ . '/memberLv/index.php';

class Bootstrap extends Utils
{

    public function __construct()
    {
        \add_action('admin_enqueue_scripts', [ $this, 'add_static_assets' ]);
    }

    public function add_static_assets($hook)
    {
        if ('users.php' == $hook) {
            \wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0');
            \wp_enqueue_script('users', self::get_plugin_url() . '/assets/js/admin-users.js', array(), self::get_plugin_ver(), true);
        }
        if ('user-edit.php' == $hook || 'profile.php' == $hook) {
            \wp_enqueue_script('user-edit', self::get_plugin_url() . '/assets/js/admin-user-edit.js', array(), self::get_plugin_ver(), true);
        }

        \wp_enqueue_style(self::KEBAB . '-css', self::get_plugin_url() . '/assets/css/admin.css', array(), self::get_plugin_ver());
    }

}
