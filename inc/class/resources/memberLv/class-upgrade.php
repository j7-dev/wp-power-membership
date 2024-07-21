<?php
/**
 * 處理會員升級相關邏輯
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\PowerMembership\Utils\Base;


/**
 * Class Upgrade
 */
final class Upgrade {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 要做會員檢查的訂單狀態
	 *
	 * @var array
	 */
	public static $order_statuses = [ 'wc-completed', 'wc-processing' ];

	/**
	 * Constructor
	 */
	public function __construct() {

		foreach ( self::$order_statuses as $status ) {
			\add_action( 'woocommerce_order_status_' . $status, [ $this, 'membership_check' ], 10, 1 );
		}

		\add_action( 'trash_' . MemberLvInit::POST_TYPE, [ $this, 'remove_user_member_lv' ], 10, 3 );
	}

	/**
	 * 會員升級檢查
	 *
	 * @param int $order_id 訂單ID
	 * @return void
	 */
	public function membership_check( $order_id ): void {
		$order = \wc_get_order( $order_id );
		if ( ! ( $order instanceof \WC_Order ) ) {
			return;
		}

		$customer_id = $order->get_customer_id();
		if ( empty( $customer_id ) ) {
			return;
		}

		// 取得下個等級的門檻
		$next_member_lv = Utils::get_next_member_lv_by( 'user_id', $customer_id );
		if ( ! $next_member_lv ) {
			return;
		}

		$timestamp                = Base::calc_timestamp($next_member_lv->limit_type, $next_member_lv->limit_value, $next_member_lv->limit_unit);
		$next_member_lv_id        = $next_member_lv->id;
		$next_member_lv_threshold = (int) $next_member_lv->threshold;

		// 取得歷史累積金額
		$order_data = Base::get_order_data_by_timestamp($customer_id, $timestamp);
		$acc_amount = (int) $order_data['total'];

		if ( $acc_amount >= $next_member_lv_threshold ) {
			\update_user_meta( $customer_id, MemberLvInit::POST_TYPE, $next_member_lv_id );
		}
	}

	/**
	 * 刪除會員等級時，將用戶的等級設為上一個等級
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post.
	 * @param string   $old_status Old status.
	 * @return void
	 */
	public function remove_user_member_lv( int $post_id, \WP_Post $post, string $old_status ): void {
		$meta_key   = MemberLvInit::POST_TYPE;
		$meta_value = $post_id;

		$prev_member_lv = Utils::get_prev_member_lv_by( 'member_lv_id', $post_id );
		if ( ! $prev_member_lv ) {
			return;
		}
		$prev_member_lv_id = $prev_member_lv?->id;

		// phpcs:disable
		global $wpdb;
		$prefix = $wpdb->prefix;
		$query  = $wpdb->prepare(
			"UPDATE {$prefix}usermeta
    SET meta_value = %s
    WHERE meta_key = %s AND meta_value = %s",
			$prev_member_lv_id,
			$meta_key,
			$meta_value
		);

		$result = $wpdb->query( $query );
		// phpcs:enable
	}
}

Upgrade::instance();
