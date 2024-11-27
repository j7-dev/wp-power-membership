<?php
/**
 * Plugin Name:       Power Membership | 訂製版本 for elittleworld
 * Plugin URI:        https://cloud.luke.cafe/plugins/power-membership/
 * Description:       Power Membership 可以設定會員升級需要的累積消費門檻，並針對特定會員等級發放優惠，也改善介面，可輕鬆查看會員的消費總覽。
 * Version:           0.2.2
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            J7
 * Author URI:        https://github.com/j7-dev
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       power-membership
 * Domain Path:       /languages
 * Tags: member, user, membership, membership site, membership plugin, membership system, membership website, membership management
 */

declare ( strict_types=1 );

namespace J7\PowerMembership;

if ( \class_exists( 'J7\PowerMembership\Plugin' ) ) {
	return;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Class Plugin
 */
final class Plugin {
	use \J7\WpUtils\Traits\PluginTrait;
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		// if your plugin depends on other plugins, you can add them here
		$this->required_plugins = [
			[
				'name'     => 'WooCommerce',
				'slug'     => 'woocommerce',
				'required' => true,
				'version'  => '7.6.0',
			],
			[
				'name'     => 'GamiPress',
				'slug'     => 'gamipress',
				'version'  => '6.7.0',
				'required' => true,
			],
			[
				'name'     => 'WP Toolkit',
				'slug'     => 'wp-toolkit',
				'source'   => 'https://github.com/j7-dev/wp-toolkit/releases/latest/download/wp-toolkit.zip',
				'required' => true,
				'version'  => '0.3.1',
			],
		];

		$this->init(
			[
				'app_name'    => 'power-membership',
				'github_repo' => 'https://github.com/j7-dev/wp-power-membership',
				'callback'    => [ Bootstrap::class, 'instance' ],
			]
		);
	}
}

Plugin::instance();
