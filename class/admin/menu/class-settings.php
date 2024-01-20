<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Menu;

use J7\PowerMembership\Utils;

final class Settings
{
	const OPT_NAME = Utils::SNAKE . '_settings';
	const ENABLE_SIMPLE_ADMIN_UI_FIELD = 'enable_simple_admin_ui';
	const ENABLE_FIRST_PURCHASE_COUPON_FIELD = 'first_purchase_coupon';
	const COUPON_AMOUNT_FIELD = 'coupon_amount';
	const MIN_CART_AMOUNT_FIELD = 'min_cart_amount';
	private $args;
	private $sections = [];

	public function __construct()
	{
		$this->set_args();
		$this->set_sections();
		$this->instance();
	}

	public function instance(): void
	{
		\Redux::set_args(self::OPT_NAME, $this->args);
		\Redux::set_sections(self::OPT_NAME, $this->sections);
		\Redux::init(self::OPT_NAME);
	}

	private function set_args(): void
	{
		$args = array(
			// This is where your data is stored in the database and also becomes your global variable name.
			'opt_name'                  => self::OPT_NAME,

			// Name that appears at the top of your panel.
			'display_name'              => Utils::APP_NAME,

			// Version that appears at the top of your panel.
			'display_version'           => Utils::get_plugin_ver(),

			// Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only).
			'menu_type'                 => 'menu',

			// Show the sections below the admin menu item or not.
			'allow_sub_menu'            => false,

			// The text to appear in the admin menu.
			'menu_title'                => esc_html__(Utils::APP_NAME, Utils::SNAKE),

			// The text to appear on the page title.
			'page_title'                => esc_html__(Utils::APP_NAME, Utils::SNAKE),

			// Disable to create your own Google fonts loader.
			'disable_google_fonts_link' => false,

			// Show the panel pages on the admin bar.
			'admin_bar'                 => false,

			// Icon for the admin bar menu.
			'admin_bar_icon'            => 'dashicons-groups',

			// Priority for the admin bar menu.
			'admin_bar_priority'        => 100,

			// Sets a different name for your global variable other than the opt_name.
			'global_variable'           => self::OPT_NAME,

			// Show the time the page took to load, etc. (forced on while on localhost or when WP_DEBUG is enabled).
			'dev_mode'                  => \J7\WpToolkit\Utils::get_dev_mode(),

			// Enable basic customizer support.
			'customizer'                => false,

			// Allow the panel to open expanded.
			'open_expanded'             => false,

			// Disable the save warning when a user changes a field.
			'disable_save_warn'         => false,

			// Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
			'page_priority'             => 90,

			// For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters.
			// 'page_parent'               => 'themes.php',

			// Permissions needed to access the options panel.
			'page_permissions'          => 'manage_options',

			// Specify a custom URL to an icon.
			'menu_icon'                 => 'dashicons-groups',

			// Force your panel to always open to a specific tab (by id).
			'last_tab'                  => '',

			// Icon displayed in the admin panel next to your menu_title.
			'page_icon'                 => 'icon-themes',

			// Page slug used to denote the panel, will be based off page title, then menu title, then opt_name if not provided.
			'page_slug'                 => self::OPT_NAME,

			// On load save the defaults to DB before user clicks save.
			'save_defaults'             => true,

			// Display the default value next to each field when not set to the default value.
			'default_show'              => true,

			// What to print by the field's title if the value shown is default.
			// 'default_mark'              => '*',

			// Shows the Import/Export panel when not used as a field.
			'show_import_export'        => false,

			// The time transients will expire when the 'database' arg is set.
			'transient_time'            => 60 * MINUTE_IN_SECONDS,

			// Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output.
			'output'                    => false,

			// Allows dynamic CSS to be generated for customizer and google fonts,
			// but stops the dynamic CSS from going to the page head.
			'output_tag'                => false,

			// Disable the footer credit of Redux. Please leave if you can help it.
			'footer_credit'             => ' ',

			// If you prefer not to use the CDN for ACE Editor.
			// You may download the Redux Vendor Support plugin to run locally or embed it in your code.
			'use_cdn'                   => true,

			// Set the theme of the option panel.  Use 'wp' to use a more modern style, default is classic.
			'admin_theme'               => 'blue',

			// Enable or disable flyout menus when hovering over a menu with submenus.
			'flyout_submenus'           => false,

			// Mode to display fonts (auto|block|swap|fallback|optional)
			// See: https://developer.mozilla.org/en-US/docs/Web/CSS/@font-face/font-display.
			'font_display'              => 'swap',

			// HINTS.
			'hints'                     => array(
				'icon'          => 'el el-question-sign',
				'icon_position' => 'right',
				'icon_color'    => 'lightgray',
				'icon_size'     => 'normal',
				'tip_style'     => array(
					'color'   => 'red',
					'shadow'  => true,
					'rounded' => false,
					'style'   => '',
				),
				'tip_position'  => array(
					'my' => 'top left',
					'at' => 'bottom right',
				),
				'tip_effect'    => array(
					'show' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'mouseover',
					),
					'hide' => array(
						'effect'   => 'slide',
						'duration' => '500',
						'event'    => 'click mouseleave',
					),
				),
			),

			// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
			// Possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
			'database'                  => '',
			'network_admin'             => true,
			'search'                    => true,
		);


		// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
		// PLEASE CHANGE THESE SETTINGS IN YOUR THEME BEFORE RELEASING YOUR PRODUCT!!
		// If these are left unchanged, they will not display in your panel!
		$args['share_icons'][] = array(
			'url'   => Utils::GITHUB_REPO,
			'title' => '你的五星好評是給開發者的最大的鼓勵',
			'icon'  => 'el el-github',
		);
		$args['share_icons'][] = array(
			'url'   => 'https://cloud.luke.cafe/',
			'title' => '網站速度不夠快？ 我們的主機代管服務 ⚡ 提供 30 天免費試用',
			'icon'  => 'el el-globe',
		);


		// Panel Intro text -> before the form.
		if (!isset($args['global_variable']) || false !== $args['global_variable']) {
			if (!empty($args['global_variable'])) {
				$v = $args['global_variable'];
			} else {
				$v = str_replace('-', '_', $args['opt_name']);
			}
		}
		$args['intro_text'] = '<p>' . sprintf(esc_html__('可以到 %1$s 查看主要功能與套件特色', Utils::SNAKE), '<a href="' . Utils::GITHUB_REPO . '" target="_blank"><i class="el el-github"></i> Github 頁面</a>') . '<p>';


		// Add content after the form.
		$args['footer_text'] = '<p class="mt-10 text-center text-sm text-gray-400">
		網站速度不夠快？
		<a target="_blank" href="https://cloud.luke.cafe/"
			class="font-semibold leading-6 text-primary hover:text-primary-400">我們的主機代管服務</a> ⚡ 提供30天免費試用
	</p>';

		$this->args = $args;
	}

	private function set_sections(): void
	{
		$this->sections[] = [
			'title'            => esc_html__('一般設定', Utils::SNAKE),
			'id'               => 'general',
			'icon'             => 'el el-home',
			'fields' => [
				[
					'id'       => self::ENABLE_SIMPLE_ADMIN_UI_FIELD,
					'type'     => 'switch',
					'title'    => esc_html__('啟用簡易後台', Utils::SNAKE),
					'subtitle' => esc_html__('關閉後，會顯示 Gamipress 的外掛選單和所有設定項', Utils::SNAKE),
					'on'       => esc_html__('啟用', Utils::SNAKE),
					'off'      => esc_html__('關閉', Utils::SNAKE),
					'default'  => 1,
				],
			],
		];

		$this->sections[] = [
			'title'            => esc_html__('優惠設定', Utils::SNAKE),
			'id'               => 'coupons',
			'icon'             => 'el el-tag',
			'fields' => [
				[
					'id'       => self::ENABLE_FIRST_PURCHASE_COUPON_FIELD,
					'type'     => 'switch',
					'title'    => esc_html__('啟用首次購買優惠', Utils::SNAKE),
					'subtitle' => esc_html__('如果此用戶已登入，且從沒有在你網站買過東西，就享有折扣', Utils::SNAKE),
					'on'       => esc_html__('啟用', Utils::SNAKE),
					'off'      => esc_html__('關閉', Utils::SNAKE),
					'default'  => 0,
				],
				[
					'id'       => self::COUPON_AMOUNT_FIELD,
					'title'    => esc_html__('首次購買優惠金額', Utils::SNAKE),
					'type'     => 'text',
					'required' => array('first_purchase_coupon', 'equals', true),
				],
				[
					'id'       => self::MIN_CART_AMOUNT_FIELD,
					'type'     => 'text',
					'title'    => esc_html__('最小訂購金額需要', Utils::SNAKE),
					'subtitle' => esc_html__('滿足首次購買優惠的最小訂購金額，如果不想限制，維持空白即可', Utils::SNAKE),
					'required' => array('first_purchase_coupon', 'equals', true),
				],
			],
		];
	}
}
