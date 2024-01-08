<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin;

use J7\PowerMembership\Utils;

/**
 * 後台 UI 相關
 * 簡易後台
 */

class UI
{
	const DEFAULT_UI            = 'default';
	const SIMPLE_UI             = 'simple';
	const DEFAULT_USER_ADMIN_UI = self::SIMPLE_UI; // TODO 之後要改回 default
	const ADMIN_UI_META_KEY     = 'power_admin_ui';

	private $user_admin_ui; // 'default' | 'simple'

	public function __construct()
	{
		$this->set_user_admin_ui();
		\add_action('admin_menu', [$this, 'menu_page'], 10);
	}

	public static function get_user_admin_ui(): string
	{
		$user_id  = \get_current_user_id();
		$admin_ui = \get_user_meta($user_id, self::ADMIN_UI_META_KEY, true);
		$admin_ui = empty($admin_ui) ? self::DEFAULT_USER_ADMIN_UI : $admin_ui;
		return $admin_ui;
	}

	private function set_user_admin_ui(): void
	{
		$admin_ui            = self::get_user_admin_ui();
		$this->user_admin_ui = $admin_ui;
	}

	public function menu_page()
	{
		global $menu;
		if ('default' === $this->user_admin_ui) {
			return;
		}
		\remove_menu_page('gamipress');
		\remove_menu_page('gamipress_ranks');

		\add_submenu_page(
			'users.php',
			__('會員等級', Utils::SNAKE),
			__('會員等級', Utils::SNAKE),
			'edit_users',
			'edit.php?post_type=member_lv',
			'',
			200
		);

		\remove_submenu_page('users.php', 'profile.php');
	}
}

new UI();
