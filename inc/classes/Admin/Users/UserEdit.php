<?php
/**
 * 用戶編輯頁面
 */

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

use J7\PowerMembership\Utils\Base;

/**
 * 用戶編輯頁面
 */
final class UserEdit {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * 建構子
	 */
	public function __construct() {
		\add_action('show_user_profile', [ $this, 'add_fields' ], 10);
		\add_action('edit_user_profile', [ $this, 'add_fields' ], 10);
		\add_action('edit_user_profile_update', [ $this, 'update_fields' ], 10);
		\add_action('personal_options_update', [ $this, 'update_fields' ], 10);
	}

	/**
	 * 新增欄位
	 *
	 * @param \WP_User $user 用戶
	 */
	public function add_fields( \WP_User $user ): void {
		$user_id          = $user->ID;
		$rank_earned_time = date('Y-m-d H:i:s', \gamipress_get_rank_earned_time($user_id, Base::MEMBER_LV_POST_TYPE));

		$args       = [
			'limit'       => -1,
			'customer_id' => $user_id,
			'status'      => [ 'wc-completed', 'wc-processing' ],
		];
		$order_data = Base::get_order_data_by_user_date($user_id, 0, $args);

		$sales_total  = $order_data['total'];
		$sales_total .= ' | 訂單 ' . $order_data['order_num'] . ' 筆';

		$user_registered = date('Y-m-d H:i:s', strtotime($user->user_registered) + 8 * 3600);

		$user_member_lv_id = \gamipress_get_user_rank_id($user_id, Base::MEMBER_LV_POST_TYPE);
		$birthday          = \get_user_meta($user_id, 'birthday', true);

		?>
		<h2>自訂欄位</h2>
		<table class="form-table" id="fieldset-yc_wallet">
			<tbody>
				<tr>
					<th>
						<label for="<?php echo Base::MEMBER_LV_POST_TYPE; ?>">會員等級</label>
					</th>
					<td>
						<select name="<?php echo Base::MEMBER_LV_POST_TYPE; ?>" id="<?php echo Base::MEMBER_LV_POST_TYPE; ?>" class="regular-text" value="<?php echo $user_member_lv_id; ?>">
							<option value="">請選擇</option>
		<?php
		$member_lvs = get_posts(
								[
									'post_type'      => Base::MEMBER_LV_POST_TYPE,
									'posts_per_page' => -1,
									'post_status'    => 'publish',
									'orderby'        => 'menu_order',
									'order'          => 'ASC',
								]
								);

		foreach ($member_lvs as $member_lv) {
			$selected = ( $user_member_lv_id == $member_lv->ID ) ? 'selected' : '';
			echo '<option value="' . $member_lv->ID . '" ' . $selected . '>' . $member_lv->post_title . '</option>';
		}
		?>
						</select>
						<span class="description">上次變更時間：<?php echo $rank_earned_time; ?></span>
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

			</tbody>
		</table>

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
	}

	/**
	 * 更新欄位
	 *
	 * @param int $user_id 用戶 ID
	 */
	public function update_fields( int $user_id ): void {
		if (!\current_user_can('edit_user', $user_id)) {
			return;
		}

		if (isset($_POST[ Base::MEMBER_LV_POST_TYPE ])) { //phpcs:ignore
			\update_user_meta($user_id, Base::CURRENT_MEMBER_LV_META_KEY, $_POST[ Base::MEMBER_LV_POST_TYPE ]); //phpcs:ignore
		}
	}
}
