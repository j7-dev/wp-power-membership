<?php
/**
 * GamiPress 購物金發放
 */

declare(strict_types=1);

namespace J7\PowerMembership\Gamipress;

use J7\PowerMembership\Plugin;

/**
 * 註冊 GamiPress 事件
 *
 * @see https://gamipress.com/snippets/tutorials/creating-a-custom-event/
 */
final class GamiPress {
	use \J7\WpUtils\Traits\SingletonTrait;

	const WEEK_DAY_KEY            = '_gamipress_every_week_day';
	const WEEK_DAY_START_TIME_KEY = '_gamipress_every_week_day_start_time';
	const WEEK_DAY_END_TIME_KEY   = '_gamipress_every_week_day_end_time';

	/**
	 * 已付款的訂單狀態
	 *
	 * @var array<string>
	 */
	public static $paid_statuses = [ 'completed', 'processing', 'withdrawal-paid' ];



	const RATIO_KEY = '_gamipress_ratio'; // 每 OOO 元 送 X 購物金

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

		\add_action('woocommerce_order_status_changed', [ __CLASS__, 'listener' ], 10, 3);
		\add_action('woocommerce_order_status_cancelled', [ __CLASS__, 'restore_point' ], 10, 1);
		\add_action('woocommerce_order_status_refunded', [ __CLASS__, 'restore_point' ], 10, 1);

		\add_action( 'woocommerce_checkout_order_review', [ __CLASS__, 'display_award_points_text' ], 15 );

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
		$key                 = self::WEEK_DAY_KEY;
		$key_start_time      = self::WEEK_DAY_START_TIME_KEY;
		$key_end_time        = self::WEEK_DAY_END_TIME_KEY;
		$selected            = \get_post_meta( $requirement_id, $key, true );
		$selected_start_time = \get_post_meta( $requirement_id, $key_start_time, true );
		$selected_end_time   = \get_post_meta( $requirement_id, $key_end_time, true );

		$start_time = $selected_start_time ? explode(':', $selected_start_time) : [ '', '' ];
		$end_time   = $selected_end_time ? explode(':', $selected_end_time) : [ '', '' ];

		$option_items = [ // date("D") = Mon, Tue, Wed, Thu, Fri, Sat, Sun
			''    => '每天',
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
				<div class="inline start_time">
					開始時間
					<input type="number" style="width: 75px;" min="0" max="23" name="hour" value="%6$s" />:
					<input type="number" style="width: 75px;" min="0" max="59" name="minute" value="%7$s" />
				</div>

				<div class="inline end_time">
					結束時間
					<input type="number" style="width: 75px;" min="0" max="23" name="hour" value="%8$s" />:
					<input type="number" style="width: 75px;" min="0" max="59" name="minute" value="%9$s" />
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
		$ratio,
		$start_time[0],
		$start_time[1],
		$end_time[0],
		$end_time[1]
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
		\wp_register_script( "{$key}-js", Plugin::$url . "/assets/js/{$key}.js", [ 'jquery' ], Plugin::$version, true );
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
		\gamipress_update_post_meta( $requirement_id, self::WEEK_DAY_START_TIME_KEY, $requirement[ self::WEEK_DAY_START_TIME_KEY ] );
		\gamipress_update_post_meta( $requirement_id, self::WEEK_DAY_END_TIME_KEY, $requirement[ self::WEEK_DAY_END_TIME_KEY ] );
		\gamipress_update_post_meta( $requirement_id, self::RATIO_KEY, absint( $requirement[ self::RATIO_KEY ] ) );
	}


	/**
	 * 事件監聽器
	 *
	 * @param int    $order_id 訂單 ID
	 * @param string $from 從
	 * @param string $to 到
	 * @return void
	 */
	public static function listener( $order_id, $from, $to ): void {
		$order = \wc_get_order($order_id);
		if (! ( $order instanceof \WC_Order )) {
			return;
		}

		if (in_array($from, self::$paid_statuses, true)) {
			return;
		}

		if (!in_array($to, self::$paid_statuses, true)) {
			return;
		}

		$total_award_points = (int) $order->get_meta('gained_award_points');

		// 如果已經發放過購物金，就不再發放
		if (!!$total_award_points) {
			return;
		}

		$order_total        = $order->get_total();
		$order_created_time = $order->get_date_created();

		$trigger_ids = self::get_trigger_ids($order_created_time?->date_i18n('H:i'));

		$total_award_points = 0;
		foreach ($trigger_ids as $trigger_id) {
			$points = \gamipress_get_post_meta($trigger_id, '_gamipress_points', true);
			$ratio  = \gamipress_get_post_meta($trigger_id, self::RATIO_KEY, true);

			$award_points        = floor($order_total / $ratio) * $points;
			$total_award_points += $award_points;
			\gamipress_award_points_to_user(
				$order->get_customer_id(),
				$award_points,
				'ee_point',
				[
					'admin_id'       => 0,
					'achievement_id' => null,
					'reason'         => "滿額贈購物金 {$award_points} 元，消費金額 {$order_total} 元，訂單編號 #{$order->get_order_number()}",
					'log_type'       => 'points_earn',
				]
				);

			$order->add_order_note("滿額贈購物金 {$award_points} 元，消費金額 {$order_total} 元，訂單編號 #{$order->get_order_number()}，使用 trigger_id #{$trigger_id}規則");
		}

		if ($total_award_points > 0) {
			$order->add_order_note("總計發放購物金 {$total_award_points} 元");
			$order->update_meta_data('gained_award_points', $total_award_points);
			$order->save_meta_data();
		}
	}

