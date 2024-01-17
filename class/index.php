<?php

declare(strict_types=1);

namespace J7\PowerMembership;

require_once __DIR__ . '/admin/index.php';
require_once __DIR__ . '/memberLv/index.php';
require_once __DIR__ . '/woocommerce/index.php';

use J7\PowerMembership\Admin\UI;
use J7\PowerMembership\Utils;


class Bootstrap
{
	private $tailwind_screen_ids = [Utils::MEMBER_LV_POST_TYPE, 'user-edit', 'users'];

	public function __construct()
	{
		$this->init();
		\add_action('admin_enqueue_scripts', [$this, 'add_static_assets']);
		\add_action('admin_head', [$this, 'add_tailwind_config'], 1000);
		\add_action('init', array($this, 'remove_notices'), 20);
	}

	private function init()
	{
		new Admin\UI();
		new Admin\Users\UserColumns();
		new Admin\Users\UserEdit();

		new MemberLv\Metabox();
		new MemberLv\MembershipUpgrade();

		new WooCommerce\Coupons\Metabox();
		new WooCommerce\Coupons\View();
		// new WooCommerce\Coupons\MenuPage();
	}

	public function add_static_assets($hook)
	{
		if (!is_admin()) {
			return;
		}

		$screen = \get_current_screen();
		if (in_array($screen->id, $this->tailwind_screen_ids)) {
			\wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0');
		}
		if ('users.php' == $hook) {
			if ('simple' === UI::get_user_admin_ui()) {
				\wp_enqueue_script('users', Utils::get_plugin_url() . '/assets/js/admin-users.js', array(), Utils::get_plugin_ver(), true);
			}
		}
		if ('user-edit.php' == $hook || 'profile.php' == $hook) {
			if ('simple' === UI::get_user_admin_ui()) {
				\wp_enqueue_script('user-edit', Utils::get_plugin_url() . '/assets/js/admin-user-edit.js', array(), Utils::get_plugin_ver(), true);
			}
		}

		\wp_enqueue_style(Utils::KEBAB . '-css', Utils::get_plugin_url() . '/assets/css/admin.css', array(), Utils::get_plugin_ver());
	}

	public function add_tailwind_config()
	{
		$screen = \get_current_screen();
		if (in_array($screen->id, $this->tailwind_screen_ids)) :
?>
			<script>
				tailwind.config = {
					prefix: 'tw-',
				}
			</script>
<?php
		endif;
	}

	public function remove_notices(): void
	{
		\remove_action('admin_notices', array(\TGM_Plugin_Activation::$instance, 'notices'));
	}
}
