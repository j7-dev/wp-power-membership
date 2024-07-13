<?php
/**
 * Bootstrap
 */

declare ( strict_types=1 );

namespace J7\PowerMembership;

use J7\PowerMembership\Admin\Menu\Setting;
use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\WpToolkit\PowerPlugins;
use J7\PowerMembership\Utils\Base;
use Kucrut\Vite;


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
		require_once __DIR__ . '/api/index.php';
		require_once __DIR__ . '/resources/index.php';
		require_once __DIR__ . '/admin/index.php';
		require_once __DIR__ . '/woocommerce/index.php';
		require_once __DIR__ . '/front-end/index.php';

		// PENDING
		// \add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_script' ), 99 );
		// \add_action( 'admin_enqueue_scripts', array( $this, 'add_static_assets' ), 100 );
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
		// PENDING
		// \wp_enqueue_script(
		// Plugin::$kebab,
		// Plugin::$url . '/js/dist/index.js',
		// [ 'jquery' ],
		// Plugin::$version,
		// [
		// 'in_footer' => true,
		// 'strategy'  => 'async',
		// ]
		// );

		// \wp_enqueue_style(
		// Plugin::$kebab,
		// Plugin::$url . '/js/dist/assets/css/index.css',
		// [],
		// Plugin::$version
		// );

		// React script

		Vite\enqueue_asset(
			Plugin::$dir . '/js/dist',
			'js/src/main.tsx',
			array(
				'handle'    => Plugin::$kebab,
				'in-footer' => true,
			)
		);

		$post_id   = \get_the_ID();
		$permalink = \get_permalink( $post_id );

		\wp_localize_script(
			Plugin::$kebab,
			Plugin::$snake . '_data',
			array(
				'env' => array(
					'siteUrl'       => \site_url(),
					'ajaxUrl'       => \admin_url( 'admin-ajax.php' ),
					'userId'        => \wp_get_current_user()?->data?->ID ?? null,
					'postId'        => $post_id,
					'permalink'     => $permalink,
					'APP_NAME'      => Plugin::$app_name,
					'KEBAB'         => Plugin::$kebab,
					'SNAKE'         => Plugin::$snake,
					'BASE_URL'      => Base::BASE_URL,
					'APP1_SELECTOR' => Base::APP1_SELECTOR,
					'API_TIMEOUT'   => Base::API_TIMEOUT,
					'nonce'         => \wp_create_nonce( Plugin::$kebab ),

				),
			)
		);

		\wp_localize_script(
			Plugin::$kebab,
			'wpApiSettings',
			array(
				'root'  => \untrailingslashit( \esc_url_raw( rest_url() ) ),
				'nonce' => \wp_create_nonce( 'wp_rest' ),
			)
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
	 * PENDING
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
			[ MemberLvInit::POST_TYPE, 'user-edit', 'users' ],
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
					[],
					Plugin::$version,
					[
						'strategy' => 'async',
					]
				);
			}
		}
		if ( 'user-edit.php' === $hook || 'profile.php' === $hook ) {
			if ( $is_simple_admin ) {
				\wp_enqueue_script(
					'user-edit',
					Plugin::$url . '/inc/assets/js/admin-user-edit.js',
					[],
					Plugin::$version,
					[
						'strategy' => 'async',
					]
				);
			}
		}
		if ( MemberLvInit::POST_TYPE === $screen->id ) {
			\wp_enqueue_script(
				MemberLvInit::POST_TYPE,
				Plugin::$url . '/inc/assets/js/member_lv.js',
				[ 'jquery' ],
				Plugin::$version,
				[
					'strategy' => 'async',
				]
			);
		}
		\wp_localize_script(
			MemberLvInit::POST_TYPE,
			MemberLvInit::POST_TYPE . '_data',
			[
				'default_member_lv_id' => MemberLvInit::instance()->default_member_lv_id,
			]
		);
	}
}
