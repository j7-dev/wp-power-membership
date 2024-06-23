<?php
/**
 * Plugin Name:       Power Membership | 讓每個人都可以輕鬆建立會員制網站
 * Plugin URI:        https://github.com/j7-dev/wp-power-membership
 * Description:       your description
 * Version:           1.0.0
 * Requires at least: 5.7
 * Requires PHP:      8.0
 * Author:            J7
 * Author URI:        https://github.com/j7-dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       power-membership
 * Domain Path:       /languages
 * Tags: woocommerce, membership, membership plugin, membership site
 */

declare ( strict_types=1 );

namespace J7\PowerMembership;

use Exception;
use J7\WpUtils\Classes\WPULogUtils;
use J7\WpUtils\Classes\WPUPointUtils;

if ( ! \class_exists( 'J7\PowerMembership\Plugin' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';

	// // DELETE
	// require_once __DIR__ . '/test/LogTableCreationTrait.php';
	// require_once __DIR__ . '/test/WPULogUtils.php';
	// require_once __DIR__ . '/test/WPUPointUtils.php';


	/**
	 * Class Plugin
	 */
	final class Plugin {
		use \J7\WpUtils\Traits\PluginTrait;
		use \J7\WpUtils\Traits\SingletonTrait;
		use \J7\WpUtils\Traits\LogTableCreationTrait;

		public const LOG_TABLE_NAME = 'power_logs';
		public const POINT_SLUG     = 'pm_points';


		/**
		 * Log Utils instance
		 *
		 * @var WPULogUtils
		 */
		public WPULogUtils $log_utils_instance;

		/**
		 * Point Utils instance
		 *
		 * @var WPUPointUtils
		 */
		public WPUPointUtils $point_utils_instance;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->log_utils_instance   = new WPULogUtils( table_name: self::LOG_TABLE_NAME );
			$this->point_utils_instance = new WPUPointUtils();
			$this->point_utils_instance->init( $this->log_utils_instance );
			require_once __DIR__ . '/inc/class/class-bootstrap.php';

			$this->required_plugins = [
				[
					'name'     => 'WooCommerce',
					'slug'     => 'woocommerce',
					'required' => true,
					'version'  => '7.6.0',
				],
				[
					'name'     => 'WP Toolkit',
					'slug'     => 'wp-toolkit',
					'source'   => 'https://github.com/j7-dev/wp-toolkit/releases/latest/download/wp-toolkit.zip',
					'required' => true,
				],
			];

			$this->init(
				[
					'app_name'    => 'Power Membership',
					'github_repo' => 'https://github.com/j7-dev/wp-power-membership',
					'callback'    => [ Bootstrap::class, 'instance' ],
				]
			);
		}

		/**
		 * Activate
		 *
		 * @return void
		 * @throws Exception - Exception.
		 */
		public function activate(): void {
			$this->create_log_table( table_name: self::LOG_TABLE_NAME );
		}
	}

	Plugin::instance();
}
