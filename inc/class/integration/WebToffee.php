<?php
/**
 * 擴展 WebToffee 的 Import Export WordPress Users and WooCommerce Customers 插件的 import user 欄位
 * TODO 不只有 wpu_default_point 還會有其他點數
 */

declare(strict_types=1);

namespace J7\PowerMembership\Integration;

if (!class_exists('Wt_Import_Export_For_Woo_Basic')) {
	return;
}

/**
 * WebToffee
 */
final class WebToffee {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_filter('hf_csv_customer_post_columns', [ $this, 'add_columns' ]);
		\add_filter('hf_csv_customer_import_columns', [ $this, 'add_import_columns' ]);
	}

	/**
	 * Add columns
	 *
	 * @param array $columns - columns
	 *
	 * @return array
	 */
	public function add_columns( array $columns ): array {
		$columns['wpu_default_point']     = 'wpu_default_point';
		$columns['member_lv']             = 'member_lv';
		$columns['member_lv_earned_time'] = 'member_lv_earned_time';

		return $columns;
	}

	/**
	 * Add import columns
	 *
	 * @param array $columns - columns
	 *
	 * @return array
	 */
	public function add_import_columns( array $columns ): array {
		$columns['wpu_default_point']     = [
			'title'       =>'購物金',
			'description' =>'',
		];
		$columns['member_lv']             = [
			'title'       =>'會員等級',
			'description' =>'',
		];
		$columns['member_lv_earned_time'] = [
			'title'       =>'會員獲得等級的時間',
			'description' =>'',
		];

		return $columns;
	}
}

WebToffee::instance();
