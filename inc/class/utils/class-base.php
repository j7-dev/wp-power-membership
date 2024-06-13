<?php
/**
 * Base
 */

declare (strict_types = 1);

namespace J7\PowerMembership\Utils;

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;

/**
 * Class Base
 */
abstract class Base {
	const DEFAULT_IMAGE = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';
	const CACHE_TIME    = 24 * HOUR_IN_SECONDS;

	/**
	 * 處理會員升級相關邏輯
	 * _gamipress_member_lv_rank: 1026 (會員等級的 post id)
	 * _gamipress_member_lv_previous_rank: 1020 (會員等級的 post id)
	 * _gamipress_member_lv_rank_earned_time: 1704704213 (秒)
	 */

	const CURRENT_MEMBER_LV_META_KEY = MemberLvInit::POST_TYPE;
	// 存 timestamp 秒 10位數
	const MEMBER_LV_EARNED_TIME_META_KEY = MemberLvInit::POST_TYPE . '_earned_time';

	/**
	 * 取得客戶訂單資料
	 * 時間參考
	 *
	 * @ref https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 查詢至今幾個月前的訂單
	 * @param array  $args 查詢條件
	 * @param string $transient_key 快取 key
	 * @return array
	 */
	public static function get_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = array(), string $transient_key = '' ): array {
		// get transient
		$key        = self::get_transient_key( $user_id, $months_ago, $transient_key );
		$order_data = \get_transient( $key );
		if ( empty( $order_data ) ) {
			$order_data = self::query_order_data_by_user_date( $user_id, $months_ago, $args, $transient_key );
		}

		return $order_data;
	}

	/**
	 * 查詢客戶訂單資料
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 查詢至今幾個月前的訂單
	 * @param array  $args 查詢條件
	 * @param string $transient_key 快取 key
	 * @return array
	 */
	public static function query_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = array(), string $transient_key = '' ): array {
		$user      = get_userdata( $user_id );
		$that_date = strtotime( 'first day of -' . $months_ago . ' month', time() );
		$that_date = strtotime( 'first day of +1 month', $that_date );

		$user_registed_time = strtotime( $user->data->user_registered );
		$is_registered      = ( $user_registed_time >= $that_date ) ? false : true;

		$month = gmdate( 'm', strtotime( "-{$months_ago} months", time() ) );
		$year  = gmdate( 'Y', strtotime( "-{$months_ago} months", time() ) );

		if ( empty( $args ) ) {
			$args = array(
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user_id,
				'post_type'   => array( 'shop_order' ),
				'post_status' => array( 'wc-completed', 'wc-processing' ),
				'date_query'  => array(
					'year'  => $year,
					'month' => $month,
				),
			);
		}
		$customer_orders = get_posts( $args );
		$total           = 0;
		foreach ( $customer_orders as $customer_order ) {
			$order  = wc_get_order( $customer_order );
			$total += $order->get_total();
		}
		$order_data['total']              = $total; // 金額
		$order_data['order_num']          = count( $customer_orders ); // N 筆訂單
		$order_data['user_is_registered'] = $is_registered; // 是否已註冊

		$key = self::get_transient_key( $user_id, $months_ago, $transient_key );
		\set_transient( $key, $order_data, self::CACHE_TIME );

		return $order_data;
	}

	/**
	 * 取得快取 key
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 查詢至今幾個月前的訂單
	 * @param string $transient_key 快取 key
	 * @return string
	 */
	public static function get_transient_key( int $user_id, int $months_ago, string $transient_key ): string {
		return "order_data_user_id_{$user_id}_months_ago_{ $months_ago}_transient_key_{$transient_key}";
	}
}
