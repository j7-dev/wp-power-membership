<?php
/**
 * Wp-admin 的 User edit
 *
 * TODO
 * 1. 註冊 生日欄位
 * 2. 顯示點數 log
 * 3. 可以修改點數
 */

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\PowerMembership\Plugin;



/**
 * Class UserEdit
 */
final class UserEdit {
	use \J7\WpUtils\Traits\SingletonTrait;

	const REASON_FIELD_NAME = 'pm_reason';
	const BDAY_FIELD_NAME   = 'birthday';



	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'show_user_profile', array( $this, 'add_fields' ), 10 );
		\add_action( 'edit_user_profile', array( $this, 'add_fields' ), 10 );
		\add_action( 'edit_user_profile_update', array( $this, 'update_fields' ), 10 );
		\add_action( 'personal_options_update', array( $this, 'update_fields' ), 10 );
	}

	/**
	 * Add fields
	 *
	 * @param \WP_User $user User.
	 * @return void
	 */
	public function add_fields( \WP_User $user ): void {
		$user_id = $user->ID;

		$member_lv_earned_time = \get_user_meta( $user_id, MemberLvInit::MEMBER_LV_EARNED_TIME_META_KEY, true );
		$member_lv_earned_time = $member_lv_earned_time ? gmdate( 'Y-m-d H:i:s', $member_lv_earned_time + 8 * 3600 ) : '-';

		$args       = array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => array( 'shop_order' ),
			'post_status' => array( 'wc-completed', 'wc-processing' ),
		);
		$order_data = Base::get_order_data_by_user_date( $user_id, 0, $args );

		$sales_total  = 'NT$ ' . $order_data['total'];
		$sales_total .= ' | 訂單 ' . $order_data['order_num'] . ' 筆';

		$user_registered = gmdate( 'Y-m-d H:i:s', strtotime( $user->user_registered ) + 8 * 3600 );

		$user_member_lv_id = \get_user_meta( $user_id, MemberLvInit::POST_TYPE, true );

		$birthday = \get_user_meta( $user_id, self::BDAY_FIELD_NAME, true );

		$member_lvs = \get_posts(
			array(
				'post_type'      => MemberLvInit::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		if ( ! $member_lvs ) {
			echo '<p>請先建立會員等級</p>';
			return;
		}

		$all_points = Plugin::instance()->point_utils_instance->get_all_points();

//phpcs:disable
		?>
		<h2>自訂欄位</h2>
		<table class="form-table" id="fieldset-yc_wallet">
			<tbody>
				<tr>
					<th>
						<label for="<?php echo MemberLvInit::POST_TYPE; ?>">會員等級</label>
					</th>
					<td>
						<select name="<?php echo MemberLvInit::POST_TYPE; ?>" id="<?php echo MemberLvInit::POST_TYPE; ?>" class="regular-text" value="<?php echo $user_member_lv_id; ?>">
							<option value="">請選擇</option>
		<?php

		foreach ( $member_lvs as $member_lv ) {
			$selected = ( $user_member_lv_id === $member_lv->ID ) ? 'selected' : '';
			echo '<option value="' . $member_lv->ID . '" ' . $selected . '>' . $member_lv->post_title . '</option>';
		}
		?>
						</select>
						<span class="description">上次變更時間：<?php echo $member_lv_earned_time; ?></span>
					</td>
				</tr>
				<tr>
					<th>
						<label for="time_MemberLVexpire_date">會員到期日</label>
					</th>
					<td>
						TODO
					</td>
				</tr>
				<tr>
					<th>
						<label for="sales_total">累積銷售額</label>
					</th>
					<td>
						<input type="text" value="<?php echo $sales_total; ?>" id="sales_total" name="sales_total" disabled="disabled" class="regular-text">
					</td>
				</tr>

				<tr class="user_register_time">
					<th>
						<label for="user_register_time">註冊時間</label>
					</th>
					<td>
						<input type="text" value="<?php echo $user_registered; ?>" id="user_register_time" name="user_register_time" disabled="disabled" class="regular-text">
					</td>
				</tr>


<?php foreach($all_points as $point):
			$user_points = (float) \get_user_meta( $user_id, $point->slug, true );
			?>
			<tr>
				<th><label for="<?php echo $point->slug; // phpcs:ignore ?>"><?php echo '直接修改' . $point->name; // phpcs:ignore ?></label></th>
				<td>
				<?php
				\woocommerce_wp_text_input(
					array(
						'id'          => $point->slug,
						'style'       => 'width:25rem;',
						'class' => 'show',
						'label'       => '',
						'type'        => 'number',
						'value'       => $user_points,
						'data_type'   => 'decimal',
						'placeholder' => '',
					)
				);
				?>
					<p>目前用戶有 <?php echo $point->name; // phpcs:ignore ?> 數量:
				<?php echo number_format( $user_points, 2 ); ?></p>
				</td>
			</tr>

			<tr id="<?php echo $point->slug . '_reason'; // phpcs:ignore ?>">
				<th><label for="<?php echo $point->slug . '_reason'; // phpcs:ignore ?>"><?php echo $point->name; // phpcs:ignore ?>調整原因</label></th>
				<td>
				<?php
				\woocommerce_wp_textarea_input(
					array(
				'id'          => $point->slug . '_reason',
				'style'       => 'width:25rem;',
				'class' => 'show',
				'label'       => '',
				'value'       => '',
				'placeholder' => '',
				'rows'        => 5,
					)
				);
				?>
				</td>
			</tr>

			<tr class="<?= self::BDAY_FIELD_NAME ?>">
					<th>
						<label for="<?= self::BDAY_FIELD_NAME ?>">生日</label>
					</th>
					<td>
						<input type="date" value="<?php echo $birthday; ?>" id="<?= self::BDAY_FIELD_NAME ?>" name="<?= self::BDAY_FIELD_NAME ?>" class="regular-text">
					</td>
				</tr>

					<?php endforeach; ?>


			</tbody>
		</table>

		<p>🚧 TODO 顯示點數 log</p>

		<script>
			(function($) {
				// disable mousewheel on a input number field when in focus
				// (to prevent Chromium browsers change the value when scrolling)
				$('form').on('focus', 'input[type=number]', function(e) {
					$(this).on('wheel.disableScroll', function(e) {
						e.preventDefault()
					})
				})
				$('form').on('blur', 'input[type=number]', function(e) {
					$(this).off('wheel.disableScroll')
				})
			})(jQuery)
		</script>
		<?php
		//phpcs:enabled
	}

	/**
	 * 更新欄位
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function update_fields( $user_id ): void {
		if ( ! \current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		//phpcs:disable
		ob_start();
		var_dump($_POST[ MemberLvInit::POST_TYPE ]);
		\J7\WpUtils\Classes\Log::info('' . ob_get_clean());
		if ( isset( $_POST[ MemberLvInit::POST_TYPE ] ) ) {
			\update_user_meta( $user_id, MemberLvInit::POST_TYPE, \sanitize_text_field($_POST[ MemberLvInit::POST_TYPE ]) );
			\update_user_meta( $user_id, MemberLvInit::MEMBER_LV_EARNED_TIME_META_KEY, time() );
		}
		if ( isset( $_POST[ self::BDAY_FIELD_NAME ] ) ) {
			\update_user_meta( $user_id, self::BDAY_FIELD_NAME, \sanitize_text_field($_POST[ self::BDAY_FIELD_NAME ]) );
		}



		$all_points = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			if ( isset( $_POST[ $point->slug ] ) ) {
				$points = (float) $_POST[ $point->slug ];
				$reason = $_POST[ $point->slug . '_reason' ] ?? '';
				$reason = \sanitize_text_field( $reason );

				$point->update_user_points(
					$user_id,
					array(
						'title' => '[手動修改] ' . $reason,
						'type'  => 'manual',
					) ,
					$points
				);
			}
		}

		//phpcs:enabled
	}
}

UserEdit::instance();
