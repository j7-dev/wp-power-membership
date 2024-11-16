<?php
/**
 * Base
 */

declare (strict_types = 1);

namespace J7\PowerMembership\Utils;

if (class_exists('J7\PowerMembership\Utils\Base')) {
	return;
}
/**
 * Class Base
 */
abstract class Base {
	const BASE_URL      = '/';
	const APP1_SELECTOR = '#power_membership';
	const APP2_SELECTOR = '#power_membership_metabox';
	const API_TIMEOUT   = '30000';
	const DEFAULT_IMAGE = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';

	const MEMBER_LV_POST_TYPE = 'member_lv';
	const CACHE_TIME          = 24 * HOUR_IN_SECONDS;

	/**
		 * 處理會員升級相關邏輯
		 *     _gamipress_member_lv_rank: 1026 (會員等級的 post id)
		 * _gamipress_member_lv_previous_rank: 1020 (會員等級的 post id)
		 * _gamipress_member_lv_rank_earned_time: 1704704213 (秒)
		 */

	const CURRENT_MEMBER_LV_META_KEY = '_gamipress_' . self::MEMBER_LV_POST_TYPE . '_rank';


	/**
	 * 取得客戶訂單
	 * 時間參考
	 *
	 * @see https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 過去幾個月
	 * @param array  $args 參數
	 * @param string $transient_key 暫存鍵
	 * @return array
	 */
	public static function get_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = [], string $transient_key = '' ): array {
		// get transient
		$key        = self::get_transient_key($user_id, $months_ago, $transient_key);
		$order_data = \get_transient($key);
		if (empty($order_data)) {
			$order_data = self::query_order_data_by_user_date($user_id, $months_ago, $args, $transient_key);
		}

		return $order_data;
	}

	/**
	 * 查詢用戶訂單數據
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 過去幾個月
	 * @param array  $args 參數
	 * @param string $transient_key 暫存鍵
	 * @return array
	 */
	public static function query_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = [], string $transient_key = '' ): array {
		$user       = get_userdata($user_id);
		$that_date  = strtotime('first day of -' . $months_ago . ' month', time());
		$start_date = \wp_date('Y-m-d', $that_date);
		$end_date   = \wp_date('Y-m-d');

		$user_registed_time = strtotime($user->data->user_registered);
		$is_registered      = ( $user_registed_time >= $that_date ) ? false : true;

		if (empty($args)) {
			$args = [
				'limit'       => -1,
				'customer_id' => $user_id,
				'status'      => [ 'wc-completed', 'wc-processing', 'wc-withdrawal-paid' ],
				'date_paid'   => "{$start_date}...{$end_date}", // YYYY-MM-DD...YYYY-MM-DD
			];
		}

		$customer_orders = \wc_get_orders($args);
		$total           = 0;
		foreach ($customer_orders as $customer_order) {
			$order  = wc_get_order($customer_order);
			$total += $order->get_total();
		}
		$order_data['total']              = $total; // 金額
		$order_data['order_num']          = count($customer_orders); // N 筆訂單
		$order_data['user_is_registered'] = $is_registered; // 是否已註冊

		$key = self::get_transient_key($user_id, $months_ago, $transient_key);
		\set_transient($key, $order_data, self::CACHE_TIME);

		return $order_data;
	}

	/**
	 * 取得暫存鍵
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 過去幾個月
	 * @param string $transient_key 暫存鍵
	 * @return string
	 */
	public static function get_transient_key( int $user_id, int $months_ago, string $transient_key ): string {
		return "order_data_user_id_{$user_id}_months_ago_{ $months_ago}_transient_key_{$transient_key}";
	}
}
