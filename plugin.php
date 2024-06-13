<?php
/**
 * Plugin Name:       Power Membership | 我的 WordPress 外掛
 * Plugin URI:        https://cloud.luke.cafe/plugins/
 * Description:       your description
 * Version:           0.0.1
 * Requires at least: 5.7
 * Requires PHP:      8.
 * Author:            Your Name
 * Author URI:        [YOUR GITHUB URL]
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       power_membership
 * Domain Path:       /languages
 * Tags: your tags
 */

declare (strict_types = 1);

namespace J7\PowerMembership;

use J7\WpUtils\Classes\LogBase;

if ( ! \class_exists( 'J7\PowerMembership\Plugin' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';

	// DELETE
	require_once __DIR__ . '/test/LogBase.php';
	require_once __DIR__ . '/test/PointBase.php';


	/**
		* Class Plugin
		*/
	final class Plugin extends LogBase {
		use \J7\WpUtils\Traits\PluginTrait;
		use \J7\WpUtils\Traits\SingletonTrait;

		/**
		 * Constructor
		 *
		 * @param mixed ...$args Arguments
		 */
		public function __construct( mixed ...$args ) {

			parent::__construct( ...$args );

			require_once __DIR__ . '/inc/class/class-bootstrap.php';

			$this->required_plugins = array(
				array(
					'name'     => 'WooCommerce',
					'slug'     => 'woocommerce',
					'required' => true,
					'version'  => '7.6.0',
				),
				array(
					'name'     => 'WP Toolkit',
					'slug'     => 'wp-toolkit',
					'source'   => 'https://github.com/j7-dev/wp-toolkit/releases/latest/download/wp-toolkit.zip',
					'required' => true,
				),
			);

			$this->init(
				array(
					'app_name'    => 'Power Membership',
					'github_repo' => 'https://github.com/j7-dev/wp-power-membership',
					'callback'    => array( Bootstrap::class, 'instance' ),
				)
			);
		}

		/**
		 * Activate
		 *
		 * @return void
		 */
		public function activate(): void {
			$this->create_table();
		}
	}

	Plugin::instance( 'power_logs' );
}
