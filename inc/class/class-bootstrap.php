<?php
/**
 * Bootstrap
 */

declare ( strict_types=1 );

namespace J7\PowerMembership;

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\WpToolkit\PowerPlugins;
use J7\PowerMembership\Admin\Menu\Setting;


/**
 * Class Bootstrap
 */
final class Bootstrap {
	use \J7\WpUtils\Traits\SingletonTrait;


	/**
	 * Constructor
	 */
	public function __construct() {
		require_once __DIR__ . '/utils/index.php';
		require_once __DIR__ . '/resources/index.php';
		require_once __DIR__ . '/admin/index.php';
		require_once __DIR__ . '/woocommerce/index.php';

		// require_once __DIR__ . '/front-end/index.php';

		\add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 99 );
		\add_action( 'admin_enqueue_scripts', array( $this, 'add_static_assets' ), 100 );
		\add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_script' ), 99 );
	}

	/**
	 * Admin Enqueue script
	 * You can load the script on demand
	 *
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public function admin_enqueue_script( $hook ): void {
		$this->enqueue_script();
	}

	/**
	 * Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public function enqueue_script(): void {
		\wp_enqueue_script(
			Plugin::$kebab,
			Plugin::$url . '/js/dist/index.js',
			array( 'jquery' ),
			Plugin::$version,
			array(
				'in_footer' => true,
				'strategy'  => 'async',
			)
		);

		\wp_enqueue_style(
			Plugin::$kebab,
			Plugin::$url . '/js/dist/assets/css/index.css',
			array(),
			Plugin::$version
		);
	}

	/**
	 * Front-end Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public function frontend_enqueue_script(): void {
		$this->enqueue_script();
	}

	/**
	 * Add static assets
	 *
	 * @param string $hook current page hook
	 *
	 * @return void
	 */
	public function add_static_assets( string $hook ): void {
		if ( ! \is_admin() ) {
			return;
		}
		$screen = \get_current_screen();

		if ( 'toplevel_page_power_plugins_settings' === $hook || in_array(
			$screen->id,
			array( MemberLvInit::POST_TYPE, 'user-edit', 'users' ),
			true
		) ) {
			\wp_enqueue_script(
				Plugin::$kebab . '-settings',
				Plugin::$url . '/inc/assets/dist/index.js',
				[ 'jquery' ],
				Plugin::$version,
				[
					'strategy'  => 'async',
					'in_footer' => true,
				]
			);

			\wp_localize_script(
				Plugin::$kebab . '-settings',
				Plugin::$snake . '_data',
				Setting::get_params()
			);

			\wp_enqueue_style(
				Plugin::$kebab . '-settings',
				Plugin::$url . '/inc/assets/dist/css/index.css',
				[],
				Plugin::$version
			);
		}

		global $power_plugins_settings;
		$is_simple_admin = $power_plugins_settings[ PowerPlugins::ENABLE_SIMPLE_ADMIN_FIELD_NAME ];

		if ( 'users.php' === $hook ) {
			if ( $is_simple_admin ) {
				\wp_enqueue_script(
					'users',
					Plugin::$url . '/inc/assets/js/admin-users.js',
					array(),
					Plugin::$version,
					array(
						'strategy' => 'async',
					)
				);
			}
		}
		if ( 'user-edit.php' === $hook || 'profile.php' === $hook ) {
			if ( $is_simple_admin ) {
				\wp_enqueue_script(
					'user-edit',
					Plugin::$url . '/inc/assets/js/admin-user-edit.js',
					array(),
					Plugin::$version,
					array(
						'strategy' => 'async',
					)
				);
			}
		}
		if ( MemberLvInit::POST_TYPE === $screen->id ) {
			\wp_enqueue_script(
				MemberLvInit::POST_TYPE,
				Plugin::$url . '/inc/assets/js/member_lv.js',
				array( 'jquery' ),
				Plugin::$version,
				array(
					'strategy' => 'async',
				)
			);
		}
		\wp_localize_script(
			MemberLvInit::POST_TYPE,
			MemberLvInit::POST_TYPE . '_data',
			array(
				'default_member_lv_id' => MemberLvInit::instance()->default_member_lv_id,
			)
		);
	}
}
