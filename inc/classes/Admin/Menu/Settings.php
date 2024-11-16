<?php
/**
 * 後台設定頁面
 */

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Menu;

use J7\PowerMembership\Plugin;
use J7\WpToolkit\PowerPlugins;


/**
 * 後台設定頁面
 */
final class Settings extends PowerPlugins {
	const ENABLE_BIGGEST_COUPON_FIELD_NAME         = 'power_membership_biggest_coupon';
	const ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME   = 'power_membership_show_further_coupons';
	const SHOW_FURTHER_COUPONS_QTY_FIELD_NAME      = 'power_membership_show_further_coupons_qty';
	const ENABLE_SHOW_COUPON_FORM_FIELD_NAME       = 'power_membership_show_coupon_form';
	const ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME = 'power_membership_show_available_coupons';


	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action('setup_theme', [ $this, 'set_sections' ], 10);
		parent::__construct();
	}

	/**
	 * 設定頁面
	 *
	 * @param array $section 設定頁面
	 *
	 * @return void
	 */
	public function set_sections( $section = [] ): void {
		$this->sections[] = [
			'title'  => Plugin::$app_name,
			'id'     => Plugin::$kebab,
			'desc'   => '<p><span class="dashicons dashicons-info" style="color: #52accc;"></span>' . sprintf(esc_html__('可以到 %1$s 查看主要功能與使用方式', 'power_membership'), '<a href="' . Plugin::$github_repo . '" target="_blank">Github 頁面</a>') . '<p>',
			'icon'   => 'el el-tag',
			'fields' => [
				[
					'id'       => self::ENABLE_SHOW_COUPON_FORM_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__('顯示 Woocommerce 預設的輸入折價碼功能', 'power_membership'),
					'subtitle' => esc_html__('啟用後，checkout 頁面將顯示 Woocommerce 預設的輸入折價碼功能', 'power_membership'),
					'on'       => esc_html__('啟用', 'power_membership'),
					'off'      => esc_html__('關閉', 'power_membership'),
					'default'  => 0,
				],
				[
					'id'       => self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__('自動顯示可用的折價券', 'power_membership'),
					'subtitle' => esc_html__('啟用後，checkout 頁面將顯示所有能用的折價券，為了得到最好的購物體驗，強烈建議開啟此項', 'power_membership'),
					'on'       => esc_html__('啟用', 'power_membership'),
					'off'      => esc_html__('關閉', 'power_membership'),
					'default'  => 1,
				],
				[
					'id'       => self::ENABLE_BIGGEST_COUPON_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__('只顯示一張最大張的折價券', 'power_membership'),
					'subtitle' => esc_html__('關閉後，checkout 頁面將顯示所有 "可用" 的折價券', 'power_membership'),
					'on'       => esc_html__('啟用', 'power_membership'),
					'off'      => esc_html__('關閉', 'power_membership'),
					'default'  => 1,
					'required' => [ self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ],
				],
				[
					'id'       => self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME,
					'type'     => 'switch',
					'title'    => esc_html__('顯示更高消費門檻的折價券', 'power_membership'),
					'subtitle' => esc_html__('關閉後，checkout 頁面將隱藏所有 "不可用" 的折價券', 'power_membership'),
					'on'       => esc_html__('啟用', 'power_membership'),
					'off'      => esc_html__('關閉', 'power_membership'),
					'default'  => 1,
					'required' => [ self::ENABLE_SHOW_AVAILABLE_COUPONS_FIELD_NAME, 'equals', 1 ],
				],
				[
					'id'         => self::SHOW_FURTHER_COUPONS_QTY_FIELD_NAME,
					'type'       => 'number',
					'title'      => esc_html__('顯示多少個更高消費門檻的折價券', 'power_membership'),
					'subtitle'   => esc_html__('預設為 3 個，不建議太多，會影響結帳頁的畫面', 'power_membership'),
					'default'    => 3,
					'required'   => [ self::ENABLE_SHOW_FURTHER_COUPONS_FIELD_NAME, 'equals', 1 ],
					'attributes' => [
						'min' => 0,
						'max' => 30,
					],
				],
			],
		];
	}
}
