<?php

declare (strict_types = 1);
namespace J7\PowerMembership;

use J7\PowerMembership\Utils;

require_once __DIR__ . '/class-user-columns.php';
require_once __DIR__ . '/memberLv/index.php';

class Bootstrap
{

    public function __construct()
    {
        \add_action('admin_enqueue_scripts', [ $this, 'add_static_assets' ]);
        \add_action('admin_head', [ $this, 'add_tailwind_config' ], 1000);

    }

    public function add_static_assets($hook)
    {
        if (!is_admin()) {
            return;
        }

        $screen = \get_current_screen();
        if ('users.php' == $hook || 'member_lv' == $screen->id) {
            \wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0');
        }
        if ('users.php' == $hook) {
            \wp_enqueue_script('users', Utils::get_plugin_url() . '/assets/js/admin-users.js', array(), Utils::get_plugin_ver(), true);
        }
        if ('user-edit.php' == $hook || 'profile.php' == $hook) {
            \wp_enqueue_script('user-edit', Utils::get_plugin_url() . '/assets/js/admin-user-edit.js', array(), Utils::get_plugin_ver(), true);
        }

        \wp_enqueue_style(Utils::KEBAB . '-css', Utils::get_plugin_url() . '/assets/css/admin.css', array(), Utils::get_plugin_ver());
    }

    public function add_tailwind_config()
    {
        ?>
<script>
    tailwind.config = {
			prefix: 'tw-',
    }
  </script>
				<?php
}

}
