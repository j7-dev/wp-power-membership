<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;
use J7\PowerMembership\Admin\Menu\Settings;

final class View
{

	public function __construct()
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		\add_action('woocommerce_before_checkout_form', [$this, 'show_available_coupons'], 10, 1);
		// \add_action('woocommerce_cart_calculate_fees', [$this, 'first_purchase_coupon']);
	}

	public function enqueue_assets(): void
	{
		if (\is_checkout()) {
			\wp_enqueue_style('dashicons');
			\wp_enqueue_script('handle-coupon', Utils::get_plugin_url() . '/assets/js/handle-coupon.js', array('wc-checkout'), Utils::get_plugin_ver(), true);
		}
	}

	public function show_available_coupons($checkout): void
	{
?>
		<script src="https://cdn.tailwindcss.com"></script>

		<?php
		$coupons = $this->get_coupons(); //取得網站一般優惠
		if (!empty($coupons)) :
			global $power_membership_settings;
			// var_dump($power_membership_settings);
			if ($power_membership_settings[Settings::ENABLE_BIGGEST_COUPON_FIELD_NAME]) {
				$coupons = $this->filter_by_biggest_coupon_only($coupons);
			}
		?>
			<div class="power-coupon">
				<h2 class="">消費滿額折扣</h2>
				<div class="mb-2 py-2">
					<?php foreach ($coupons as $coupon) :
						$props = $this->get_coupon_props($coupon);
					?>
						<label class="block px-2 py-1 <?= $props['disabled_bg'] ?>">

							<input data-type="normal_coupon" id="coupon-<?= $coupon->get_id(); ?>" name="yf_normal_coupon" class="mr-2 normal_coupon" type="radio" value="<?= $coupon->get_code(); ?>" <?= $props['disabled'] ?>>
							<span class="dashicons dashicons-tag <?= $props['disabled'] === 'disabled' ? 'text-gray-400' : 'text-red-400' ?>"></span>
							<?= $coupon->get_code() . $coupon->get_description() . $props['reason']; ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
<?php
	}

	public function get_coupons(): array
	{

		$coupon_ids_without_minimum_amount = get_posts(array(
			'posts_per_page' => -1,
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_key'       => 'minimum_amount',
			'meta_compare'   => 'NOT EXISTS',
		)) ?? [];

		$coupon_ids_with_minimum_amount = get_posts(array(
			'posts_per_page' => -1,
			'meta_key'       => 'minimum_amount',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_key'       => 'minimum_amount',
			'meta_compare'   => 'EXISTS',
		)) ?? [];

		$coupon_ids = array_merge($coupon_ids_without_minimum_amount, $coupon_ids_with_minimum_amount);
		$coupons    = array_map(function ($coupon_id) {
			return new \WC_Coupon($coupon_id);
		}, $coupon_ids);

		// Filter 出 allowed_membership_ids 是 [] 的 coupon (沒有限制)
		// 或是 allowed_membership_ids 包含 user 的 membership id 的 coupon
		$coupons = array_filter($coupons, function (\WC_Coupon $coupon): bool {
			$condition_by_membership_ids = $this->filter_condition_by_membership_ids($coupon);
			$condition_by_first_purchase = $this->filter_condition_by_first_purchase($coupon);

			return $condition_by_membership_ids && $condition_by_first_purchase;
		});

		return $coupons;
	}

	private function filter_condition_by_membership_ids(\WC_Coupon $coupon): bool
	{
		// 或是 allowed_membership_ids 包含 user 的 membership id 的 coupon
		$allowed_membership_ids = $coupon->get_meta(Metabox::SELECT_FIELD_NAME);
		$allowed_membership_ids = is_array($allowed_membership_ids) ? $allowed_membership_ids : [];
		$user_id                = \get_current_user_id();
		$user_member_lv_id      = \gamipress_get_user_rank_id($user_id, Utils::MEMBER_LV_POST_TYPE);
		if (in_array($user_member_lv_id, $allowed_membership_ids)) {
			return true;
		}

		// Filter 出 allowed_membership_ids 是 [] 的 coupon (沒有限制)
		return empty($allowed_membership_ids);
	}

	private function filter_condition_by_first_purchase(\WC_Coupon $coupon): bool
	{
		$value = $coupon->get_meta(Metabox::FIRST_PURCHASE_COUPON_FIELD_NAME);
		if ("yes" === $value) {
			return $this->is_first_purchase();
		}

		return true;
	}

	/**
	 * 隱藏小的coupon
	 * 只出現大的coupon
	 */
	public function filter_by_biggest_coupon_only(array $coupons): array
	{
		global $power_membership_settings;
		$cart_total = (int) WC()->cart->subtotal;

		$available_coupons 	 = [];
		foreach ($coupons as $key => $coupon) {
			$coupon_id = $coupon->get_id();
			$minimum_amount = (int) $coupon->get_minimum_amount();
			if ($cart_total >= $minimum_amount) {
				if ($coupon->is_valid_for_cart()) {
					$available_coupons[$coupon_id] = $coupon;
				}
			} else {
				$further_coupons[$coupon_id] = abs($cart_total - $minimum_amount);
			}
		}

		// 如果啟用顯示 further coupons
		if ($power_membership_settings[Settings::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME]) {
			$further_coupons = !empty($further_coupons) ? $further_coupons : [];
			asort($further_coupons); // from small to big
		} else {
			$further_coupons = [];
		}

		// 如果啟用只顯示最大折扣券
		if ($power_membership_settings[Settings::ENABLE_BIGGEST_COUPON_FIELD_NAME]) {
			$biggest_coupon = $this->get_biggest_coupon($available_coupons);
			$keys           = empty($biggest_coupon) ? array_keys($further_coupons) : [$biggest_coupon->get_id(), ...array_keys($further_coupons)];
		} else {
			$keys           = array_keys($available_coupons);
		}

		foreach ($coupons as $key => $coupon) {
			$coupon_id = $coupon->get_id();
			if (!in_array($coupon_id, $keys)) {
				unset($coupons[$key]);
			}
		}

		return $coupons;
	}


	public function get_biggest_coupon(array $coupons): ?\WC_Coupon
	{
		// 初始化最大折扣金额
		$max_discount_amount = 0;
		// 初始化最大折扣券对象
		$max_discount_coupon = null;

		// 遍历优惠券数组
		foreach ($coupons as $coupon) {
			// 获取折扣金额
			$discount_amount = $coupon->get_amount();

			// 检查是否是固定折扣券或百分比折扣券
			// 这里简单地根据折扣金额的正负来判断，你可能需要根据实际情况调整
			if ($discount_amount > $max_discount_amount) {
				// 更新最大折扣金额和对应的折扣券对象
				$max_discount_amount = $discount_amount;
				$max_discount_coupon = $coupon;
			}
		}

		return $max_discount_coupon;
	}

	public function get_coupon_props(\WC_Coupon $coupon): array
	{
		if (empty($coupon)) {
			return [];
		}
		$cart_total     = (int) WC()->cart->subtotal;
		$coupon_amount  = (int) $coupon->get_amount();
		$minimum_amount = (int) $coupon->get_minimum_amount();

		$props = [];
		if ($cart_total < $minimum_amount) {

			$d                       = $minimum_amount - $cart_total;
			$shop_url                = site_url('shop');
			$props['is_available'] = false;
			$props['reason']       = "，<span class='text-red-400'>還差 ${d} 元</span>，<a href='${shop_url}'>再去多買幾件 》</a>";
			$props['disabled']     = "disabled";
			$props['disabled_bg']  = "bg-gray-100 cursor-not-allowed";
			return $props;
		} else {
			$props['is_available'] = true;
			$props['reason']       = "";
			$props['disabled']     = "";
			$props['disabled_bg']  = "";
			return $props;
		}
	}

	public static function get_order_quantity_by_user(int $user_id = 0): int
	{
		if (empty($user_id)) {
			$user_id   = \get_current_user_id();
		}
		global $wpdb;
		$query = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status IN ('wc-completed', 'wc-processing') AND post_author = %d", $user_id);

		$order_count = (int) $wpdb->get_var($query);

		return $order_count;
	}

	private function is_first_purchase(int $user_id = 0): bool
	{
		if (empty($user_id)) {
			$user_id   = \get_current_user_id();
		}
		$count = self::get_order_quantity_by_user($user_id);

		return $count === 0;
	}


	// public function add_fee(\WC_Cart $cart): void
	// {
	// 	$cart->add_fee(__("首次消費折 {$discount} 元", Utils::TEXT_DOMAIN), -$discount);
	// }
}
