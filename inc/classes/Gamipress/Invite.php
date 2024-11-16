<?php
/**
 * GamiPress 邀請朋友註冊購物金發放
 */

declare(strict_types=1);

namespace J7\PowerMembership\Gamipress;

use J7\PowerMembership\Plugin;

/**
 * 註冊 GamiPress Invite 事件
 *
 * @see https://gamipress.com/snippets/tutorials/creating-a-custom-event/
 */
final class Invite {
	use \J7\WpUtils\Traits\SingletonTrait;

	const INVITE_KEY = '_gamipress_invite';

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_filter( 'gamipress_activity_triggers', [ __CLASS__, 'register_triggers' ] );

		\add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ], 100 );

		\add_action( 'woocommerce_register_form', [ __CLASS__, 'add_ref_field' ] );
		\add_action( 'woocommerce_created_customer', [ __CLASS__, 'save_ref_field' ], 10 );
		\add_action( 'woocommerce_created_customer', [ __CLASS__, 'listener' ], 20 );
	}

	/**
	 * 註冊 GamiPress 事件
	 *
	 * @param array<string, array<string, string>> $triggers 事件列表
	 * @return array<string, array<string, string>> 事件列表
	 */
	public static function register_triggers( array $triggers ): array {
		// The array key will be the group label
		$triggers['邀請朋友註冊'] = [
			// Every event of this group is formed with:
			// 'specific_event_that_will_be_triggered' => 'Event Label'
			'pm_invite_register' => __( '邀請朋友註冊', 'gamipress' ),
			// Also, you can add as many events as you want
			// 'my_prefix_another_custom_specific_event' => __( 'Another custom specific event label', 'gamipress' ),
			// 'my_prefix_super_custom_specific_event' => __( 'Super custom specific event label', 'gamipress' ),
		];
		return $triggers;
	}


	/**
	 * Enqueue wp scripts
	 *
	 * @return void
	 */
	public static function enqueue_scripts(): void {
		$key = self::INVITE_KEY;
		// Scripts
		\wp_enqueue_script(
			"{$key}-js",
			Plugin::$url . "/assets/js/{$key}.js",
			[ 'jquery' ],
			Plugin::$version,
			[
				'in_footer' => false,
				'strategy'  => 'async',
			]
			);
	}



	/**
	 * 事件監聽器
	 *
	 * @param int $customer_id 用戶 ID
	 * @return void
	 */
	public static function listener( $customer_id ): void {
		$ref_user_id = \get_user_meta($customer_id, 'ref_user_ids', true);
		if (!$ref_user_id) {
			return;
		}

		$trigger_ids = \get_posts(
			[
				'post_type'   => 'points-award',
				'post_status' => 'publish',
				'meta_key'    => '_gamipress_trigger_type',
				'meta_value'  => 'pm_invite_register',
				'fields'      => 'ids',
			]
			);

		foreach ($trigger_ids as $trigger_id) {
			$points = \gamipress_get_post_meta($trigger_id, '_gamipress_points', true);
			if (!$points) {
				continue;
			}

			\gamipress_award_points_to_user(
				$ref_user_id,
				$points,
				'ee_point',
				[
					'admin_id'       => 0,
					'achievement_id' => null,
					'reason'         => "邀請朋友 #{$customer_id} 註冊，獲得 {$points} 元購物金",
					'log_type'       => 'points_earn',
				]
				);
		}
	}

	/**
	 * 新增 ref 欄位
	 *
	 * @return void
	 */
	public static function add_ref_field(): void {
		echo '<input type="hidden" id="pm_ref" name="ref" value="" />';
	}

	/**
	 * 儲存 ref 欄位
	 *
	 * @param int $customer_id 用戶 ID
	 * @return void
	 */
	public static function save_ref_field( $customer_id ): void {
		if (!isset($_POST['ref'])) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}
		$ref_user_id = \sanitize_text_field(\wp_unslash($_POST['ref'])); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		\update_user_meta($customer_id, 'ref_user_ids', $ref_user_id);
	}
}
