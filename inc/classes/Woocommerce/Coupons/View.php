<?php
/**
 * 優惠券 View
 */

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Admin\Menu\Settings;
use J7\PowerMembership\WooCommerce\Coupons\Metabox;
use J7\PowerMembership\Plugin;


/**
 * 優惠券 View
 */
final class View {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 進一步的優惠券
	 *
	 * @var array
	 */
	public $further_coupons = [];

	/**
	 * 建構子
	 */
	public function __construct() {
		\add_action('setup_theme', [ $this, 'init' ], 110);
	}

	/**
	 * 初始化
	 */
	public function init(): void {
		global $power_plugins_settings;
		\add_action('woocommerce_before_checkout_form', [ $this, 'show_award_deduct' ], 20, 1);

		if ($power_plugins_settings[ Settings::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME ] ?? false) {
			\add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
			\add_action('woocommerce_before_checkout_form', [ $this, 'show_available_coupons' ], 10, 1);
			\add_filter('woocommerce_coupon_validate_minimum_amount', [ $this, 'modify_minimum_amount_condition' ], 200, 3);
			\add_filter('woocommerce_coupon_is_valid', [ $this, 'custom_condition' ], 200, 3);
		}

		if (!( $power_plugins_settings[ Settings::ENABLE_SHOW_COUPON_FORM_FIELD_NAME ] ?? false )) {
			\add_action('init', [ $this, 'remove_wc_coupon_form' ], 20);
		}

		\add_action('wp_ajax_award_deduct_point', [ $this, 'award_deduct_point' ]);
		\add_action('woocommerce_cart_calculate_fees', [ $this, 'add_custom_fee' ]);
		\add_action('woocommerce_checkout_order_created', [ $this, 'exec_deduct_point' ]);
		\add_action('woocommerce_cart_emptied', [ $this, 'clear_cart_and_session' ]);
		\add_action('init', [ $this, 'clear_fee' ]);
		// 訂單取消時，歸還購物金；更新: 折抵的購物金不退
		// \add_action('woocommerce_order_status_cancelled', [ $this, 'restore_award_deduct_point' ]);
	}

	/**
	 * 移除 WooCommerce 優惠券表單
	 */
	public function remove_wc_coupon_form(): void {
		\remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
	}

	/**
	 * 添加資產
	 */
	public function enqueue_assets(): void {
		if (\is_checkout()) {
			\wp_enqueue_style('dashicons');
			\wp_enqueue_style('handle-coupon', Plugin::$url . '/assets/css/front.min.css', [], Plugin::$version);

			\wp_enqueue_script( 'jquery-blockui' );
			\wp_enqueue_script('handle-coupon', Plugin::$url . '/assets/js/handle-coupon.js', [ 'wc-checkout' ], Plugin::$version, true);
		}
	}

	/**
	 * 顯示購物金折抵
	 *
	 * @param \WC_Checkout $checkout 結帳頁面
	 */
	public function show_award_deduct( $checkout ): void {

		$custom_fee  = \WC()->session->get('custom_fee');
		$current_fee = $custom_fee ? (int) $custom_fee['amount'] : 0;

		$user_point       = \gamipress_get_user_points(\get_current_user_id(), 'ee_point');
		$user_point_price = \wc_price($user_point + $current_fee);
		$sub_total        = (int) WC()->cart->subtotal;

		$coupons = $this->get_valid_award_deduct_coupons(); // 取得購物車折抵優惠
		if (empty($coupons)) {
			return;
		}

		echo '<div class="mb-2 py-2">';
		foreach ($coupons as $coupon) {
			$deduct_ratio      = $coupon->get_amount() / 100;
			$max_deduct_amount = \floor($sub_total * $deduct_ratio);

			printf(
				/*html*/'<p class="mb-0">購物金折抵，最高可以折抵購物車金額 %1$s %% 即 %2$s 元，您目前有 <span id="user-point" >%3$s</span> 元購物金</p>',
				$coupon->get_amount(),
				\wc_price($max_deduct_amount),
				$user_point_price
			);

			$name = 'award_deduct_point';
			printf(
				/*html*/'
					<input type="number" class="input-text inline-block w-40" name="%1$s" id="%1$s" placeholder="" value="">
					<button id="%1$s-apply" data-coupon_id="%2$s" data-user_point="%3$d" type="button" class="button">折抵</button>
				',
				$name,
				$coupon->get_id(),
				$user_point,
			);

		}
		echo '</div>';
	}