	/**
	 * 取得符合條件的 trigger_ids
	 *
	 * @param string $target_time 目標時間 H:i
	 * @return array<int>
	 */
	public static function get_trigger_ids( string $target_time ): array {
		$trigger_ids = \get_posts(
			[
				'post_type'   => 'points-award',
				'post_status' => 'publish',
				'meta_key'    => '_gamipress_trigger_type',
				'meta_value'  => 'pm_award_by_order_amount',
				'fields'      => 'ids',
			]
			);

		// $hk_time = "2025-10-27 20:23";
		// $server_timestamp = strtotime($hk_time . " -8 hours");

		// 把 Repeat 欄位為 不限制/或等同今天星期幾的 trigger_id 找出來
		$trigger_ids = \array_filter(
			$trigger_ids,
			function ( $trigger_id ) use ( $target_time ) {
				$repeat     = \gamipress_get_post_meta($trigger_id, self::WEEK_DAY_KEY, true);
				$start_time = \gamipress_get_post_meta($trigger_id, self::WEEK_DAY_START_TIME_KEY, true);
				$end_time   = \gamipress_get_post_meta($trigger_id, self::WEEK_DAY_END_TIME_KEY, true);

				$points = \gamipress_get_post_meta($trigger_id, '_gamipress_points', true);
				$ratio  = \gamipress_get_post_meta($trigger_id, self::RATIO_KEY, true);

				$missing_data = !$points || !$ratio;

				if ($missing_data) {
					return false;
				}

				if ($start_time) {
					// $target_time = $order_created_time?->date_i18n('H:i');
					$in_range = self::in_range($start_time, $end_time, $target_time);
					return ( $repeat === '' && $in_range ) || ( $repeat === \date('D', \time() + 8 * 3600) && $in_range );
				} else {
					return $repeat === '' || $repeat === \date('D', \time() + 8 * 3600);
				}
			}
			);

		return $trigger_ids;
	}

	/**
	 * 訂單取消或退款時，歸還購物金
	 *
	 * @param int $order_id 訂單 ID
	 *
	 * @return void
	 */
	public static function restore_point( $order_id ): void {
		$order = \wc_get_order($order_id);
		if (! ( $order instanceof \WC_Order )) {
			return;
		}

		$total_award_points = (int) $order->get_meta('gained_award_points');

		if (!$total_award_points) {
			return;
		}

		\gamipress_deduct_points_to_user(
			$order->get_customer_id(),
		$total_award_points,
		'ee_point',
		[
			'admin_id'       => 0,
			'achievement_id' => null,
			'reason'         => "訂單狀態取消，收回已發放購物金 {$total_award_points} 元，消費金額，訂單編號 #{$order->get_order_number()}",
			'log_type'       => 'points_earn',
		] //phpcs:ignore
		);

		$order->add_order_note("訂單狀態取消，收回已發放購物金 {$total_award_points} 元，消費金額，訂單編號 #{$order->get_order_number()}");
		$order->delete_meta_data('gained_award_points');
		$order->save();
	}


	/**
	 * 檢查時間是否在範圍內
	 *
	 * @param string $start_time 開始時間 13:00
	 * @param string $end_time 結束時間 20:00
	 * @param string $compare_time 比較時間 14:00
	 * @return bool
	 */
	public static function in_range( $start_time, $end_time, $compare_time = null ) {
		// 驗證時間格式
		if (!preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $start_time)) {
			return false;
		}

		$compare_time = $compare_time ? $compare_time : \current_time('H:i');

		if (!$end_time || $start_time >= $end_time) {
			// 如果結束時間比開始時間小，就只看開始了沒有
			// 香港比伺服器快 8 小時，所以伺服器時間要減 8 小時才是對應的香港時間
			// 目前伺服器是 UTC+8 所以不用減 8 小時
			$server_start_time = date('H:i', strtotime($start_time));
			// $server_target = date('H:i', strtotime($hk_time)); // LOCAL 測試

			return $compare_time >= $server_start_time;
		}

		// 如果結束時間比開始時間大，檢查當前時間是否在範圍內
		// 目前伺服器是 UTC+8 所以不用減 8 小時
		$server_start_time = date('H:i', strtotime($start_time));
		$server_end_time   = date('H:i', strtotime($end_time));

		return $compare_time >= $server_start_time && $compare_time <= $server_end_time;
	}


	/**
	 * 顯示完成訂單可以獲得多少購物金文字
	 *
	 * @return void
	 */
	public static function display_award_points_text(): void {
		$cart_total         = (float) \WC()->cart->get_total('edit');
		$total_award_points = 0;
		$trigger_ids        = self::get_trigger_ids(\wp_date('H:i'));

		foreach ($trigger_ids as $trigger_id) {
			$points = (float) \gamipress_get_post_meta($trigger_id, '_gamipress_points', true);
			$ratio  = (float) \gamipress_get_post_meta($trigger_id, self::RATIO_KEY, true);

			$award_points        = floor($cart_total / $ratio) * $points;
			$total_award_points += $award_points;
		}

		printf(
		/*html*/'
		<table class="">
			<tbody>
				<tr>
					<th>訂單完成後可獲得購物金</th>
					<td style="text-align: right;">
					%s 元
					</td>
				</tr>
			</tbody>
		</table>',
		\wc_price($total_award_points)
		);
	}
}

GamiPress::instance();
