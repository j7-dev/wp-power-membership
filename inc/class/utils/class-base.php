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
	const BASE_URL      = '/';
	const APP1_SELECTOR = '#power_membership_logs';
	const API_TIMEOUT   = '30000';
	const DEFAULT_IMAGE = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';
	const CACHE_TIME    = 24 * HOUR_IN_SECONDS;

	/**
	 * 取得客戶訂單資料
	 * 時間參考
	 *
	 * @deprecated 未來應該可以統一用 timestamp 就好
	 *
	 * @ref https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 查詢至今幾個月前的訂單
	 * @param array  $args 查詢條件
	 * @param string $transient_key 快取 key
	 * @return array
	 */
	public static function get_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = [], string $transient_key = '' ): array {
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
	 * @deprecated 未來應該可以統一用 timestamp 就好
	 *
	 * @param int    $user_id 用戶 ID
	 * @param int    $months_ago 查詢至今幾個月前的訂單
	 * @param array  $args 查詢條件
	 * @param string $transient_key 快取 key
	 * @return array
	 */
	public static function query_order_data_by_user_date( int $user_id, int $months_ago = 0, array $args = [], string $transient_key = '' ): array {
		$user      = get_userdata( $user_id );
		$that_date = strtotime( 'first day of -' . $months_ago . ' month', time() );
		$that_date = strtotime( 'first day of +1 month', $that_date );

		$user_registed_time = strtotime( $user->data->user_registered );
		$is_registered      = ( $user_registed_time >= $that_date ) ? false : true;

		$month = gmdate( 'm', strtotime( "-{$months_ago} months", time() ) );
		$year  = gmdate( 'Y', strtotime( "-{$months_ago} months", time() ) );

		if ( empty( $args ) ) {
			$args = [
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user_id,
				'post_type'   => [ 'shop_order' ],
				'post_status' => [ 'wc-completed', 'wc-processing' ],
				'date_query'  => [
					'year'  => $year,
					'month' => $month,
				],
			];
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
	 * 根據用戶ID和指定的時間戳獲取訂單數據。
	 *
	 * 此函數首先嘗試從暫存中獲取訂單數據。如果暫存中沒有數據，
	 * 則會調用 `query_order_data_by_timestamp` 函數從數據庫中查詢數據。
	 *
	 * @param int         $user_id 用戶ID。
	 * @param int         $timestamp 指定的時間戳，用於查詢訂單數據。
	 * @param string|null $begin 指定查詢的起始時間範圍，可為'day'或'month'，或為null。
	 * @return array 返回包含訂單數據的數組。
	 */
	public static function get_order_data_by_timestamp( int $user_id, int $timestamp = 0, ?string $begin = null ): array {
		// get transient
		$key        = self::get_transient_key($user_id, $timestamp, "_timestamp__begin_{$begin}");
		$order_data = \get_transient($key);
		if (empty($order_data)) {
			$order_data = self::query_order_data_by_timestamp($user_id, $timestamp, $begin);
		}

		return $order_data;
	}

	/**
	 * 根據用戶ID和時間戳獲取訂單數據。
	 *
	 * @param int         $user_id 用戶ID。
	 * @param int         $timestamp 指定的時間戳，用於查詢訂單數據。
	 * @param string|null $begin 指定查詢的起始時間範圍，可為'day'或'month'，或為null。
	 * @param array|null  $args 額外的查詢參數。
	 * @return array 返回包含訂單數據的數組。
	 */
	public static function query_order_data_by_timestamp( int $user_id, int $timestamp, ?string $begin = null, ?array $args = [] ): array {
		global $wpdb;
		$user = \get_userdata($user_id);

		if ('day' === $begin) {
			$timestamp = strtotime('today', $timestamp);
		} elseif ('month' === $begin) {
			$timestamp = strtotime('first day of this month', $timestamp);
		}

		$user_registed_time = strtotime($user->data->user_registered);
		$is_registered      = ( $user_registed_time >= $timestamp ) ? false : true;

		$prepare = $wpdb->prepare(
			"SELECT
					SUM(pm.meta_value) as total_amount,
					COUNT(DISTINCT p.ID) as order_count
			FROM %1\$s as p
			JOIN %2\$s as pm ON p.ID = pm.post_id
			JOIN %2\$s as pm2 ON p.ID = pm2.post_id
			WHERE p.post_type = 'shop_order'
			AND p.post_status IN ('wc-completed', 'wc-processing')
			%3\$s
			AND pm.meta_key = '_order_total'
			AND pm2.meta_key = '_customer_user'
			AND pm2.meta_value = %4\$d",
			"{$wpdb->prefix}posts",
		"{$wpdb->prefix}postmeta",
			$timestamp ? "AND UNIX_TIMESTAMP(p.post_date) >= {$timestamp}" : '',
			$user_id
		);

		$result = $wpdb->get_row($prepare);

		$order_data['total']              = $result->total_amount; // 金額
		$order_data['order_num']          = $result->order_count; // N 筆訂單
		$order_data['user_is_registered'] = $is_registered; // 是否已註冊

		$key = self::get_transient_key($user_id, $timestamp, "_timestamp__begin_{$begin}");
		\set_transient($key, $order_data, self::CACHE_TIME);

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


	/**
	 * 取得特定月份生日的用戶 IDs
	 *
	 * @param ?string $month 月份 (01 ~ 12) (預設為當月)
	 * @return array int[]
	 */
	public static function get_user_ids_by_bday_month( ?string $month = '' ): array {
		global $wpdb;

		if ( ! $month ) {
			$month = gmdate( 'm', time() + 8 * 3600 );
		}

		$records = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'birthday' AND meta_value LIKE %s",
				'%' . $month . '%'
			),
			ARRAY_A
		);
		if ( ! $records ) {
			return [];
		}

		$user_ids = [];
		foreach ( $records as $record ) {
			$user_ids[] = (int) $record['user_id'];
		}

		return $user_ids;
	}

	/**
	 * 計算基於指定的排名限制類型和值來獲取時間戳。
	 *
	 * 此函數根據排名限制類型（固定或指定）來計算時間戳。
	 * - 如果類型為 'fixed'，則基於當前時間往回計算指定的時間單位和值。
	 * - 如果類型為 'assigned'，則將指定的日期（格式為 'Y-m-d'）轉換為時間戳。
	 *
	 * @param string $next_rank_limit_type 排名限制類型，可為 'fixed' 或 'assigned'。
	 * @param mixed  $next_rank_limit_value 排名限制的值，如果類型為 'fixed'，則為數字；如果類型為 'assigned'，則為日期字符串。
	 * @param string $next_rank_limit_unit 排名限制的時間單位，僅當類型為 'fixed' 時使用。
	 * @return int|false 返回計算後的時間戳。 如果是 unlimited 就會是 0
	 */
	public static function calc_timestamp( $next_rank_limit_type, $next_rank_limit_value, $next_rank_limit_unit ): int|bool {
		$calc_timestamp = 0;
		if ('fixed' === $next_rank_limit_type) {
			/**
			 * $next_rank_limit_value = 10
			 * $next_rank_limit_unit = 'month'
			 */
			$calc_timestamp = strtotime("-{$next_rank_limit_value} {$next_rank_limit_unit}");
		} elseif ('assigned' === $next_rank_limit_type) {
			// convert 'Y-m-d' to 'timestamp'
			$calc_timestamp = strtotime( (string) $next_rank_limit_value);
		}
		return $calc_timestamp;
	}
}
