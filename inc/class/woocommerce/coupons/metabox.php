<?php
/**
 * Coupon Metabox
 */

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Plugin;
use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;

/**
 * Class Metabox
 */
final class Metabox {

	const ALLOWED_MEMBER_LV_FIELD_NAME     = 'pm_allowed_membership_ids';
	const FIRST_PURCHASE_COUPON_FIELD_NAME = 'pm_first_purchase_coupon';
	const MIN_QUANTITY_FIELD_NAME          = 'pm_min_quantity';
	const HIDE_THIS_COUPON_FIELD_NAME      = 'pm_hide_this_coupon';


	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'woocommerce_coupon_options', array( $this, 'add_general_fields' ), 10, 2 );
		\add_action( 'woocommerce_coupon_options_usage_restriction', array( $this, 'add_restriction_fields' ), 10, 2 );
		\add_action( 'woocommerce_coupon_options_save', array( $this, 'update_fields' ), 10, 2 );
	}

	/**
	 * 在 coupon 的 general tab 新增欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	public function add_general_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		$this->render_hide_this_coupon_field( $coupon_id, $coupon );
	}

	/**
	 * Render fields
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function render_hide_this_coupon_field( int $coupon_id, \WC_Coupon $coupon ): void {
		// phpcs:disable
		$value = $coupon->get_meta( self::HIDE_THIS_COUPON_FIELD_NAME );
		$value = $value === 'yes' ? 'yes' : 'no';
		?>
		<p class="form-field">
			<label for="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>">不自動顯示此優惠券</label>
			<input type="checkbox" class="checkbox" name="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>" id="<?php echo self::HIDE_THIS_COUPON_FIELD_NAME; ?>" value="yes" <?php checked( $value, 'yes' ); ?>>
			<span class="description">因為使用 Power Membership 會自動將所有用戶可用的優惠券顯示出來，當你想要發給特定用戶輸入折價碼的時候，可以勾選此選項。</span>
		</p>
		<?php
		// phpcs:enable
	}

	/**
	 * 在 coupon 的 restriction tab 新增欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	public function add_restriction_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		$this->render_allowed_membership_field( $coupon_id, $coupon );
		$this->render_first_purchase_field( $coupon_id, $coupon );
		$this->render_min_quantity_field( $coupon_id, $coupon );
	}



	/**
	 * Render fields
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function render_allowed_membership_field( int $coupon_id, \WC_Coupon $coupon ): void {
		// phpcs:disable
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME; ?>"><?php _e( '允許的會員等級', Plugin::$snake ); ?></label>
				<select id="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME; ?>" name="<?php echo self::ALLOWED_MEMBER_LV_FIELD_NAME . '[]'; ?>" style="width: 50%;" class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( '無須會員等級', Plugin::$snake ); ?>">
		<?php
		$member_lvs    = gamipress_get_ranks(
			[
				'post_type' => MemberLvInit::POST_TYPE,
			]
		);
		$member_lv_ids = $coupon->get_meta( self::ALLOWED_MEMBER_LV_FIELD_NAME );
		$member_lv_ids = is_array( $member_lv_ids ) ? $member_lv_ids : [];

		if ( $member_lvs ) {
			foreach ( $member_lvs as $member_lv ) {
				echo '<option value="' . esc_attr( $member_lv->ID ) . '"' . wc_selected( $member_lv->ID, $member_lv_ids ) . '>' . esc_html( $member_lv->post_title ) . '</option>';
			}
		}
		?>
				</select>
		<?php echo \wc_help_tip( __( '只有指定的會員等級才可以使用此優惠', 'power-membership' ) ); ?>
			</p>
		</div>
		<?php
		// phpcs:enable
	}

	/**
	 * Render fields
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function render_first_purchase_field( int $coupon_id, \WC_Coupon $coupon ): void {
		// phpcs:disable
		$value = $coupon->get_meta( self::FIRST_PURCHASE_COUPON_FIELD_NAME );
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?php echo self::FIRST_PURCHASE_COUPON_FIELD_NAME; ?>"><?php _e( '首次購買優惠', Plugin::$snake ); ?></label>

				<input type="checkbox" class="checkbox" name="<?php echo self::FIRST_PURCHASE_COUPON_FIELD_NAME; ?>" id="<?php echo self::FIRST_PURCHASE_COUPON_FIELD_NAME; ?>" value="yes" <?php checked( 'yes', $value ); ?>>

				<span class="description"><?php _e( '如果此用戶已登入，且從沒有在你網站買過東西，就可以使用折價券', 'power-membership' ); ?></span>

			</p>
		</div>
		<?php
		// phpcs:enable
	}

	/**
	 * Render fields
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function render_min_quantity_field( int $coupon_id, \WC_Coupon $coupon ): void {
		// phpcs:disable
		$value = $coupon->get_meta( self::MIN_QUANTITY_FIELD_NAME );
		?>
		<div class="options_group">
			<p class="form-field">
				<label for="<?php echo self::MIN_QUANTITY_FIELD_NAME; ?>"><?php _e( '最少購買數量限制', Plugin::$snake ); ?></label>

				<input type="text" class="short wc_input_price" name="<?php echo self::MIN_QUANTITY_FIELD_NAME; ?>" id="<?php echo self::MIN_QUANTITY_FIELD_NAME; ?>" placeholder="無數量限制" value="<?php echo $value; ?>">

				<span class="description"><?php _e( '使用此功能可以實現：買2件9折、買3件折200 等優惠方案<br />填 0 的話則不會做任何現制', 'power-membership' ); ?></span>

			</p>
		</div>
		<?php
		// phpcs:enable
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	public function update_fields( int $coupon_id, \WC_Coupon $coupon ): void {
		if ( empty( $coupon ) ) {
			return;
		}
		$this->handle_update_hide_this_coupon_field( $coupon_id, $coupon );
		$this->handle_update_allowed_membership_field( $coupon_id, $coupon );
		$this->handle_update_first_purchase_field( $coupon_id, $coupon );
		$this->handle_update_min_quantity_field( $coupon_id, $coupon );

		$coupon->save();
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function handle_update_hide_this_coupon_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $_POST[ self::HIDE_THIS_COUPON_FIELD_NAME ] ?? ''; // phpcs:ignore

		if ( 'yes' === $value ) {
			$coupon->update_meta_data( self::HIDE_THIS_COUPON_FIELD_NAME, $value );
		} else {
			$coupon->update_meta_data( self::HIDE_THIS_COUPON_FIELD_NAME, 'no' );
		}
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function handle_update_allowed_membership_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$allowed_membership_ids = isset( $_POST[ self::ALLOWED_MEMBER_LV_FIELD_NAME ] ) ? (array) $_POST[ self::ALLOWED_MEMBER_LV_FIELD_NAME ] : array(); // phpcs:ignore

		$coupon->update_meta_data( self::ALLOWED_MEMBER_LV_FIELD_NAME, $allowed_membership_ids );
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function handle_update_first_purchase_field( int $coupon_id, \WC_Coupon $coupon ): void {
		$value = $_POST[ self::FIRST_PURCHASE_COUPON_FIELD_NAME ] ?? ''; // phpcs:ignore

		if ( 'yes' === $value ) {
			$coupon->update_meta_data( self::FIRST_PURCHASE_COUPON_FIELD_NAME, $value );
		} else {
			$coupon->update_meta_data( self::FIRST_PURCHASE_COUPON_FIELD_NAME, 'no' );
		}
	}

	/**
	 * 更新欄位
	 *
	 * @param int        $coupon_id Coupon ID.
	 * @param \WC_Coupon $coupon Coupon object.
	 *
	 * @return void
	 */
	private function handle_update_min_quantity_field( int $coupon_id, \WC_Coupon $coupon ): void {
		if ( isset( $_POST[ self::MIN_QUANTITY_FIELD_NAME ] ) ) { // phpcs:ignore
			$value = (int) \sanitize_text_field( $_POST[ self::MIN_QUANTITY_FIELD_NAME ] );	// phpcs:ignore
			$coupon->update_meta_data( self::MIN_QUANTITY_FIELD_NAME, $value );
		}
	}
}

new Metabox();
