<?php
/**
 * 初始化
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\Order;

use J7\PowerMembership\Plugin;
use J7\WpUtils\Classes\WPUPoint;
use J7\WpUtils\Classes\WPUPointUtils;
use J7\PowerMembership\Resources\Point\Metabox;
use J7\PowerMembership\Resources\MemberLv\Utils;


/**
 * Class Order
 */
final class Order {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'woocommerce_payment_complete', array( $this, 'bonus_on_certain_day' ) );
	}

	/**
	 * Bonus on certain day
	 *
	 * @return void
	 */
	public function bonus_on_certain_day( $order_id ): void {
		// 只有每週四、週日才執行
		if ( ! in_array(
			gmdate( 'l', time() + 8 * 3600 ),
			array( 'Thursday', 'Sunday' ),
			true
		) ) {
			return;
		}

		$order = \wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$subtotal    = $order->get_subtotal();
		$customer_id = $order->get_customer_id();

		$default_point_slug = WPUPointUtils::DEFAULT_POINT_SLUG;
		$point              = Plugin::instance()->point_utils_instance->get_point_by_slug( $default_point_slug );

		// TODO
		// 消費每  $2000 ＝ 20 購物金
		$award_points = floor( $subtotal / 2000 ) * 20;

		$point->award_points_to_user(
			user_id: (int) $customer_id,
			args: array(
				'title' => "訂單每消費 $2000 送 20 點購物金，共 {$award_points} 點，訂單編號：{$order_id}",
				'type'  => 'system',
			),
			points: $award_points
		);
	}
}

Order::instance();
