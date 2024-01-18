<?php

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils;

/**
 * 處理會員升級相關邏輯
 * TODO 把累積消費金額存起來? 就不用每次都要算
 */

class MembershipUpgrade
{
	public function __construct()
	{
		\add_action('woocommerce_order_status_completed', [$this, 'membership_check'], 10, 1);
		\add_action('woocommerce_order_status_processing', [$this, 'membership_check'], 10, 1);
		\add_action('trash_' . Utils::MEMBER_LV_POST_TYPE, [$this, 'remove_user_member_lv'], 10, 3);
	}

	public function membership_check($order_id): void
	{
		$order       = new \WC_Order($order_id);
		if (empty($order)) {
			return;
		}
		$customer_id = $order->get_customer_id(); // Or $order->get_user_id();
		if (empty($customer_id)) {
			return;
		}

		$args = array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $customer_id,
			'post_type'   => array('shop_order'),
			'post_status' => array('wc-completed', 'wc-processing'), // TODO 可以做成選單
		);
		// 取得歷史累積金額
		$order_data = Utils::get_order_data_by_user_date($customer_id, 0, $args);
		$acc_amount = (int) $order_data['total'];

		// 取得下個等級的門檻
		$next_rank_id        = \gamipress_get_next_user_rank_id($customer_id, Utils::MEMBER_LV_POST_TYPE);
		$next_rank_threshold = (int) \get_post_meta($next_rank_id, Metabox::THRESHOLD_META_KEY, true);

		if ($acc_amount >= $next_rank_threshold) {
			\update_user_meta($customer_id, Utils::CURRENT_MEMBER_LV_META_KEY, $next_rank_id);
		}
	}

	public function remove_user_member_lv(int $post_id, \WP_Post $post, string $old_status): void
	{
		$meta_key = Utils::CURRENT_MEMBER_LV_META_KEY;
		$meta_value = $post_id;
		$prev_member_id = \gamipress_get_prev_rank_id($post_id);
		// 如果用戶的等級被刪除，則將其等級設為預設等級
		$new_meta_value = empty($prev_member_id) ? Metabox::$default_member_lv_id : $prev_member_id;

		global $wpdb;
		$prefix = $wpdb->prefix;
		$query = $wpdb->prepare(
			"UPDATE {$prefix}usermeta
    SET meta_value = %s
    WHERE meta_key = %s AND meta_value = %s",
			$new_meta_value,
			$meta_key,
			$meta_value
		);

		$result = $wpdb->query($query);
	}
}
