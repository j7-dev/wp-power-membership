<?php

/**
 * Plugin Name:       Power Membership | 讓每個人都可以輕鬆建立會員制網站
 * Plugin URI:        https://cloud.luke.cafe/plugins/power-membership/
 * Description:       Power Membership 可以設定會員升級需要的累積消費門檻，並針對特定會員等級發放優惠，也改善介面，可輕鬆查看會員的消費總覽。
 * Version:           0.0.4
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            J7
 * Author URI:        https://github.com/j7-dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       power-partner-server
 * Domain Path:       /languages
 * Tags: member, user, membership, membership site, membership plugin, membership system, membership website, membership management, membership management system, membership management plugin, membership management website, membership management system plugin, membership management system website, membership management plugin wordpress, membership management system wordpress, membership management website wordpress, membership management syste
 */

declare(strict_types=1);

namespace J7\PowerMembership;

use J7\PowerMembership\Utils;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;



class Init
{
	private static $instance;
	public static $is_all_plugins_activated = false;
	const GAMIPRESS_CLASS = 'GamiPress';
	const WOOCOMMERCE_CLASS = 'WooCommerce';
	const METABOX_CLASS = 'RW_Meta_Box';

	public function __construct()
	{
		require_once __DIR__ . '/required_plugins/index.php';
		require_once __DIR__ . '/vendor/autoload.php';
		require_once __DIR__ . '/utils/index.php';
		require_once __DIR__ . '/class/index.php';

		\register_activation_hook(__FILE__, [$this, 'activate']);
		\register_deactivation_hook(__FILE__, [$this, 'deactivate']);
		\add_action('tgmpa_register', [$this, 'register_required_plugins']);
		\add_action('plugins_loaded', [$this, 'check_required_plugins']);

		$this->plugin_update_checker();
	}

	public function check_required_plugins()
	{
		self::$is_all_plugins_activated = \class_exists(self::GAMIPRESS_CLASS) && \class_exists(self::WOOCOMMERCE_CLASS) && \class_exists(self::METABOX_CLASS);

		if (self::$is_all_plugins_activated) {
			new Bootstrap();
		}
	}

