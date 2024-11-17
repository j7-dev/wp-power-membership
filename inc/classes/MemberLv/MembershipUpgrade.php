<?php
/**
 * 處理會員升級相關邏輯
 */

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils\Base;

/**
 * 處理會員升級相關邏輯
 */
final class MembershipUpgrade {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 建構子
	 */
	public function __construct() {
		\add_action('woocommerce_order_status_changed', [ __CLASS__, 'membership_check' ], 10, 3);
		\add_action('trash_' . Base::MEMBER_LV_POST_TYPE, [ __CLASS__, 'remove_user_member_lv' ], 10, 3);
	}

	/**
	 * 會員升級檢查
	 *
	 * @param int    $order_id 訂單 ID
	 * @param string $from 從
	 * @param string $to 到
	 */
	public static function membership_check( int $order_id, string $from, string $to ): void {
		$order = \wc_get_order($order_id);
		if (empty($order)) {
			return;
		}
		$customer_id = $order->get_customer_id(); // Or $order->get_user_id();
		if (empty($customer_id)) {
			return;
		}

		if (in_array($from, [ 'completed', 'processing', 'withdrawal-paid' ], true)) {
			return;
		}

		if (!in_array($to, [ 'completed', 'processing', 'withdrawal-paid' ], true)) {
			return;
		}

		// 取得最近12個月累積金額
		$order_data = Base::query_order_data_by_user_date($customer_id, 12);
		$acc_amount = (int) $order_data['total'];

		self::handle_upgrade($customer_id, $acc_amount);
	}

	/**
	 * 處理升級
	 *
	 * @param int $customer_id 用戶 ID
	 * @param int $acc_amount 累積金額
	 * @return void
	 */
	public static function handle_upgrade( int $customer_id, $acc_amount ): void {
		$all_ranks = self::get_all_rank_threshold();

		foreach ($all_ranks as $rank_id => $threshold) {
			if ($acc_amount >= $threshold) {
				\gamipress_award_rank_to_user($rank_id, $customer_id);
				break;
			}
		}
	}

	/**
	 * 取得所有等級的門檻
	 *
	 * @return array<int, int> ID => threshold
	 */
	public static function get_all_rank_threshold(): array {
		$ranks = \gamipress_get_ranks(
			[
				'post_status' => 'publish',
			]
			);

		$formatted_ranks = [];
		foreach ($ranks as $rank) {
			$formatted_ranks[ $rank->ID ] = (int) \get_post_meta($rank->ID, Metabox::THRESHOLD_META_KEY, true);
		}

		\arsort($formatted_ranks);

		return $formatted_ranks;
	}

	/**
	 * 刪除用戶會員等級
	 *
	 * @param int      $post_id 文章 ID
	 * @param \WP_Post $post 文章
	 * @param string   $old_status 舊狀態
	 */
	public static function remove_user_member_lv( int $post_id, \WP_Post $post, string $old_status ): void {
		$meta_key       = Base::CURRENT_MEMBER_LV_META_KEY;
		$meta_value     = $post_id;
		$prev_member_id = \gamipress_get_prev_rank_id($post_id);
		// 如果用戶的等級被刪除，則將其等級設為預設等級
		$new_meta_value = empty($prev_member_id) ? Metabox::$default_member_lv_id : $prev_member_id;

		// phpcs:disable
		global $wpdb;
		$prefix = $wpdb->prefix;
		$query  = $wpdb->prepare(
			"UPDATE {$prefix}usermeta
    SET meta_value = %s
    WHERE meta_key = %s AND meta_value = %s",
			$new_meta_value,
			$meta_key,
			$meta_value
		);

		$result = $wpdb->query($query);
		// phpcs:enable
	}
}

MembershipUpgrade::instance();
