<?php

declare(strict_types=1);

namespace J7\PowerMembership;

abstract class Utils {

	const APP_NAME    = 'Power Membership';
	const KEBAB       = 'power-membership';
	const SNAKE       = 'power_membership';
	const TEXT_DOMAIN = self::SNAKE;

	const DEFAULT_IMAGE       = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';
	const MEMBER_LV_POST_TYPE = 'member_lv';
	const GITHUB_REPO         = 'https://github.com/j7-dev/wp-power-membership';
	const CACHE_TIME          = 24 * HOUR_IN_SECONDS;

	/**
	 * 處理會員升級相關邏輯
	 *     _gamipress_member_lv_rank: 1026 (會員等級的 post id)
	 * _gamipress_member_lv_previous_rank: 1020 (會員等級的 post id)
	 * _gamipress_member_lv_rank_earned_time: 1704704213 (秒)
	 */

	const CURRENT_MEMBER_LV_META_KEY = '_gamipress_' . self::MEMBER_LV_POST_TYPE . '_rank';

	// const ORDER_META_KEY = 'pp_create_site_responses';

	// protected const API_URL            = 'https://cloud.luke.cafe';
	// protected const API_URL = 'http://luke.local';

	// protected const USER_NAME          = 'j7.dev.gg';
	// protected const PASSWORD           = 'YQLj xV2R js9p IWYB VWxp oL2E';
	// protected const TEMPLATE_SERVER_ID = 2202;
	// protected const TRANSIENT_KEY      = 'pp_cloud_sites';

	/**
	 * 取得客戶訂單
	 * 時間參考
	 *
	 * @ref https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
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

	public static function get_transient_key( int $user_id, int $months_ago, string $transient_key ): string {
		return "order_data_user_id_{$user_id}_months_ago_{ $months_ago}_transient_key_{$transient_key}";
	}

	/*
	* 在 wp_admin 中使用do_shortcode
	*/
	public static function admin_do_shortcode( $content, $ignore_html = false ): mixed {
		global $shortcode_tags;

		if (false === strpos($content, '[')) {
			return $content;
		}

		if (empty($shortcode_tags) || !is_array($shortcode_tags)) {
			return $content;
		}

		// Find all registered tag names in $content.
		preg_match_all('@\[([^<>&/\[\]\x00-\x20=]++)@', $content, $matches);
		$tagnames = array_intersect(array_keys($shortcode_tags), $matches[1]);

		if (empty($tagnames)) {
			return $content;
		}

		$content = do_shortcodes_in_html_tags($content, $ignore_html, $tagnames);

		$pattern = get_shortcode_regex($tagnames);
		$content = preg_replace_callback("/$pattern/", 'do_shortcode_tag', $content);

		// Always restore square braces so we don't break things like <!--[if IE ]>.
		$content = unescape_invalid_shortcodes($content);

		return $content;
	}

	public static function get_plugin_dir(): string {
		$plugin_dir = \untrailingslashit(\wp_normalize_path(\plugin_dir_path(__DIR__ . '../')));
		return $plugin_dir;
	}

	public static function get_plugin_url(): string {
		$plugin_url = \untrailingslashit(\plugin_dir_url(self::get_plugin_dir() . '/plugin.php'));
		return $plugin_url;
	}

	public static function get_plugin_ver(): string {
		$plugin_data = \get_plugin_data(self::get_plugin_dir() . '/plugin.php');
		$plugin_ver  = $plugin_data['Version'];
		return $plugin_ver;
	}
}