	public static function instance()
	{
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * wp plugin 更新檢查 update checker
	 */
	public function plugin_update_checker(): void
	{
		$updateChecker = PucFactory::buildUpdateChecker(
			Utils::GITHUB_REPO,
			__FILE__,
			Utils::KEBAB
		);
		$updateChecker->setBranch('master');
		// $updateChecker->setAuthentication(Utils::GITHUB_PAT);
		$updateChecker->getVcsApi()->enableReleaseAssets();
	}

	public function register_required_plugins(): void
	{
		$plugins = [
			[
				'name'     => 'GamiPress',
				'slug'     => 'gamipress',
				'required' => true,
				'version' => '6.7.0',
			],
			[
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
				'version' => '7.6.1',
			],
			[
				'name'     => 'Metabox',
				'slug'     => 'meta-box',
				'required' => true,
			],
		];

		/*
         * Array of configuration settings. Amend each line as needed.
         *
         * TGMPA will start providing localized text strings soon. If you already have translations of our standard
         * strings available, please help us make TGMPA even better by giving us access to these translations or by
         * sending in a pull-request with .po file(s) with the translations.
         *
         * Only uncomment the strings in the config array if you want to customize the strings.
         */
		$config = array(
			'id'           => 'power-membership', // Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '', // Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins', // Menu slug.
			'parent_slug'  => 'plugins.php', // Parent menu slug.
			'capability'   => 'manage_options', // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true, // Show admin notices or not.
			'dismissable'  => false, // If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => __('這個訊息將在依賴套件被安裝並啟用後消失。Power Membership 沒有這些依賴套件的情況下將無法運作！', 'power_membership'), // If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => true, // Automatically activate plugins after installation or not.
			'message'      => '', // Message to output right before the plugins table.
			'strings'      => array(
				'page_title'                      => __('安裝依賴套件', 'power_membership'),
				'menu_title'                      => __('安裝依賴套件', 'power_membership'),
				'installing'                      => __('安裝套件: %s', 'power_membership'), // translators: %s: plugin name.
				'updating'                        => __('更新套件: %s', 'power_membership'), //translators: %s: plugin name.
				'oops'                            => __('OOPS! plugin API 出錯了', 'power_membership'),
				'notice_can_install_required'     => _n_noop(
					//translators: 1: plugin name(s).
					'Power Membership 依賴套件: %1$s.',
					'Power Membership 依賴套件: %1$s.',
					'power_membership'
				),
				'notice_can_install_recommended'  => _n_noop(
					// translators: 1: plugin name(s).
					'Power Membership 推薦套件: %1$s.',
					'Power Membership 推薦套件: %1$s.',
					'power_membership'
				),
				'notice_ask_to_update'            => _n_noop(
					// translators: 1: plugin name(s).
					'以下套件需要更新到最新版本來兼容 Power Membership: %1$s.',
					'以下套件需要更新到最新版本來兼容 Power Membershipe: %1$s.',
					'power_membership'
				),
				'notice_ask_to_update_maybe'      => _n_noop(
					// translators: 1: plugin name(s).
					'以下套件有更新: %1$s.',
					'以下套件有更新: %1$s.',
					'power_membership'
				),
				'notice_can_activate_required'    => _n_noop(
					// translators: 1: plugin name(s).
					'以下依賴套件目前為停用狀態: %1$s.',
					'以下依賴套件目前為停用狀態: %1$s.',
					'power_membership'
				),
				'notice_can_activate_recommended' => _n_noop(
					// translators: 1: plugin name(s).
					'以下推薦套件目前為停用狀態: %1$s.',
					'以下推薦套件目前為停用狀態: %1$s.',
					'power_membership'
				),
				'install_link'                    => _n_noop(
					'安裝套件',
					'安裝套件',
					'power_membership'
				),
				'update_link'                     => _n_noop(
					'更新套件',
					'更新套件',
					'power_membership'
				),
				'activate_link'                   => _n_noop(
					'啟用套件',
					'啟用套件',
					'power_membership'
				),
				'return'                          => __('回到安裝依賴套件', 'power_membership'),
				'plugin_activated'                => __('套件啟用成功', 'power_membership'),
				'activated_successfully'          => __('以下套件已成功啟用:', 'power_membership'),
				// translators: 1: plugin name.
				'plugin_already_active'           => __('沒有執行任何動作 %1$s 已啟用', 'power_membership'),
				// translators: 1: plugin name.
				'plugin_needs_higher_version'     => __('Power Membership 未啟用。Power Membership 需要新版本的 %s 。請更新套件。', 'power_membership'),
				// translators: 1: dashboard link.
				'complete'                        => __('所有套件已成功安裝跟啟用 %1$s', 'power_membership'),
				'dismiss'                         => __('關閉通知', 'power_membership'),
				'notice_cannot_install_activate'  => __('有一個或以上的依賴/推薦套件需要安裝/更新/啟用', 'power_membership'),
				'contact_admin'                   => __('請聯繫網站管理員', 'power_membership'),

				'nag_type'                        => 'error', // Determines admin notice type - can only be one of the typical WP notice classes, such as 'updated', 'update-nag', 'notice-warning', 'notice-info' or 'error'. Some of which may not work as expected in older WP versions.
			),
		);

		\tgmpa($plugins, $config);
	}


	public function activate(): void
	{
		$this->create_member_lv_post_type();
	}

	public function deactivate(): void
	{
		// 刪除會員等級 post type 或是 transient
	}

	private function create_member_lv_post_type(): void
	{
		$post_type = Utils::MEMBER_LV_POST_TYPE;
		if (\post_type_exists($post_type)) {
			return;
		}

		// create member_lv Rank Type in Gamipress
		\wp_insert_post(
			array(
				'post_title'  => '會員等級',
				'post_type'   => 'rank-type',
				'post_status' => 'publish',
				'post_name'   => Utils::MEMBER_LV_POST_TYPE,
				'meta_input'  => array(
					'_gamipress_plural_name' => '會員等級',
				),
			)
		);
	}
}

Init::instance();
