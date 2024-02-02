<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;
use J7\PowerMembership\Admin\Menu\Settings;
use J7\PowerMembership\WooCommerce\Coupons\Metabox;

final class View
{

	public $further_coupons = [];

	public function __construct()
	{
		global $power_membership_settings;

		if ($power_membership_settings[Settings::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME]) {
			\add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
			\add_action('woocommerce_before_checkout_form', [$this, 'show_available_coupons'], 10, 1);
			\add_filter('woocommerce_coupon_validate_minimum_amount', [$this, 'modify_minimum_amount_condition'], 200, 3);
			\add_filter('woocommerce_coupon_is_valid', [$this, 'custom_condition'], 200, 3);
		}

		if (!$power_membership_settings[Settings::ENABLE_SHOW_COUPON_FORM_FIELD_NAME]) {
			\add_action('init', [$this, 'remove_wc_coupon_form'], 20);
		}

		// \add_action('woocommerce_cart_calculate_fees', [$this, 'first_purchase_coupon']);
	}

	public function remove_wc_coupon_form(): void
	{
		\remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
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
		$coupons = $this->get_valid_coupons(); //取得網站一般優惠
		if (!empty($coupons)) :
			global $power_membership_settings;
			// var_dump($power_membership_settings);
			$coupons = $this->sort_coupons($coupons);
		?>
			<div class="power-coupon">
				<h2 class="">消費滿額折扣</h2>
				<div class="mb-2 py-2">
					<?php foreach ($coupons as $coupon) {
						$props = $this->get_coupon_props($coupon);
						\load_template(__DIR__ . '/templates/basic.php', false, [
							'coupon' => $coupon,
							'props' => $props,
						]);
					}
					?>
				</div>
			</div>
		<?php endif; ?>
<?php
	}

	public function get_valid_coupons(): array
	{
		$coupon_ids = get_posts(array(
			'posts_per_page' => -1,
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'OR',
				[
					'key'     => Metabox::HIDE_THIS_COUPON_FIELD_NAME,
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => Metabox::HIDE_THIS_COUPON_FIELD_NAME,
					'value'   => 'yes',
					'compare' => '!=',
				],
			),
		)) ?? [];

		$coupons    = array_map(function ($coupon_id) {
			return new \WC_Coupon($coupon_id);
		}, $coupon_ids);

		$discounts = new \WC_Discounts(WC()->cart);

		foreach ($coupons as $key => $coupon) {
			$valid = $discounts->is_coupon_valid($coupon);
			if (is_wp_error($valid)) {
				unset($coupons[$key]);
			}
		}

		return $coupons;
	}

	public function custom_condition(bool $is_valid, \WC_Coupon $coupon, \WC_Discounts $discounts): bool
	{
		$condition_by_membership_ids = $this->filter_condition_by_membership_ids($coupon);
		$condition_by_first_purchase = $this->filter_condition_by_first_purchase($coupon);
		$condition_by_min_quantity = $this->filter_condition_by_min_quantity($coupon);

		return $condition_by_membership_ids && $condition_by_first_purchase && $condition_by_min_quantity && $is_valid;
	}

	private function filter_condition_by_membership_ids(\WC_Coupon $coupon): bool
	{
		// 或是 allowed_membership_ids 包含 user 的 membership id 的 coupon
		$allowed_membership_ids = $coupon->get_meta(Metabox::ALLOWED_MEMBER_LV_FIELD_NAME);
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

	private function filter_condition_by_min_quantity(\WC_Coupon $coupon): bool
	{
		$min_quantity = (int) $coupon->get_meta(Metabox::MIN_QUANTITY_FIELD_NAME);
		if (!empty($min_quantity)) {
			$cart = \WC()->cart;
			$cart_item_quantities = (int) array_sum($cart->get_cart_item_quantities());
			return $cart_item_quantities >= $min_quantity;
		}

		return true;
	}

	/**
	 * 隱藏小的coupon
	 * 只出現大的coupon
	 */
	public function sort_coupons(array $available_coupons): array
	{
		global $power_membership_settings;



		$further_coupons 	 = $this->further_coupons;

		usort($available_coupons, function ($a, $b) {
			return (int) $this->get_coupon_amount($b) - (int) $this->get_coupon_amount($a);
		});
		usort($further_coupons, function ($a, $b) {
			return (int) $a->get_minimum_amount() - (int) $a->get_minimum_amount();
		});

		// 只保留前 N 個 further_coupons
		$show_further_coupons_qty = $power_membership_settings[Settings::SHOW_FURTHER_COUPONS_QTY_FIELD_NAME] ?? 3;
		$sliced_further_coupons = array_slice($further_coupons, 0, $show_further_coupons_qty);

		// 如果啟用只顯示最大折扣券
		if ($power_membership_settings[Settings::ENABLE_BIGGEST_COUPON_FIELD_NAME]) {
			$result = array_merge([$available_coupons[0]], $sliced_further_coupons);
		} else {
			$result = array_merge($available_coupons, $sliced_further_coupons);
		}
		return $result;
	}

	public function get_coupon_amount(\WC_Coupon $coupon): int
	{
		if ($coupon->is_type(array('percent'))) {
			$cart = WC()->cart;
			return (int) $coupon->get_amount() * (int) $cart->subtotal / 100;
		}
		return (int) $coupon->get_amount();
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

	public function modify_minimum_amount_condition($not_valid, $coupon, $subtotal): bool
	{
		global $power_membership_settings;

		if ($power_membership_settings[Settings::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME] && $not_valid) {
			$this->further_coupons[] = $coupon;
		}

		return $not_valid;
	}


	// public function add_fee(\WC_Cart $cart): void
	// {
	// 	$cart->add_fee(__("首次消費折 {$discount} 元", Utils::TEXT_DOMAIN), -$discount);
	// }
}
