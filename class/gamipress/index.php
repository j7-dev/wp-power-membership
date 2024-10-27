<?php
/**
 * GamiPress 事件
 */

declare(strict_types=1);

namespace J7\PowerMembership\Gamipress;

use J7\PowerMembership\Utils;

/**
 * 註冊 GamiPress 事件
 *
 * @see https://gamipress.com/snippets/tutorials/creating-a-custom-event/
 */
final class GamiPress {

	const WEEK_DAY_KEY = '_gamipress_every_week_day';
	const RATIO_KEY    = '_gamipress_ratio'; // 每 OOO 元 送 X 購物金

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_filter( 'gamipress_activity_triggers', [ __CLASS__, 'register_triggers' ] );
		\add_action('gamipress_requirement_ui_html_after_limit', [ __CLASS__, 'render_field' ], 10, 2);

		\add_filter( 'gamipress_requirement_object', [ __CLASS__, 'requirement_object' ], 10, 2 );
		\add_action( 'gamipress_ajax_update_requirement', [ __CLASS__, 'ajax_update_requirement' ], 10, 2 );

		\add_action( 'admin_init', [ __CLASS__, 'register_scripts' ] );
		\add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ], 100 );

		\add_action( 'woocommerce_payment_complete', [ __CLASS__, 'listener' ] );
		// TEST \add_action( 'init', [ __CLASS__, 'listener' ] );
	}

	/**
	 * 註冊 GamiPress 事件
	 *
	 * @param array<string, array<string, string>> $triggers 事件列表
	 * @return array<string, array<string, string>> 事件列表
	 */
	public static function register_triggers( array $triggers ): array {
		// The array key will be the group label
		$triggers['消費滿額送購物⾦'] = [
			// Every event of this group is formed with:
			// 'specific_event_that_will_be_triggered' => 'Event Label'
			'pm_award_by_order_amount' => __( '滿額贈購物金', 'gamipress' ),
			// Also, you can add as many events as you want
			// 'my_prefix_another_custom_specific_event' => __( 'Another custom specific event label', 'gamipress' ),
			// 'my_prefix_super_custom_specific_event' => __( 'Super custom specific event label', 'gamipress' ),
		];
		return $triggers;
	}


	/**
	 * 渲染欄位
	 *
	 * @param int $requirement_id 需求 ID
	 * @param int $requirement 需求
	 * @return void
	 */
	public static function render_field( int $requirement_id, int $requirement ): void {
		?>
<style>
	.inline {
		display: inline;
	}
</style>
		<?php
		$key      = self::WEEK_DAY_KEY;
		$selected = \get_post_meta( $requirement_id, $key, true );

		$option_items = [ // date("D") = Mon, Tue, Wed, Thu, Fri, Sat, Sun
			''    => '不限制',
			'Mon' => '每週一',
			'Tue' => '每週二',
			'Wed' => '每週三',
			'Thu' => '每週四',
			'Fri' => '每週五',
			'Sat' => '每週六',
			'Sun' => '每週日',
		];

		$options = '';
		foreach ( $option_items as $value => $label ) {
			$options .= sprintf(
				'<option value="%1$s" %2$s>%3$s</option>',
			$value,
			\selected( $value, $selected, false ),
			$label
				);
		}

		$key2  = self::RATIO_KEY;
		$ratio = \get_post_meta( $requirement_id, $key2, true );

		printf(
		/*html*/'
		<div class="%1$s-row">
				<label for="%1$s-%2$d">Repeat</label>
				<div class="%1$s inline">
						<select id="%1$s-%2$d">
							%3$s
						</select>
				</div>
		</div>
		<div class="%4$s-row">
				<label for="%4$s-%2$d">每滿多少錢</label>
				<div class="%4$s inline">
				<input type="number" name="%4$s" id="%4$s-%2$d" class="points" value="%5$s">
				</div>
		</div>
		',
		$key,
		$requirement_id,
		$options,
		$key2,
		$ratio
		);
	}

	/**
	 * Register admin scripts
	 *
	 * @since       1.0.0
	 * @return      void
	 */
	public static function register_scripts(): void {
		$key = self::WEEK_DAY_KEY;
		// Scripts
		\wp_register_script( "{$key}-js", Utils::get_plugin_url() . "/assets/js/{$key}.js", [ 'jquery' ], Utils::get_plugin_ver(), true );
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook 頁面
	 * @return void
	 */
	public static function enqueue_scripts( string $hook ): void {
		global $post_type;

		if ( $post_type === 'points-type' ) {
			$key = self::WEEK_DAY_KEY;
			\wp_enqueue_script( "{$key}-js" );
		}
	}


	/**
	 * Add the score field to the requirement object
	 *
	 * @param array<string, mixed> $requirement 需求
	 * @param int                  $requirement_id 需求 ID
	 *
	 * @return array
	 */
	public static function requirement_object( $requirement, $requirement_id ) {

		// Expiration fields
		$requirement[ self::WEEK_DAY_KEY ] = \gamipress_get_post_meta( $requirement_id, self::WEEK_DAY_KEY, true );
		$requirement[ self::RATIO_KEY ]    = \absint( \gamipress_get_post_meta( $requirement_id, self::RATIO_KEY, true ) );

		return $requirement;
	}

	/**
	 * Custom handler to save the score on requirements UI
	 *
	 * @param int                  $requirement_id 需求 ID
	 * @param array<string, mixed> $requirement 需求
	 */
	public static function ajax_update_requirement( $requirement_id, $requirement ) {

		// Save expiration fields field
		\gamipress_update_post_meta( $requirement_id, self::WEEK_DAY_KEY, $requirement[ self::WEEK_DAY_KEY ] );
		\gamipress_update_post_meta( $requirement_id, self::RATIO_KEY, absint( $requirement[ self::RATIO_KEY ] ) );
	}


	/**
	 * 事件監聽器
	 *
	 * @param int $order_id 訂單 ID
	 * @return void
	 */
	public static function listener( $order_id ): void {
		$order    = \wc_get_order($order_id);
		if (! ( $order instanceof \WC_Order )) {
			return;
		}
		$order_subtotal = $order->get_subtotal();

		$trigger_ids = \get_posts(
			[
				'post_type'   => 'points-award',
				'post_status' => 'publish',
				'meta_key'    => '_gamipress_trigger_type',
				'meta_value'  => 'pm_award_by_order_amount',
				'fields'      => 'ids',
			]
			);

		// 把 Repeat 欄位為 不限制/或等同今天星期幾的 trigger_id 找出來
		$trigger_ids = \array_filter(
			$trigger_ids,
			function ( $trigger_id ) {
				$repeat = \gamipress_get_post_meta($trigger_id, self::WEEK_DAY_KEY, true);
				return $repeat === '' || $repeat === \wp_date('D');
			}
			);

		foreach ($trigger_ids as $trigger_id) {
			$points = \gamipress_get_post_meta($trigger_id, '_gamipress_points', true);
			if (!$points) {
				continue;
			}
			$ratio        = \gamipress_get_post_meta($trigger_id, self::RATIO_KEY, true);
			$award_points = floor($order_subtotal / $ratio) * $points;

			\gamipress_award_points_to_user(
				$order->get_customer_id(),
				$award_points,
				'ee_point',
				[
					'admin_id'       => 0,
					'achievement_id' => null,
					'reason'         => "滿額贈購物金 {$award_points} 元，消費金額 {$order_subtotal} 元 #{$order->get_order_number()}",
					'log_type'       => 'points_earn',
				]
				);
		}
	}
}

new GamiPress();
