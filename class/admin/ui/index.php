<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin;

use J7\PowerMembership\Utils;
use J7\PowerMembership\Admin\Menu\Settings;

/**
 * 後台 UI 相關
 * 簡易後台
 */

final class UI
{
	public function __construct()
	{
		global $power_membership_settings;

		if ($power_membership_settings[Settings::ENABLE_SIMPLE_ADMIN_UI_FIELD]) {
			\add_action('admin_init', [$this, 'remove_gamipress_admin_notices'], 10);
			\add_action('admin_menu', [$this, 'menu_page'], 10);
			\add_action('admin_head', [$this, 'remove_metabox'], 200);
		}
	}

	public function remove_gamipress_admin_notices(): void
	{
		\remove_action('admin_notices', 'gamipress_admin_notices');
	}


	public function menu_page(): void
	{
		\remove_menu_page('gamipress');
		\remove_menu_page('gamipress_ranks');

		\add_submenu_page(
			'users.php',
			__('會員等級', Utils::TEXT_DOMAIN),
			__('會員等級', Utils::TEXT_DOMAIN),
			'edit_users',
			'edit.php?post_type=' . Utils::MEMBER_LV_POST_TYPE,
			'',
			200
		);

		\remove_submenu_page('users.php', 'profile.php');
	}

	public function remove_metabox(): void
	{
		// \remove_meta_box('rank-details', Utils::MEMBER_LV_POST_TYPE, 'side');
		\remove_meta_box('rank-template', Utils::MEMBER_LV_POST_TYPE, 'side');
		\remove_meta_box('postexcerpt', Utils::MEMBER_LV_POST_TYPE, 'normal');
		\remove_meta_box('authordiv', Utils::MEMBER_LV_POST_TYPE, 'normal');
		\remove_meta_box('rank-data', Utils::MEMBER_LV_POST_TYPE, 'advanced');
		\remove_meta_box('gamipress-requirements-ui', Utils::MEMBER_LV_POST_TYPE, 'advanced');
		\remove_meta_box('gamipress-earners', Utils::MEMBER_LV_POST_TYPE, 'advanced');
		// \remove_meta_box('gamipress-wc-product-points', 'product', 'side');
	}
}
