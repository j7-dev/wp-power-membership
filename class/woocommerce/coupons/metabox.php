<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;

final class Metabox
{
	const SELECT_FIELD_NAME = Utils::SNAKE . '_allowed_membership_ids';
	const FIRST_PURCHASE_COUPON_FIELD_NAME = Utils::SNAKE . '_first_purchase_coupon';
	const MIN_QUANTITY_FIELD_NAME = Utils::SNAKE . '_min_quantity';


	public function __construct()
	{
		\add_action('woocommerce_coupon_options_usage_restriction', [$this, 'add_fields'], 10, 2);
		\add_action('woocommerce_coupon_options_save', [$this, 'update_fields'], 10, 2);
		\add_filter('woocommerce_coupon_is_valid', [$this, 'custom_coupon_validation'], 10, 2);
	}

	public function add_fields(int $coupon_id, \WC_Coupon $coupon): void
	{
		if (empty($coupon)) {
			echo 'coupon is empty';
			return;
		}
		$this->add_allowed_membership_field($coupon_id, $coupon);
		$this->add_first_purchase_field($coupon_id, $coupon);
		$this->add_min_quantity_field($coupon_id, $coupon);
	}

	private function add_allowed_membership_field(int $coupon_id, \WC_Coupon $coupon): void
	{
?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?= self::SELECT_FIELD_NAME ?>"><?php _e('允許的會員等級', Utils::SNAKE); ?></label>
				<select id="<?= self::SELECT_FIELD_NAME ?>" name="<?= self::SELECT_FIELD_NAME . '[]' ?>" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('無須會員等級', Utils::SNAKE); ?>">
					<?php
					$member_lvs = gamipress_get_ranks([
						'post_type' => Utils::MEMBER_LV_POST_TYPE,
					]);
					$member_lv_ids = $coupon->get_meta(self::SELECT_FIELD_NAME);
					$member_lv_ids = is_array($member_lv_ids) ? $member_lv_ids : [];

					if ($member_lvs) {
						foreach ($member_lvs as $member_lv) {
							echo '<option value="' . esc_attr($member_lv->ID) . '"' . wc_selected($member_lv->ID, $member_lv_ids) . '>' . esc_html($member_lv->post_title) . '</option>';
						}
					}
					?>
				</select>
				<?php echo wc_help_tip(__('只有指定的會員等級才可以使用此優惠', Utils::TEXT_DOMAIN)); ?>
			</p>
		</div>
	<?php
	}

	private function add_first_purchase_field(int $coupon_id, \WC_Coupon $coupon): void
	{
		$value = $coupon->get_meta(self::FIRST_PURCHASE_COUPON_FIELD_NAME);
	?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?= self::FIRST_PURCHASE_COUPON_FIELD_NAME ?>"><?php _e('首次購買優惠', Utils::SNAKE); ?></label>

				<input type="checkbox" class="checkbox" name="<?= self::FIRST_PURCHASE_COUPON_FIELD_NAME ?>" id="<?= self::FIRST_PURCHASE_COUPON_FIELD_NAME ?>" value="yes" <?php checked('yes', $value) ?>>

				<span class="description"><?php _e('如果此用戶已登入，且從沒有在你網站買過東西，就可以使用折價券', Utils::TEXT_DOMAIN); ?></span>

			</p>
		</div>
	<?php
	}

	private function add_min_quantity_field(int $coupon_id, \WC_Coupon $coupon): void
	{
		$value = $coupon->get_meta(self::MIN_QUANTITY_FIELD_NAME);
	?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?= self::MIN_QUANTITY_FIELD_NAME ?>"><?php _e('最少購買數量限制', Utils::SNAKE); ?></label>

				<input type="number" class="" name="<?= self::MIN_QUANTITY_FIELD_NAME ?>" id="<?= self::MIN_QUANTITY_FIELD_NAME ?>" value="<?= $value ?>">

				<span class="description"><?php _e('使用此功能可以實現：買2件9折、買3件折200 等優惠方案', Utils::TEXT_DOMAIN); ?></span>

			</p>
		</div>
<?php
	}

	public function update_fields(int $coupon_id, \WC_Coupon $coupon): void
	{
		if (empty($coupon)) {
			return;
		}

		$this->update_allowed_membership_field($coupon_id, $coupon);
		$this->update_first_purchase_field($coupon_id, $coupon);

		$coupon->save();
	}

	private function update_allowed_membership_field(int $coupon_id, \WC_Coupon $coupon): void
	{
		$allowed_membership_ids = isset($_POST[self::SELECT_FIELD_NAME]) ? (array) $_POST[self::SELECT_FIELD_NAME] : array();

		$coupon->update_meta_data(self::SELECT_FIELD_NAME, $allowed_membership_ids);
	}

	private function update_first_purchase_field(int $coupon_id, \WC_Coupon $coupon): void
	{
		$value = $_POST[self::FIRST_PURCHASE_COUPON_FIELD_NAME] ?? '';

		if ("yes" === $value) {
			$coupon->update_meta_data(self::FIRST_PURCHASE_COUPON_FIELD_NAME, $value);
		} else {
			$coupon->update_meta_data(self::FIRST_PURCHASE_COUPON_FIELD_NAME, "no");
		}
	}

	public function custom_coupon_validation($is_valid, $coupon)
	{
		if (empty($coupon)) {
			return $is_valid;
		}
		$member_lv_ids = $coupon->get_meta(self::SELECT_FIELD_NAME);
		$member_lv_ids = is_array($member_lv_ids) ? $member_lv_ids : [];
		if (empty($member_lv_ids)) {
			return $is_valid;
		}

		$user_id = \get_current_user_id();
		if (empty($user_id)) {
			return false;
		}

		$member_lv_id = \gamipress_get_user_rank_id($user_id, Utils::MEMBER_LV_POST_TYPE);
		if (!in_array($member_lv_id, $member_lv_ids)) {
			$is_valid = false;
			$coupon->add_coupon_message(__('此優惠僅限指定會員等級使用', Utils::TEXT_DOMAIN));
		}

		return $is_valid;
	}
}
