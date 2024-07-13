<?php
/**
 * Power Plugins 的選單
 */

declare ( strict_types=1 );

namespace J7\PowerMembership\Admin\Menu;

use J7\PowerMembership\Plugin;
use J7\PowerMembership\Resources\MemberLv\Utils;
use J7\WpToolkit\PowerPlugins;
use J7\WpUtils\Classes\Point;
use J7\WpUtils\Traits\SingletonTrait;

use function add_action;
use function esc_html__;
use function sprintf;
use function woocommerce_wp_select;

/**
 * Class Setting
 */
final class Setting {
	use SingletonTrait;

	public const ENABLE_BIGGEST_COUPON_FIELD_NAME            = 'pm_biggest_coupon';
	public const ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME      = 'pm_show_further_coupons';
	public const SHOW_FURTHER_COUPONS_QTY_FIELD_NAME         = 'pm_show_further_coupons_qty';
	public const ENABLE_SHOW_COUPON_FORM_FIELD_NAME          = 'pm_show_coupon_form';
	public const ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME    = 'pm_show_available_coupons';
	public const ENABLE_BONUS_ON_CERTAIN_DAY_FIELD_NAME      = 'pm_bonus_on_certain_day';
	public const DEDUCT_LIMIT_PERCENTAGE_FIELD_NAME          = 'pm_deduct_limit_percentage'; // 購物金使用上限
	public const AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME = 'pm_award_points_after_user_register'; // 會員註冊完成就送 XX 購物金
	public const AWARD_POINTS_AFTER_USER_BIRTHDAY_FIELD_NAME = 'pm_award_points_after_user_bday'; // 會員生日月份送 XX 購物金


	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'setup_theme', [ $this, 'set_redux_menu' ], 10 );
	}

	/**
	 * 設定 Redux 選單
	 *
	 * @return void
	 */
	public function set_redux_menu(): void {
		// 每個點數的欄位
		$points_fields_array = $this->get_points_fields_array();

		$power_plugins_instance = PowerPlugins::get_instance();
		$section                = [
			'title'  => Plugin::$app_name,
			'id'     => Plugin::$kebab,
			'desc'   => '<p><span class="dashicons dashicons-info" style="color: #52accc;"></span>' . sprintf(
				// translators: %1$s is a placeholder for the link to the Github page.
				esc_html__( '可以到 %1$s 查看主要功能與使用方式', 'power-membership' ),
				'<a href="' . Plugin::$github_repo . '" target="_blank">Github 頁面</a>'
			) . '<p>',
			'icon'   => 'el el-tag',
			'fields' => [
				[
					'id'       => self::ENABLE_SHOW_COUPON_FORM_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__( '顯示 Woocommerce 預設的輸入折價碼功能', 'power-membership' ),
					'subtitle' => esc_html__(
						'啟用後，checkout 頁面將顯示 Woocommerce 預設的輸入折價碼功能',
						'power-membership'
					),
					'on'       => esc_html__( '啟用', 'power-membership' ),
					'off'      => esc_html__( '關閉', 'power-membership' ),
					'default'  => 0,
				],
				[
					'id'       => self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__( '自動顯示可用的折價券', 'power-membership' ),
					'subtitle' => esc_html__(
						'啟用後，checkout 頁面將顯示所有能用的折價券，為了得到最好的購物體驗，強烈建議開啟此項',
						'power-membership'
					),
					'on'       => esc_html__( '啟用', 'power-membership' ),
					'off'      => esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
				],
				[
					'id'       => self::ENABLE_BIGGEST_COUPON_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__( '只顯示一張最大張的折價券', 'power-membership' ),
					'subtitle' => esc_html__( '關閉後，checkout 頁面將顯示所有 "可用" 的折價券', 'power-membership' ),
					'on'       => esc_html__( '啟用', 'power-membership' ),
					'off'      => esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
					'required' => [ self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ],
				],
				[
					'id'       => self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__( '顯示更高消費門檻的折價券', 'power-membership' ),
					'subtitle' => esc_html__( '關閉後，checkout 頁面將隱藏所有 "不可用" 的折價券', 'power-membership' ),
					'on'       => esc_html__( '啟用', 'power-membership' ),
					'off'      => esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
					'required' => [ self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ],
				],
				[
					'id'         => self::SHOW_FURTHER_COUPONS_QTY_FIELD_NAME,
					'type'       => 'number',
					'title'      => esc_html__( '顯示多少個更高消費門檻的折價券', 'power-membership' ),
					'subtitle'   => esc_html__( '預設為 3 個，不建議太多，會影響結帳頁的畫面', 'power-membership' ),
					'default'    => 3,
					'required'   => [ self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME, 'equals', 1 ],
					'attributes' => [
						'min' => 0,
						'max' => 30,
					],
				],

				...$points_fields_array,

				[
					'id'       => self::ENABLE_BONUS_ON_CERTAIN_DAY_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__( '啟用特定日期優惠', 'power-membership' ),
					'subtitle' => esc_html__( '啟用後，每週四週日消費每  $2000 ＝ 20 購物金', 'power-membership' ),
					'on'       => esc_html__( '啟用', 'power-membership' ),
					'off'      => esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
				],
				[
					'id'       => 'condition-repeater-wrapper',
					'title'    => esc_html__( '設定條件', 'power-membership' ),
					'type'     => 'callback',
					'callback' => [ $this, 'custom_callback_function' ],
				],
				[
					'id'         => self::DEDUCT_LIMIT_PERCENTAGE_FIELD_NAME,
					'type'       => 'number',
					'title'      => esc_html__( '使用上限為訂單金額幾%', 'power-membership' ),
					'subtitle'   => esc_html__( '不包含稅金、運費', 'power-membership' ),
					'default'    => 2,
					'attributes' => [
						'min' => 0,
						'max' => 100,
					],
				],

			],
		];
		$power_plugins_instance->set_sections( $section );
	}

	/**
	 * 取得點數欄位陣列
	 *
	 * @return array
	 */
	private function get_points_fields_array(): array {
		$all_points = Plugin::instance()->point_service_instance->get_all_points();
		// 每個點數的欄位
		$points_fields_array = [];
		foreach ( $all_points as $point ) {
			$points_fields_array[] = [
				'type'   => 'section',
				'title'  => sprintf( esc_html__( '⟪%s⟫ 點數設定', 'power-membership' ), // phpcs:ignore
					$point->name
				),
				'indent' => true,
			];

			$points_fields_array_user_register = $this->get_points_fields_array_user_register( $point );
			foreach ( $points_fields_array_user_register as $field ) {
				$points_fields_array[] = $field;
			}

			$points_fields_array_user_bday = $this->get_points_fields_array_user_bday( $point );
			foreach ( $points_fields_array_user_bday as $field ) {
				$points_fields_array[] = $field;
			}

			$points_fields_array[] = [
				'type'   => 'section',
				'indent' => false,
			];
		}

		return $points_fields_array;
	}

	/**
	 * 取得用戶註冊送購物金點數欄位陣列
	 *
	 * @param Point $point 點數
	 *
	 * @return array
	 */
	private function get_points_fields_array_user_register( Point $point ): array {
		$points_fields_array  = [];
		$register_enable      = self::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME . '__' . $point->id . '__enable';
		$register_amount      = self::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME . '__' . $point->id . '__amount';
		$register_expire_days = self::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME . '__' . $point->id . '__expire_days';

		$points_fields_array[] = [
			'id'      => $register_enable,
			'type'    => 'switch',
			'title'   => sprintf( esc_html__( '啟用首次成為會員送 ⟪%s⟫', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'on'      => esc_html__( '啟用', 'power-membership' ),
			'off'     => esc_html__( '關閉', 'power-membership' ),
			'default' => 0,
		];
		$points_fields_array[] = [
			'id'         => $register_amount,
			'type'       => 'number',
			'title'      => sprintf(
				esc_html__( '首次成為會員送多少 ⟪%s⟫', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'subtitle'   => esc_html__( '預設為 30 天', 'power-membership' ),
			'default'    => 30,
			'required'   => [
				$register_enable,
				'equals',
				1,
			],
			'attributes' => [
				'min' => 0,
			],
		];
		$points_fields_array[] = [
			'id'         => $register_expire_days,
			'type'       => 'number',
			'title'      => sprintf(
				esc_html__( '首次成為會員 ⟪%s⟫ 使用期限(天) ', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'subtitle'   => esc_html__( '-1 天代表沒有使用期限', 'power-membership' ),
			'default'    => - 1,
			'required'   => [
				$register_enable,
				'equals',
				1,
			],
			'attributes' => [
				'min' => - 1,
			],
		];

		return $points_fields_array;
	}

	/**
	 * 取得用戶生日送購物金點數欄位陣列
	 *
	 * @param $point Point
	 *
	 * @return array
	 */
	private function get_points_fields_array_user_bday( Point $point ): array {
		$points_fields_array = [];
		$bday_enable         = self::AWARD_POINTS_AFTER_USER_BIRTHDAY_FIELD_NAME . '__' . $point->id . '__enable';
		$bday_amount         = self::AWARD_POINTS_AFTER_USER_BIRTHDAY_FIELD_NAME . '__' . $point->id . '__amount';
		$bday_expire_days    = self::AWARD_POINTS_AFTER_USER_BIRTHDAY_FIELD_NAME . '__' . $point->id . '__expire_days';

		$points_fields_array[] = [
			'id'      => $bday_enable,
			'type'    => 'switch',
			'title'   => sprintf( esc_html__( '啟用會員生日禮金 ⟪%s⟫', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'on'      => esc_html__( '啟用', 'power-membership' ),
			'off'     => esc_html__( '關閉', 'power-membership' ),
			'default' => 0,
		];

		$points_fields_array[] = [
			'id'         => $bday_amount,
			'type'       => 'number',
			'title'      => sprintf(
				esc_html__( '生日禮金送多少 ⟪%s⟫', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'default'    => 0,
			'required'   => [
				$bday_enable,
				'equals',
				1,
			],
			'attributes' => [
				'min' => 0,
			],
		];
		$points_fields_array[] = [
			'id'         => $bday_expire_days,
			'type'       => 'number',
			'title'      => sprintf(
				esc_html__( '生日禮金 ⟪%s⟫ 使用期限(天) ', 'power-membership' ), // phpcs:ignore
				$point->name
			),
			'subtitle'   => esc_html__( '-1 天代表沒有使用期限', 'power-membership' ),
			'default'    => - 1,
			'required'   => [
				$bday_enable,
				'equals',
				1,
			],
			'attributes' => [
				'min' => - 1,
			],
		];

		return $points_fields_array;
	}

	public static function get_params() {
		// 所有條件
		$condition_options = [
			'on_register'       => '會員首次註冊',
			'on_birthday_month' => '會員生日當月1號',
			'on_birthday_day'   => '會員生日當日',
			'on_special_day'    => '特定日期',
		];

		// 選擇點數種類
		$all_points    = Plugin::instance()->point_service_instance->get_all_points();
		$point_options = [];
		foreach ( $all_points as $point ) {
			$point_options[ $point->id ] = $point->name;
		}

		$all_member_lvs    = Utils::get_member_lvs();
		$member_lv_options = [
			0 => '所有會員',
		];
		foreach ( $all_member_lvs as $member_lv ) {
			$member_lv_options[ $member_lv->id ] = $member_lv->name;
		}

		return [
			'base_name'         => 'power_plugins_settings',
			'condition_options' => $condition_options,
			'point_options'     => $point_options,
			'member_lv_options' => $member_lv_options,
		];
	}

	/**
	 * Custom callback function
	 * Repeater + 複雜選擇器
	 * FIXME: 選不到 condition ，未來有空再解
	 *
	 * @param array $field Field array.
	 *
	 * @return void
	 */
	public function custom_callback_function( array $field ): void {
		echo '<div id="condition-repeater" class="my-4"></div>';
	}
}

Setting::instance();
