<?php
/**
 * Power Plugins 的選單
 */

declare (strict_types = 1);

namespace J7\PowerMembership\Admin\Menu;

use J7\PowerMembership\Plugin;
use J7\WpToolkit\PowerPlugins;

/**
 * Class Setting
 */
final class Setting {
	use \J7\WpUtils\Traits\SingletonTrait;

	const ENABLE_BIGGEST_COUPON_FIELD_NAME         = 'pm_biggest_coupon';
	const ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME   = 'pm_show_further_coupons';
	const SHOW_FURTHER_COUPONS_QTY_FIELD_NAME      = 'pm_show_further_coupons_qty';
	const ENABLE_SHOW_COUPON_FORM_FIELD_NAME       = 'pm_show_coupon_form';
	const ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME = 'pm_show_available_coupons';
	const ENABLE_BONUS_ON_CERTAIN_DAY_FIELD_NAME   = 'pm_bonus_on_certain_day';
	const DEDUCT_LIMIT_PERCENTAGE_FIELD_NAME       = 'pm_deduct_limit_percentage'; // 購物金使用上限



	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'setup_theme', array( $this, 'set_redux_menu' ), 10 );
	}

	/**
	 * 設定 Redux 選單
	 *
	 * @return void
	 */
	public function set_redux_menu(): void {
		$power_plugins_instance = PowerPlugins::get_instance();
		$section                = array(
			'title'  => Plugin::$app_name,
			'id'     => Plugin::$kebab,
			'desc'   => '<p><span class="dashicons dashicons-info" style="color: #52accc;"></span>' . sprintf(
				// translators: %1$s is a placeholder for the link to the Github page.
				\esc_html__( '可以到 %1$s 查看主要功能與使用方式', 'power-membership' ),
				'<a href="' . Plugin::$github_repo . '" target="_blank">Github 頁面</a>'
			) . '<p>',
			'icon'   => 'el el-tag',
			'fields' => array(
				array(
					'id'       => self::ENABLE_SHOW_COUPON_FORM_FIELD_NAME,
					'type'     => 'switch',
					'title'    => \esc_html__( '顯示 Woocommerce 預設的輸入折價碼功能', 'power-membership' ),
					'subtitle' => \esc_html__( '啟用後，checkout 頁面將顯示 Woocommerce 預設的輸入折價碼功能', 'power-membership' ),
					'on'       => \esc_html__( '啟用', 'power-membership' ),
					'off'      => \esc_html__( '關閉', 'power-membership' ),
					'default'  => 0,
				),
				array(
					'id'       => self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => \esc_html__( '自動顯示可用的折價券', 'power-membership' ),
					'subtitle' => \esc_html__( '啟用後，checkout 頁面將顯示所有能用的折價券，為了得到最好的購物體驗，強烈建議開啟此項', 'power-membership' ),
					'on'       => \esc_html__( '啟用', 'power-membership' ),
					'off'      => \esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
				),
				array(
					'id'       => self::ENABLE_BIGGEST_COUPON_FIELD_NAME,
					'type'     => 'switch',
					'title'    => \esc_html__( '只顯示一張最大張的折價券', 'power-membership' ),
					'subtitle' => \esc_html__( '關閉後，checkout 頁面將顯示所有 "可用" 的折價券', 'power-membership' ),
					'on'       => \esc_html__( '啟用', 'power-membership' ),
					'off'      => \esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
					'required' => array( self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ),
				),
				array(
					'id'       => self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => \esc_html__( '顯示更高消費門檻的折價券', 'power-membership' ),
					'subtitle' => \esc_html__( '關閉後，checkout 頁面將隱藏所有 "不可用" 的折價券', 'power-membership' ),
					'on'       => \esc_html__( '啟用', 'power-membership' ),
					'off'      => \esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
					'required' => array( self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ),
				),
				array(
					'id'         => self::SHOW_FURTHER_COUPONS_QTY_FIELD_NAME,
					'type'       => 'number',
					'title'      => \esc_html__( '顯示多少個更高消費門檻的折價券', 'power-membership' ),
					'subtitle'   => \esc_html__( '預設為 3 個，不建議太多，會影響結帳頁的畫面', 'power-membership' ),
					'default'    => 3,
					'required'   => array( self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME, 'equals', 1 ),
					'attributes' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				array(
					'id'       => self::ENABLE_BONUS_ON_CERTAIN_DAY_FIELD_NAME,
					'type'     => 'switch',
					'title'    => \esc_html__( '啟用特定日期優惠', 'power-membership' ),
					'subtitle' => \esc_html__( '啟用後，每週四週日消費每  $2000 ＝ 20 購物金', 'power-membership' ),
					'on'       => \esc_html__( '啟用', 'power-membership' ),
					'off'      => \esc_html__( '關閉', 'power-membership' ),
					'default'  => 1,
				),
				array(
					'id'         => self::DEDUCT_LIMIT_PERCENTAGE_FIELD_NAME,
					'type'       => 'number',
					'title'      => \esc_html__( '使用上限為訂單金額幾%', 'power-membership' ),
					'subtitle'   => \esc_html__( '不包含稅金、運費', 'power-membership' ),
					'default'    => 2,
					'attributes' => array(
						'min' => 0,
						'max' => 100,
					),
				),

			),
		);
		$power_plugins_instance->set_sections( $section );
	}
}

Setting::instance();