	/**
	 * 顯示可用優惠券
	 *
	 * @param \WC_Checkout $checkout 結帳頁面
	 */
	public function show_available_coupons( $checkout ): void {

		$coupons = $this->get_valid_coupons(); // 取得網站一般優惠
		if (empty($coupons)) {
			return;
		}
		global $power_plugins_settings;
		// var_dump($power_plugins_settings);
		$coupons = $this->sort_coupons($coupons);
		echo '<div class="power-coupon">';
		echo '<h2 class="">消費滿額折扣</h2>';
		echo '<div class="mb-2 py-2">';
		foreach ($coupons as $coupon) {
			$props = $this->get_coupon_props($coupon);
			\load_template(
						__DIR__ . '/templates/basic.php',
						false,
						[
							'coupon' => $coupon,
							'props'  => $props,
						]
						);
		}
		echo '</div>';
		echo '</div>';
	}

	/**
	 * 取得有效的優惠券
	 *
	 * @return array
	 */
	public function get_valid_coupons(): array {
		$coupon_ids = \get_posts(
			[
				'posts_per_page' => -1,
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_query'     => [
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
				],
			]
			) ?? [];

		$coupons = array_map(
			function ( $coupon_id ) {
				return new \WC_Coupon($coupon_id);
			},
			$coupon_ids
			);

		$coupons = array_filter(
			$coupons,
			function ( $coupon ) {
				return 'award_deduct' !== $coupon->get_discount_type();
			}
			);

		$discounts = new \WC_Discounts(WC()->cart);

		foreach ($coupons as $key => $coupon) {
			$valid = $discounts->is_coupon_valid($coupon);
			if (is_wp_error($valid)) {
				unset($coupons[ $key ]);
			}
		}

