<?php
/**
 * 後台 UI 相關
 */

declare(strict_types=1);

namespace J7\PowerMembership\Admin;

use J7\PowerMembership\Utils\Base;
use J7\WpToolkit\PowerPlugins;

/**
 * 後台 UI 相關
 * 簡易後台
 */
final class Ui {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action('setup_theme', [ $this, 'init' ], 110);
	}

	/**
	 * 初始化
	 */
	public function init(): void {
		global $power_plugins_settings;

		if ($power_plugins_settings[ PowerPlugins::ENABLE_SIMPLE_ADMIN_FIELD_NAME ] ?? false) {
			\add_action('admin_init', [ $this, 'remove_gamipress_admin_notices' ], 10);
			\add_action('admin_menu', [ $this, 'menu_page' ], 10);
			\add_action('admin_head', [ $this, 'remove_metabox' ], 200);
		}
	}

	/**
	 * 移除 GamiPress 的通知
	 */
	public function remove_gamipress_admin_notices(): void {
		\remove_action('admin_notices', 'gamipress_admin_notices');
	}

	/**
	 * 新增會員等級的選單
	 */
	public function menu_page(): void {
		\remove_menu_page('gamipress');
		\remove_menu_page('gamipress_ranks');

		\add_submenu_page(
			'users.php',
			__('會員等級', 'power_membership'),
			__('會員等級', 'power_membership'),
			'edit_users',
			'edit.php?post_type=' . Base::MEMBER_LV_POST_TYPE,
			'',
			200
		);

		\remove_submenu_page('users.php', 'profile.php');
	}

	/**
	 * 移除 GamiPress 的 meta box
	 */
	public function remove_metabox(): void {
		// \remove_meta_box('rank-details', Base::MEMBER_LV_POST_TYPE, 'side');
		\remove_meta_box('rank-template', Base::MEMBER_LV_POST_TYPE, 'side');
		\remove_meta_box('postexcerpt', Base::MEMBER_LV_POST_TYPE, 'normal');
		\remove_meta_box('authordiv', Base::MEMBER_LV_POST_TYPE, 'normal');
		\remove_meta_box('rank-data', Base::MEMBER_LV_POST_TYPE, 'advanced');
		\remove_meta_box('gamipress-requirements-ui', Base::MEMBER_LV_POST_TYPE, 'advanced');
		\remove_meta_box('gamipress-earners', Base::MEMBER_LV_POST_TYPE, 'advanced');
		// \remove_meta_box('gamipress-wc-product-points', 'product', 'side');
	}
}
