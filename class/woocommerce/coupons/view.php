<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;

class View
{
	public $show_one_coupon_only = true; // 隱藏小的 coupons，只顯示一個金額較大的 coupon

	public function __construct()
	{
		\add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
		\add_action('woocommerce_before_checkout_form', [$this, 'show_available_coupons'], 10, 1);
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
			$coupons = $this->handle_show_one_coupon_only($coupons);
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

	public function get_coupons()
	{

		$coupon_ids_without_minimum_amount = get_posts(array(
			'posts_per_page' => -1,
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'meta_key'       => 'minimum_amount',
			'meta_compare'   => 'NOT EXISTS',
			'fields'         => 'ids',
		));

		$coupon_ids_with_minimum_amount = get_posts(array(
			'posts_per_page' => -1,
			'meta_key'       => 'minimum_amount',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'meta_key'       => 'minimum_amount',
			'meta_compare'   => 'EXISTS',
			'fields'         => 'ids',
		));

		$coupon_ids = array_merge($coupon_ids_without_minimum_amount, $coupon_ids_with_minimum_amount);
		$coupons    = array_map(function ($coupon_id) {
			return new \WC_Coupon($coupon_id);
		}, $coupon_ids);
		return $coupons;
	}

	/**
	 * 隱藏小的coupon
	 * 只出現大的coupon
	 */
	public function handle_show_one_coupon_only($coupons)
	{
		if (!$this->show_one_coupon_only) {
			return $coupons;
		}
		$cart_total = (int) WC()->cart->subtotal;

		// get coupon amount

		foreach ($coupons as $key => $coupon) {
			$minimum_amount = (int) $coupon->get_minimum_amount();
			// $minimum_amount = !empty($minimum_amount) ? $minimum_amount : 0;
			if ($cart_total >= $minimum_amount) {
				$meet[$coupon->get_id()] = abs($cart_total - $minimum_amount);
			} else {
				$not_meet[$coupon->get_id()] = abs($cart_total - $minimum_amount);
			}
		}
		$meet     = !empty($meet) ? $meet : [];
		$not_meet = !empty($not_meet) ? $not_meet : [];
		asort($meet); //from small to big
		asort($not_meet); // from small to big

		$biggest_coupon = array_slice($meet, 0, 1, true);
		$keys           = array_keys($biggest_coupon + $not_meet);
		foreach ($coupons as $key => $coupon) {
			if (!in_array($coupon->get_id(), $keys)) {
				unset($coupons[$key]);
			}
		}

		return $coupons;
	}

	public function get_coupon_props($coupon)
	{
		$cart_total     = (int) WC()->cart->subtotal;
		$coupon_amount  = (int) $coupon->get_amount();
		$minimum_amount = (int) $coupon->get_minimum_amount();

		$props = [];
		if ($cart_total < $minimum_amount) {

			$d                       = $minimum_amount - $cart_total;
			$shop_url                = site_url('shop');
			$props['is_available'] = false;
			$props['reason']       = "，<span class='text-danger'>還差 ${d} 元</span>，<a href='${shop_url}'>再去多買幾件 》</a>";
			$props['disabled']     = "disabled";
			$props['disabled_bg']  = "bg-light cursor-not-allowed";
			return $props;
		} else {
			$props['is_available'] = true;
			$props['reason']       = "";
			$props['disabled']     = "";
			$props['disabled_bg']  = "";
			return $props;
		}
	}
}

new View();
