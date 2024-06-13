<?php
/**
 * Bootstrap
 */

declare (strict_types = 1);

namespace J7\PowerMembership;

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
	 * Front-end Enqueue script
	 * You can load the script on demand
	 *
	 * @return void
	 */
	public function frontend_enqueue_script(): void {
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
}