		return $coupons;
	}

	/**
	 * 取得有效的購物金折抵優惠券
	 *
	 * @return array
	 */
	public function get_valid_award_deduct_coupons(): array {
		$coupon_ids = \get_posts(
			[
				'posts_per_page' => -1,
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'meta_key'       => 'discount_type',
				'meta_value'     => 'award_deduct',
			]
			) ?? [];

		$coupons = array_map(
			function ( $coupon_id ) {
				return new \WC_Coupon($coupon_id);
			},
			$coupon_ids
			);

		$discounts = new \WC_Discounts(WC()->cart);

		foreach ($coupons as $key => $coupon) {
			$valid = $discounts->is_coupon_valid($coupon);
			if (is_wp_error($valid)) {
				unset($coupons[ $key ]);
			}
		}

		return $coupons;
	}

	/**
	 * 自訂條件
	 *
	 * @param bool          $is_valid 是否有效
	 * @param \WC_Coupon    $coupon 優惠券
	 * @param \WC_Discounts $discounts 折扣
	 * @return bool
	 */
	public function custom_condition( bool $is_valid, \WC_Coupon $coupon, \WC_Discounts $discounts ): bool {
		$condition_by_membership_ids = $this->filter_condition_by_membership_ids($coupon);
		$condition_by_first_purchase = $this->filter_condition_by_first_purchase($coupon);
		$condition_by_min_quantity   = $this->filter_condition_by_min_quantity($coupon);

		return $condition_by_membership_ids && $condition_by_first_purchase && $condition_by_min_quantity && $is_valid;
	}

	/**
	 * 過濾會員等級條件
	 *
	 * @param \WC_Coupon $coupon 優惠券
	 * @return bool
	 */
	private function filter_condition_by_membership_ids( \WC_Coupon $coupon ): bool {
		// 或是 allowed_membership_ids 包含 user 的 membership id 的 coupon
		$allowed_membership_ids = $coupon->get_meta(Metabox::ALLOWED_MEMBER_LV_FIELD_NAME);
		$allowed_membership_ids = is_array($allowed_membership_ids) ? $allowed_membership_ids : [];
		$user_id                = \get_current_user_id();
		$user_member_lv_id      = \gamipress_get_user_rank_id($user_id, Base::MEMBER_LV_POST_TYPE);

		if (in_array($user_member_lv_id, $allowed_membership_ids)) {
			return true;
		}

		// Filter 出 allowed_membership_ids 是 [] 的 coupon (沒有限制)
		return empty($allowed_membership_ids);
	}

	/**
	 * 過濾首次購買條件
	 *
	 * @param \WC_Coupon $coupon 優惠券
	 * @return bool
	 */
	private function filter_condition_by_first_purchase( \WC_Coupon $coupon ): bool {
		$value = $coupon->get_meta(Metabox::FIRST_PURCHASE_COUPON_FIELD_NAME);
		if ('yes' === $value) {
			return $this->is_first_purchase();
		}

		return true;
	}

	/**
	 * 過濾最小數量條件
	 *
	 * @param \WC_Coupon $coupon 優惠券
	 * @return bool
	 */
	private function filter_condition_by_min_quantity( \WC_Coupon $coupon ): bool {
		$min_quantity = (int) $coupon->get_meta(Metabox::MIN_QUANTITY_FIELD_NAME);
		if (!empty($min_quantity)) {
			$cart                 = \WC()->cart;
			$cart_item_quantities = (int) array_sum($cart->get_cart_item_quantities());
			return $cart_item_quantities >= $min_quantity;
		}

		return true;
	}

	/**
	 * 隱藏小的coupon
	 * 只出現大的coupon
	 *
	 * @param array $available_coupons 可用優惠券
	 * @return array
	 */
	public function sort_coupons( array $available_coupons ): array {
		global $power_plugins_settings;

		$further_coupons = $this->further_coupons;

		usort(
			$available_coupons,
			function ( $a, $b ) {
				return (int) $this->get_coupon_amount($b) - (int) $this->get_coupon_amount($a);
			}
			);
		usort(
			$further_coupons,
			function ( $a, $b ) {
				return (int) $a->get_minimum_amount() - (int) $a->get_minimum_amount();
			}
			);

		// 只保留前 N 個 further_coupons
		$show_further_coupons_qty = (int) $power_plugins_settings[ Settings::SHOW_FURTHER_COUPONS_QTY_FIELD_NAME ] ?? 3;
		$sliced_further_coupons   = array_slice($further_coupons, 0, $show_further_coupons_qty);

		// 如果啟用只顯示最大折扣券
		if ($power_plugins_settings[ Settings::ENABLE_BIGGEST_COUPON_FIELD_NAME ]) {
			$result = array_merge([ $available_coupons[0] ], $sliced_further_coupons);
		} else {
			$result = array_merge($available_coupons, $sliced_further_coupons);
		}
		return $result;
	}

	/**
	 * 獲取優惠券金額
	 *
	 * @param \WC_Coupon $coupon 優惠券
	 * @return int
	 */
	public function get_coupon_amount( \WC_Coupon $coupon ): int {
		if ($coupon->is_type([ 'percent' ])) {
			$cart = WC()->cart;
			return (int) $coupon->get_amount() * (int) $cart->subtotal / 100;
		}
		return (int) $coupon->get_amount();
	}

	/**
	 * 獲取最大折扣券
	 *
	 * @param array $coupons 優惠券數組
	 * @return \WC_Coupon|null
	 */
	public function get_biggest_coupon( array $coupons ): ?\WC_Coupon {
		// 初始化最大折扣金額
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

	/**
	 * 獲取優惠券屬性
	 *
	 * @param \WC_Coupon $coupon 優惠券
	 * @return array
	 */
	public function get_coupon_props( \WC_Coupon $coupon ): array {
		if (empty($coupon)) {
			return [];
		}
		$cart_total     = (int) WC()->cart->subtotal;
		$coupon_amount  = (int) $coupon->get_amount();
		$minimum_amount = (int) $coupon->get_minimum_amount();

		$props = [];
		if ($cart_total < $minimum_amount) {

			$d                     = $minimum_amount - $cart_total;
			$shop_url              = site_url('shop');
			$props['is_available'] = false;
			$props['reason']       = "，<span class='text-red-400'>還差 ${d} 元</span>，<a href='${shop_url}'>再去多買幾件 》</a>";
			$props['disabled']     = 'disabled';
			$props['disabled_bg']  = 'bg-gray-100 cursor-not-allowed';
			return $props;
		} else {
			$props['is_available'] = true;
			$props['reason']       = '';
			$props['disabled']     = '';
			$props['disabled_bg']  = '';
			return $props;
		}
	}

	/**
	 * 獲取用戶訂單數量
	 *
	 * @param int $user_id 用戶 ID
	 * @return int
	 */
	public static function get_order_quantity_by_user( int $user_id = 0 ): int {
		if (empty($user_id)) {
			$user_id = \get_current_user_id();
		}
		global $wpdb;
		$query = $wpdb->prepare("SELECT COUNT(ID) FROM {$wpdb->prefix}posts WHERE post_type = 'shop_order' AND post_status IN ('wc-completed', 'wc-processing') AND post_author = %d", $user_id);

		$order_count = (int) $wpdb->get_var($query); // phpcs:ignore

		return $order_count;
	}

	/**
	 * 是否首次購買
	 *
	 * @param int $user_id 用戶 ID
	 * @return bool
	 */
	private function is_first_purchase( int $user_id = 0 ): bool {
		if (empty($user_id)) {
			$user_id = \get_current_user_id();
		}
		$count = self::get_order_quantity_by_user($user_id);

		return $count === 0;
	}

	/**
	 * 修改最小金額條件
	 *
	 * @param bool       $not_valid 是否無效
	 * @param \WC_Coupon $coupon 優惠券
	 * @param int        $subtotal 小計
	 * @return bool
	 */
	public function modify_minimum_amount_condition( bool $not_valid, \WC_Coupon $coupon, int $subtotal ): bool {
		global $power_plugins_settings;

		if ($power_plugins_settings[ Settings::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME ] && $not_valid) {
			$this->further_coupons[] = $coupon;
		}

		return $not_valid;
	}

	/**
	 * Checkout 頁面操作折抵購物金
	 *
	 * @return void
	 */
	public function award_deduct_point(): void {
		$value     = (int) $_POST['value']; // phpcs:ignore
		$coupon_id = (int) $_POST['coupon_id']; // phpcs:ignore
		if (!\is_user_logged_in()) {
			\wp_send_json_error('請先登入');
		}

		if (!$value || !$coupon_id) {
			\wp_send_json_error('請輸入折抵的數值');
		}

		$user_id    = \get_current_user_id();
		$user_point = \gamipress_get_user_points($user_id, 'ee_point');

		if ($user_point < $value) {
			\wp_send_json_error('購物金不足');
		}

		$sub_total = (int) WC()->cart->subtotal;
		$coupon    = new \WC_Coupon($coupon_id);

		$deduct_ratio      = $coupon->get_amount() / 100;
		$max_deduct_amount = \floor($sub_total * $deduct_ratio);

		if ($value > $max_deduct_amount) {
			\wp_send_json_error('購物金折抵金額超過上限');
		}

		\WC()->session->set(
			'custom_fee',
			[
				'amount'    => -$value,
				'coupon_id' => $coupon_id,
			]
			);
		// 觸發購物車重新計算
		\WC()->cart->calculate_totals();

		\wp_send_json_success(
			[
				'updated_user_point' => $user_point - $value,
			]
			);
	}

	/**
	 * 添加自訂費用
	 */
	public function add_custom_fee(): void {
		$value = WC()->session->get('custom_fee');

		$point_amount      = $value['amount'] ?? 0;
		$coupon_id         = $value['coupon_id'] ?? 0;
		$coupon            = new \WC_Coupon($coupon_id);
		$sub_total         = (int) WC()->cart->subtotal;
		$deduct_ratio      = $coupon->get_amount() / 100;
		$max_deduct_amount = \floor($sub_total * $deduct_ratio);

		if ($point_amount > $max_deduct_amount) {
			$point_amount = $max_deduct_amount;
		}

		if ($value && \is_checkout()) {
			\WC()->cart->add_fee(
				'購物金折抵',
					$point_amount,
					false
			);
		}
	}

	/**
	 * 執行扣除點數
	 *
	 * @param \WC_Order $order 訂單
	 */
	public function exec_deduct_point( \WC_Order $order ): void {
		$user_id      = $order->get_customer_id();
		$value        = WC()->session->get('custom_fee');
		$point_amount = $value['amount'] ?? 0;
		if (!$point_amount) {
			return;
		}

		$order->update_meta_data('award_deduct_point', $point_amount);
		$order->save();

		$updated_user_point = \gamipress_deduct_points_to_user(
			$user_id,
			(int) $point_amount,
			'ee_point',
			[
				'admin_id'       => 0,
				'achievement_id' => null,
				'reason'         => "使用購物金折抵 {$point_amount} 元",
				'log_type'       => 'points_deduct',
			]
			);
	}

	/**
	 * 清除購物車和會話
	 */
	public function clear_cart_and_session(): void {
		\WC()->session->set('custom_fee', null);
	}

	/**
	 * 清除自訂費用
	 */
	public function clear_fee(): void {
		if (isset($_GET['remove_item'])) {
			\WC()->session->set('custom_fee', null);
		}
	}

	/**
	 * 恢復購物金折抵點數
	 *
	 * @param int $order_id 訂單 ID
	 */
	public function restore_award_deduct_point( int $order_id ): void {
		$order        = \wc_get_order($order_id);
		$deduct_point = (int) $order->get_meta('award_deduct_point');
		$award_point  = $deduct_point * -1;
		$user_id      = $order->get_customer_id();
		\gamipress_award_points_to_user(
			$user_id,
			(int) $award_point,
			'ee_point',
			[
				'admin_id'       => 0,
				'achievement_id' => null,
				'reason'         => "歸還購物金折抵 {$deduct_point} 元，訂單 #{$order_id} 取消",
				'log_type'       => 'points_award',
			]
		);
	}

	// public function add_fee(\WC_Cart $cart): void
	// {
	// $cart->add_fee(__("首次消費折 {$discount} 元", 'power_membership'), -$discount);
	// }
}
