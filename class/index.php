<?php

declare(strict_types=1);

namespace J7\PowerMembership;

require_once __DIR__ . '/admin/index.php';
require_once __DIR__ . '/memberLv/index.php';
require_once __DIR__ . '/woocommerce/index.php';

use J7\PowerMembership\Utils;
use J7\PowerMembership\MemberLv\Metabox;
use J7\PowerMembership\Admin\Menu\Settings;


final class Bootstrap
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
		new Admin\Menu\Settings();
		new Admin\UI();
		new Admin\Users\UserColumns();
		new Admin\Users\UserEdit();

		new MemberLv\Metabox();
		new MemberLv\MembershipUpgrade();

		new WooCommerce\Coupons\Metabox();
		new WooCommerce\Coupons\View();
	}

	public function add_static_assets($hook): void
	{
		if (!is_admin()) {
			return;
		}

		global $power_membership_settings;
		$is_admin_ui_simple = $power_membership_settings[Settings::ENABLE_SIMPLE_ADMIN_UI_FIELD];

		$screen = \get_current_screen();

		if (in_array($screen->id, $this->tailwind_screen_ids)) {
			\wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.0');
		}
		if ('users.php' == $hook) {
			if ($is_admin_ui_simple) {
				\wp_enqueue_script('users', Utils::get_plugin_url() . '/assets/js/admin-users.js', array(), Utils::get_plugin_ver(), [
					'strategy' => 'async'
				]);
			}
		}
		if ('user-edit.php' == $hook || 'profile.php' == $hook) {
			if ($is_admin_ui_simple) {
				\wp_enqueue_script('user-edit', Utils::get_plugin_url() . '/assets/js/admin-user-edit.js', array(), Utils::get_plugin_ver(), [
					'strategy' => 'async'
				]);
			}
		}
		if (Utils::MEMBER_LV_POST_TYPE === $screen->id) {
			\wp_enqueue_script(Utils::MEMBER_LV_POST_TYPE, Utils::get_plugin_url() . '/assets/js/member_lv.js', array('jquery'), Utils::get_plugin_ver(), [
				'strategy' => 'async'
			]);
		}
		\wp_localize_script(Utils::MEMBER_LV_POST_TYPE, Utils::MEMBER_LV_POST_TYPE . '_data', [
			'default_member_lv_id' => Metabox::$default_member_lv_id,
		]);
	}

	public function add_tailwind_config(): void
	{
		$screen = \get_current_screen();
		if (in_array($screen->id, $this->tailwind_screen_ids)) :
?>
			<script>
				tailwind.config = {
					important: '.tailwindcss',
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
