<?php
/**
 * Front-end MyAccount Page
 * 我的學習
 */

declare( strict_types=1 );

namespace J7\PowerMembership\FrontEnd;

use J7\PowerMembership\Utils\Base;

/**
 * Class FrontEnd
 */
final class MyAccount {
	use \J7\WpUtils\Traits\SingletonTrait;

	public const POINT_LOG_ENDPOINT = 'pm_point_log';

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'init', [ $this, 'custom_account_endpoint' ] );
		\add_filter( 'woocommerce_account_menu_items', [ $this, 'custom_menu_items' ], 100, 1 );
		\add_action(
			'woocommerce_account_' . self::POINT_LOG_ENDPOINT . '_endpoint',
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Custom account endpoint 我的學習
	 */
	public function custom_account_endpoint(): void {
		\add_rewrite_endpoint( self::POINT_LOG_ENDPOINT, EP_ROOT | EP_PAGES );
	}

	/**
	 * Add menu item 我的學習
	 *
	 * @param array $items Menu items.
	 *
	 * @return array
	 */
	public function custom_menu_items( array $items ): array {
		// 重新排序，排在控制台後
		return array_slice( $items, 0, 1, true ) + [
			self::POINT_LOG_ENDPOINT => '購物金紀錄',
		] + array_slice( $items, 1, null, true );
	}

	/**
	 * Render courses
	 */
	public function render_page(): void {
		$id = \substr( Base::APP1_SELECTOR, 1 );
		printf(
		/*html*/            '<div id="%1$s" class="w-full"></div>',
			$id // phpcs:ignore
		);
	}
}

MyAccount::instance();
