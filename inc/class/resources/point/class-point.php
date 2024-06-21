<?php
/**
 * 初始化
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\Point;

use Exception;
use J7\PowerMembership\Admin\Menu\Setting;
use J7\PowerMembership\Plugin;
use J7\PowerMembership\Resources\MemberLv\Utils;
use J7\WpUtils\Classes\WPUPoint;
use WC_Cart;

/**
 * Class Init
 */
final class Point {

	use \J7\WpUtils\Traits\SingletonTrait;


	public const FEE_NAME_PREFIX = '購物金折抵';

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'wpu_point_update_user_points', array( $this, 'create_user_log' ), 100, 4 );

		\add_action( 'user_register', array( $this, 'award_after_user_register' ), 10, 2 );

		// 結帳頁使用購物金
		\add_action( 'woocommerce_cart_calculate_fees', array( $this, 'apply_points_for_deduction' ) );
		\add_action( 'woocommerce_order_status_completed', array( $this, 'deduct_user_point' ) );
	}

	/**
	 * 對指定會員發放生日禮金https://elextensions.com/how-to-add-discount-programmatically-on-woocommerce/
	 *
	 * @param int $user_id - user id
	 * @return void
	 */
	public static function award_bday_by_user_id( int $user_id ): void {
		$user           = \get_userdata( $user_id );
		$user_member_lv = Utils::get_member_lv_by( 'user_id', $user_id );
		$all_points     = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			$award_points = $user_member_lv?->get_bday_award_points( $point->slug );
			if ( ! $award_points ) {
				continue;
			}

			$allow_bday_reward = self::allow_bday_reward( $user_id, $point );

			if ( $allow_bday_reward ) {
				// Award the points to the user
				$point->award_points_to_user(
					(int) $user_id,
					array(
						'title' => "生日禮金發放 {$point->name} {$award_points} 點 - {$user->display_name} ({$user_member_lv->name})",
						'type'  => 'system',
					),
					$award_points
				);

				\update_user_meta( $user_id, 'last_' . $point->slug . '_birthday_awarded_on', gmdate( 'Y-m-d H:i:s', strtotime( '+8 hours' ) ) );
			}
			// else 不發放生日禮金
		}
	}

	/**
	 * Allow birthday reward
	 *
	 * @param int      $user_id - user id
	 * @param WPUPoint $point - point
	 * @return bool
	 */
	public static function allow_bday_reward( int $user_id, WPUPoint $point ): bool {
		$last_awarded_on = \get_user_meta( $user_id, 'last_' . $point->slug . '_birthday_awarded_on', true );
		if ( ! $last_awarded_on ) {
			return true;
		}

		$last_awarded_on = strtotime( $last_awarded_on );
		$today           = strtotime( gmdate( 'Y-m-d H:i:s', strtotime( '+8 hours' ) ) );

		$diff = $today - $last_awarded_on;

		$days = $diff / ( 60 * 60 * 24 );

		return $days >= 330; // 365天後才能再次發放，太嚴格，這邊只抓330天，避免有人調整天數盜領點數
	}

	/**
	 * Get log types
	 * PENDING
	 *
	 * @return array
	 */
	public function get_log_types(): array {
		return array(
			'system' => '系統',
			'admin'  => '管理員',
			'user'   => '使用者',
		);
	}

	/**
	 * Create user log
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points
	 * @param string  $point_slug - point slug
	 *
	 * @return void
	 * @throws Exception
	 */
	public function create_user_log( int $user_id = 0, array $args = array(), float $points = 0, string $point_slug = 'wpu_default_point' ): void {
		Plugin::instance()->log_utils_instance->insert_user_log( $user_id, $args, $points, $point_slug );
	}

	/**
	 * 首次成為會員送 XX 購物金
	 *
	 * @param integer $user_id - user id
	 * @param array   $userdata - user data
	 * @return void
	 */
	public function award_after_user_register( int $user_id, array $userdata ): void {

		$all_points = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			$award_points = (float) \get_post_meta( $point->id, Metabox::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME, true );
			if ( ! $award_points ) {
				continue;
			}

			$point->award_points_to_user(
				$user_id,
				array(
					'title' => '首次成為會員送購物金',
					'type'  => 'system',
				),
				$award_points
			);
		}
	}

	/**
	 * Apply points for deduction
	 * PENDING 未來可以做成，勾選後再套用
	 *
	 * @param WC_Cart $cart - cart
	 * @return void
	 */
	public function apply_points_for_deduction( WC_Cart $cart ): void {

		global $woocommerce;

		// 扣物金扣抵上限百分比
		$deduct_limit_percentage = $this->get_deduct_limit_percentage();

		$discount_price = $this->get_discount( $cart );

		if ( ! $deduct_limit_percentage || ! $discount_price ) {
			return;
		}

		$woocommerce->cart->add_fee(
			name: self::FEE_NAME_PREFIX . " {$deduct_limit_percentage}%",
			amount: $discount_price,
		);
	}



	/**
	 * Get deduct limit percentage
	 * 購物金扣抵上限百分比，使用上限為訂單金額幾%
	 *
	 * @return float
	 */
	public function get_deduct_limit_percentage(): float {
		global $power_plugins_settings;
		// 扣物金扣抵上限百分比
		$deduct_limit_percentage = (float) $power_plugins_settings[ Setting::DEDUCT_LIMIT_PERCENTAGE_FIELD_NAME ];
		return $deduct_limit_percentage / 100;
	}

	/**
	 * Get discount
	 *
	 * @param WC_Cart $cart - cart
	 * @return float
	 */
	public function get_discount( WC_Cart $cart ): float {
		// 扣物金扣抵上限百分比
		$deduct_limit_percentage = self::get_deduct_limit_percentage();

		// get cart subtotal
		$cart_subtotal = (float) $cart->get_subtotal();

		$default_point = Plugin::instance()->point_utils_instance->get_default_point();

		$current_user_id = \get_current_user_id();

		$user_points = (float) \get_user_meta( $current_user_id, $default_point->slug, true );

		$max_deduct_amount = $cart_subtotal * $deduct_limit_percentage;

		$deduct_amount = \min( $max_deduct_amount, $user_points );

		return -1 * $deduct_amount;
	}

	public function deduct_user_point( $order_id ): void {
		$order = \wc_get_order( $order_id );

		$default_point = Plugin::instance()->point_utils_instance->get_default_point();

		// get fee
		$fees = $order->get_fees();

		foreach ( $fees as $fee ) {
			$fee_name = $fee->get_name();
			if ( str_starts_with( $fee_name, self::FEE_NAME_PREFIX ) ) {
				// 執行扣點
				$default_point->deduct_points_to_user(
					user_id: (int) $order->get_customer_id(),
					args: array(
						'title' => '訂單折抵購物金' . $default_point->name . ' ' . $fee->get_amount() . ' 點',
						'type'  => 'system',
					),
					points: (float) $fee->get_amount()
				);
			}
		}
	}
}

Point::instance();