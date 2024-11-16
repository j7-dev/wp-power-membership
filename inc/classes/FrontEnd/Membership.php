<?php
/**
 * MyAccount Membership Page
 */

declare( strict_types=1 );

namespace J7\PowerMembership\FrontEnd;

use J7\PowerMembership\Utils\Base;

/**
 * Class Membership
 */
final class Membership {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * @var array<string, string> $my_account_pages 我的帳號頁面
	 */
	public static $my_account_pages = [
		'pm_membership' => '我的會籍',
	];

	/**
	 * Constructor
	 */
	public function __construct() {

		\add_action( 'init', [ __CLASS__, 'custom_account_pages' ] );
		\add_filter( 'woocommerce_account_menu_items', [ __CLASS__, 'custom_menu_items' ], 100, 1 );
		foreach ( self::$my_account_pages as $endpoint => $page ) {
			\add_action(
				'woocommerce_account_' . $endpoint . '_endpoint',
				[ __CLASS__, "render_{$endpoint}" ] // @phpstan-ignore-line
			);
		}

		\add_action( 'woocommerce_edit_account_form', [ __CLASS__, 'add_birthday_field' ] );
		\add_action( 'woocommerce_save_account_details', [ __CLASS__, 'save_birthday_field' ] );
		\add_action( 'woocommerce_register_form', [ __CLASS__, 'add_birthday_field_to_registration_form' ] );
		\add_action( 'woocommerce_created_customer', [ __CLASS__, 'save_birthday_field_on_registration' ] );
	}

	/**
	 * Custom account pages
	 */
	public static function custom_account_pages(): void {
		foreach ( self::$my_account_pages as $endpoint => $page ) {
			\add_rewrite_endpoint( $endpoint, EP_ROOT | EP_PAGES );
		}
		\flush_rewrite_rules();
	}

	/**
	 * Add menu item
	 *
	 * @param array<string,string> $items Menu items.
	 *
	 * @return array<string,string>
	 */
	public static function custom_menu_items( array $items ): array {
		foreach ( self::$my_account_pages as $endpoint => $page ) {
			$items[ $endpoint ] = $page;
		}
		return $items;
	}

	/**
	 * Render pm_membership
	 */
	public static function render_pm_membership(): void { // phpcs:ignore
		$member_lv      = \gamipress_get_user_rank(null, 'member_lv');
		$next_member_lv = \gamipress_get_next_user_rank(null, 'member_lv');

		$next_member_lv_threshold       = \get_post_meta($next_member_lv->ID, 'power_membership_threshold', true);
		$next_member_lv_threshold_price = $next_member_lv_threshold ? \wc_price( (float) $next_member_lv_threshold) : null;
		// 取得最近12個月累積金額
		$current_user_id = \get_current_user_id();

		$order_data       = Base::query_order_data_by_user_date($current_user_id, 12);
		$acc_amount       = (float) $order_data['total'];
		$acc_amount_price = \wc_price( (float) $order_data['total']);

		$diff      = $next_member_lv_threshold ? $next_member_lv_threshold - $acc_amount : null;
		$diff_text = $diff ? '，還差 ' . \wc_price( (float) $diff) : '';

		$fields = [
			'目前會員等級'         => match ($member_lv instanceof \WP_Post) {
				true => $member_lv->post_title,
				false => '無法取得會員等級',
			},
			'購物金'            => \wc_price(\gamipress_get_user_points(0, 'ee_point')),
			'下個會員等級'         => match ($next_member_lv instanceof \WP_Post) {
				true => $next_member_lv->post_title,
				false => '已是最高會員等級',
			},
			'升級條件'           => $next_member_lv_threshold ? "最近一年累積消費達到 {$next_member_lv_threshold_price}元{$diff_text}" : '',
			'最近一年累積消費'       => $acc_amount_price,
			'邀請朋友成為會員，賺取購物金' => \site_url("?ref={$current_user_id}"),
		];

		$rows = '';
		foreach ($fields as $key => $value) {
			$rows .= sprintf(
				/*html*/'<tr><td>%s</td><td>%s</td></tr>',
				$key,
				$value,
			);
		}

		printf(
		/*html*/'
			<table>
				%s
			</table>
			',
		$rows,
		);

		printf(
		/*html*/'<a href="%s" class="button">回報問題</a>',
		\site_url('report-error'),
		);
	}


	/**
	 * Add birthday field
	 */
	public static function add_birthday_field(): void {
		\woocommerce_form_field(
				'birthday',
				[
					'type'     => 'date',
					'label'    => __( '生日', 'textdomain' ),
					'required' => true,
				],
				\get_user_meta( \get_current_user_id(), 'birthday', true )
				);
	}

	/**
	 * Save birthday field
	 *
	 * @param int $user_id User ID.
	 */
	public static function save_birthday_field( $user_id ): void {
		if ( isset( $_POST['birthday'] ) ) {// phpcs:ignore
			\update_user_meta( $user_id, 'birthday', \sanitize_text_field( $_POST['birthday'] ) ); // phpcs:ignore
		}
	}

	/**
	 * 在註冊表單新增生日欄位
	 */
	public static function add_birthday_field_to_registration_form(): void {
		\woocommerce_form_field(
			'birthday',
			[
				'type'  => 'date',
				'label' => '生日',
			]
		);
	}

	/**
	 * 在註冊時儲存生日欄位到 user_meta
	 *
	 * @param int $customer_id 客戶 ID
	 */
	public static function save_birthday_field_on_registration( $customer_id ): void {
		if ( isset( $_POST['birthday'] ) ) { // phpcs:ignore
			\update_user_meta( $customer_id, 'birthday', \sanitize_text_field( $_POST['birthday'] ) ); // phpcs:ignore
		}
	}
}
