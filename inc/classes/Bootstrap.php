<?php
/**
 * Bootstrap
 */

declare (strict_types=1);

namespace J7\PowerMembership;

use J7\PowerMembership\Utils\Base;
use J7\WpToolkit\PowerPlugins;
use Kucrut\Vite;
use J7\PowerMembership\MemberLv\Metabox;


if (class_exists('J7\PowerMembership\Bootstrap')) {
	return;
}

/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		new Admin\Menu\Settings();
		Admin\Ui::instance();
		Admin\Users\UserColumns::instance();
		Admin\Users\UserEdit::instance();

		MemberLv\Metabox::instance();
		MemberLv\MembershipUpgrade::instance();

		WooCommerce\Coupons\Metabox::instance();
		WooCommerce\Coupons\View::instance();

		Gamipress\Gamipress::instance();
		Gamipress\Invite::instance();
		Gamipress\Api::instance();

		Frontend\Membership::instance();

		\add_action('admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_script' ], 99);
		\add_action('admin_enqueue_scripts', [ __CLASS__, 'add_static_assets' ], 99);
		\add_action('wp_enqueue_scripts', [ __CLASS__, 'frontend_enqueue_script' ], 99);
	}

	/**
	 * Admin Enqueue script
	 * You can load the script on demand
	 *
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public static function admin_enqueue_script( $hook ): void {
		self::enqueue_script();
	}

	/**
	 * Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public static function enqueue_script(): void {

		Vite\enqueue_asset(
			Plugin::$dir . '/js/dist',
			'js/src/main.tsx',
			[
				'handle'    => Plugin::$kebab,
				'in-footer' => true,
			]
		);

		$post_id = \get_the_ID();

		\wp_localize_script(
			Plugin::$kebab,
			Plugin::$snake . '_data',
			[
				'env' => [
					'siteUrl'       => \untrailingslashit(site_url()),
					'ajaxUrl'       => \untrailingslashit(admin_url('admin-ajax.php')),
					'userId'        => \get_current_user_id(),
					'postId'        => $post_id,
					'APP_NAME'      => Plugin::$app_name,
					'KEBAB'         => Plugin::$kebab,
					'SNAKE'         => Plugin::$snake,
					'BASE_URL'      => Base::BASE_URL,
					'APP1_SELECTOR' => Base::APP1_SELECTOR,
					'API_TIMEOUT'   => Base::API_TIMEOUT,
					'nonce'         => \wp_create_nonce(Plugin::$kebab),
				],
			]
		);

		\wp_localize_script(
			Plugin::$kebab,
			'wpApiSettings',
			[
				'root'  => \untrailingslashit(\esc_url_raw(\rest_url())),
				'nonce' => \wp_create_nonce('wp_rest'),
			]
		);
	}

	/**
	 * Front-end Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public static function frontend_enqueue_script(): void {
		self::enqueue_script();
	}

	/**
	 * Add static assets
	 *
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public static function add_static_assets( $hook ): void {
		if (!\is_admin()) {
			return;
		}

		global $power_plugins_settings;
		$is_simple_admin = $power_plugins_settings[ PowerPlugins::ENABLE_SIMPLE_ADMIN_FIELD_NAME ];

		$screen = \get_current_screen();

		if ('users.php' === $hook) {
			if ($is_simple_admin) {
				\wp_enqueue_script(
					'users',
					Plugin::$url . '/assets/js/admin-users.js',
					[],
					Plugin::$version,
					[
						'strategy' => 'async',
					]
				);
			}
		}
		if ('user-edit.php' === $hook || 'profile.php' === $hook) {
			if ($is_simple_admin) {
				\wp_enqueue_script(
					'user-edit',
					Plugin::$url . '/assets/js/admin-user-edit.js',
					[],
					Plugin::$version,
					[
						'strategy' => 'async',
					]
				);
			}
		}
		if (Base::MEMBER_LV_POST_TYPE === $screen->id) {
			\wp_enqueue_script(
				Base::MEMBER_LV_POST_TYPE,
				Plugin::$url . '/assets/js/member_lv.js',
				[ 'jquery' ],
				Plugin::$version,
				[
					'strategy' => 'async',
				]
			);
		}
		\wp_localize_script(
			Base::MEMBER_LV_POST_TYPE,
			Base::MEMBER_LV_POST_TYPE . '_data',
			[
				'default_member_lv_id' => Metabox::$default_member_lv_id,
			]
		);
	}
}
