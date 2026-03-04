<?php
/**
 * 會員等級折扣
 */

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils\Base;

/**
 * 會員等級折扣
 *
 * 根據當前登入用戶所屬的會員等級，在 WooCommerce 購物車計算費用時套用對應的折扣。
 */
final class MemberDiscount {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 建構子
	 */
	public function __construct() {
		\add_action('woocommerce_cart_calculate_fees', [ $this, 'apply_member_discount' ]);
	}

	/**
	 * 套用會員等級折扣
	 *
	 * @param \WC_Cart $cart WooCommerce 購物車物件
	 * @return void
	 */
	public function apply_member_discount( \WC_Cart $cart ): void {
		if (\is_admin() && !defined('DOING_AJAX')) {
			return;
		}

		$user_id = \get_current_user_id();
		if (!$user_id) {
			return;
		}

		$member_lv_id = (int) \get_user_meta($user_id, Base::CURRENT_MEMBER_LV_META_KEY, true);
		if (!$member_lv_id) {
			return;
		}

		$discount_percent = (int) \get_post_meta($member_lv_id, Metabox::DISCOUNT_META_KEY, true);
		if (!$discount_percent) {
			return;
		}

		$cart_total      = (float) $cart->get_subtotal();
		$discount_amount = \round($cart_total * ($discount_percent / 100) * -1);

		$cart->add_fee('會員折扣', $discount_amount);
	}
}

MemberDiscount::instance();
