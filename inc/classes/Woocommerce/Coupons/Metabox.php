<?php
/**
 * 優惠券 MetaBox
 */

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Plugin;

/**
 * 優惠券 MetaBox
 */
final class Metabox {
	use \J7\WpUtils\Traits\SingletonTrait;

	const ALLOWED_MEMBER_LV_FIELD_NAME       = 'power_membership_allowed_membership_ids';
	const FIRST_PURCHASE_COUPON_FIELD_NAME   = 'power_membership_first_purchase_coupon';
	const MIN_QUANTITY_FIELD_NAME            = 'power_membership_min_quantity';
	const HIDE_THIS_COUPON_FIELD_NAME        = 'power_membership_hide_this_coupon';
	const AWARD_DEDUCT_FIELD_NAME            = 'power_membership_award_deduct';
	const AWARD_DEDUCT_PERCENTAGE_FIELD_NAME = 'power_membership_award_deduct_percentage';

	/**
	 * 建構子
	 */
	public function __construct() {
		\add_filter( 'woocommerce_coupon_discount_types', [ __CLASS__, 'add_custom_discount_type' ] );
		\add_action('woocommerce_coupon_options', [ $this, 'add_general_fields' ], 10, 2);
		\add_action('woocommerce_coupon_options_usage_restriction', [ $this, 'add_restriction_fields' ], 10, 2);
		\add_action('woocommerce_coupon_options_save', [ $this, 'update_fields' ], 10, 2);
		\add_action('admin_enqueue_scripts', [ __CLASS__, 'add_script' ]);
	}

	/**
	 * 新增自訂折扣類型
	 *
	 * @param array $discount_types 折扣類型
	 * @return array
	 */
	public static function add_custom_discount_type( array $discount_types ): array {
		$discount_types['award_deduct'] = '購物金折抵';
		return $discount_types;
	}

	/**
	 * 新增一般欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	public function add_general_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		if (empty($coupon)) {
			echo 'coupon is empty';
			return;
		}
		$this->add_hide_this_coupon_field($coupon_id, $coupon);
	}

	/**
	 * 新增限制欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	public function add_restriction_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		if (empty($coupon)) {
			echo 'coupon is empty';
			return;
		}
		$this->add_allowed_membership_field($coupon_id, $coupon);
		$this->add_first_purchase_field($coupon_id, $coupon);
		$this->add_min_quantity_field($coupon_id, $coupon);
	}

	/**
	 * 新增不自動顯示此優惠券欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function add_hide_this_coupon_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $coupon->get_meta(self::HIDE_THIS_COUPON_FIELD_NAME);
		$value = $value === 'yes' ? 'yes' : 'no';
		?>
		<p class="form-field">
			<label for="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>">不自動顯示此優惠券</label>
			<input type="checkbox" class="checkbox" name="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>" id="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>" value="yes" <?php checked($value, 'yes'); ?>>
			<span class="description">因為使用 Power Membership 會自動將所有用戶可用的優惠券顯示出來，當你想要發給特定用戶輸入折價碼的時候，可以勾選此選項。</span>
		</p>
		<?php
	}

	/**
	 * 新增允許的會員等級欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function add_allowed_membership_field( int $coupon_id, \WC_Coupon $coupon ): void {
		// phpcs:disable
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME; ?>"><?php _e('允許的會員等級', 'power_membership'); ?></label>
				<select id="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME; ?>" name="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME . '[]'; ?>" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('無須會員等級', 'power_membership'); ?>">
		<?php
		$member_lvs    = gamipress_get_ranks(
						[
							'post_type' => Base::MEMBER_LV_POST_TYPE,
						]
						);
		$member_lv_ids = $coupon->get_meta(self::ALLOWED_MEMBER_LV_FIELD_NAME);
		$member_lv_ids = is_array($member_lv_ids) ? $member_lv_ids : [];

		if ($member_lvs) {
			foreach ($member_lvs as $member_lv) {
				echo '<option value="' . esc_attr($member_lv->ID) . '"' . wc_selected($member_lv->ID, $member_lv_ids) . '>' . esc_html($member_lv->post_title) . '</option>';
			}
		}
		?>
				</select>
		<?php echo wc_help_tip(__('只有指定的會員等級才可以使用此優惠', 'power_membership')); ?>
			</p>
		</div>
		<?php
		// phpcs:enable
	}

	/**
	 * 新增首次購買優惠欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function add_first_purchase_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $coupon->get_meta(self::FIRST_PURCHASE_COUPON_FIELD_NAME);
		printf(
		/*html*/'
		<div class="options_group">
			<p class="form-field">
				<label for="%1$s">%2$s</label>
				<input type="checkbox" class="checkbox" name="%1$s" id="%1$s" value="yes" %3$s>
				<span class="description">%4$s</span>
			</p>
		</div>
		',
			self::FIRST_PURCHASE_COUPON_FIELD_NAME,
		'首次購買優惠',
		\checked('yes', $value, false),
		'如果此用戶已登入，且從沒有在你網站買過東西，就可以使用折價券'
		);
	}

	/**
	 * 新增最少購買數量限制欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function add_min_quantity_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $coupon->get_meta(self::MIN_QUANTITY_FIELD_NAME);

		printf(
		/*html*/'
		<div class="options_group">
			<p class="form-field">
				<label for="%1$s">%2$s</label>
				<input type="text" class="short wc_input_price" name="%1$s" id="%1$s" placeholder="無數量限制" value="%3$s">
				<span class="description">%4$s</span>
			</p>
		</div>
		',
		self::MIN_QUANTITY_FIELD_NAME,
		'最少購買數量限制',
		$value,
		'使用此功能可以實現：買2件9折、買3件折200 等優惠方案<br />填 0 的話則不會做任何現制'
		);
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	public function update_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		if (empty($coupon)) {
			return;
		}
		$this->update_hide_this_coupon_field($coupon_id, $coupon);
		$this->update_allowed_membership_field($coupon_id, $coupon);
		$this->update_first_purchase_field($coupon_id, $coupon);
		$this->update_min_quantity_field($coupon_id, $coupon);
		$coupon->save();
	}

	/**
	 * 更新不自動顯示此優惠券欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function update_hide_this_coupon_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $_POST[ self::HIDE_THIS_COUPON_FIELD_NAME ] ?? ''; // phpcs:ignore

		if ('yes' === $value) {
			$coupon->update_meta_data(self::HIDE_THIS_COUPON_FIELD_NAME, $value);
		} else {
			$coupon->update_meta_data(self::HIDE_THIS_COUPON_FIELD_NAME, 'no');
		}
	}

	/**
	 * 更新允許的會員等級欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function update_allowed_membership_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$allowed_membership_ids = isset($_POST[ self::ALLOWED_MEMBER_LV_FIELD_NAME ]) ? (array) \wp_unslash($_POST[ self::ALLOWED_MEMBER_LV_FIELD_NAME ]) : []; // phpcs:ignore

		$coupon->update_meta_data(self::ALLOWED_MEMBER_LV_FIELD_NAME, $allowed_membership_ids);
	}

	/**
	 * 更新首次購買優惠欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function update_first_purchase_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $_POST[ self::FIRST_PURCHASE_COUPON_FIELD_NAME ] ?? ''; // phpcs:ignore

		if ('yes' === $value) {
			$coupon->update_meta_data(self::FIRST_PURCHASE_COUPON_FIELD_NAME, $value);
		} else {
			$coupon->update_meta_data(self::FIRST_PURCHASE_COUPON_FIELD_NAME, 'no');
		}
	}

	/**
	 * 更新最少購買數量限制欄位
	 *
	 * @param int        $coupon_id 優惠券 ID
	 * @param \WC_Coupon $coupon 優惠券
	 */
	private function update_min_quantity_field( int $coupon_id, \WC_Coupon $coupon ): void {
		if (isset($_POST[ self::MIN_QUANTITY_FIELD_NAME ])) { // phpcs:ignore
			$value = (int) \sanitize_text_field($_POST[ self::MIN_QUANTITY_FIELD_NAME ]); // phpcs:ignore
			$coupon->update_meta_data(self::MIN_QUANTITY_FIELD_NAME, $value);
		}
	}

	/**
	 * 新增腳本
	 *
	 * @param string $hook 頁面 hook
	 */
	public static function add_script( string $hook ): void {
		$key = 'pm_coupon_admin';
		global $post;
		if (( $hook === 'post.php' && $post->post_type === 'shop_coupon' ) || ( $hook === 'post-new.php' && $post->post_type === 'shop_coupon' )) {
			\wp_enqueue_script(
				"{$key}-js",
				Plugin::$url . "/inc/assets/js/{$key}.js",
				[ 'jquery' ],
				Plugin::$version,
				[
					'strategy'  => 'async',
					'in_footer' => true,
				]
				);
		}
	}
}

Metabox::instance();
