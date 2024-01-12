<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

use J7\PowerMembership\Utils;

class UserEdit
{

	public function __construct()
	{
		\add_action('show_user_profile', [$this, 'add_fields'], 10);
		\add_action('edit_user_profile', [$this, 'add_fields'], 10);
		\add_action('edit_user_profile_update', [$this, 'update_fields'], 10);
		\add_action('personal_options_update', [$this, 'update_fields'], 10);

		// 往 gamipress_log 添加時間跟上色
		\add_action('gamipress_before_render_log', [$this, 'add_date_to_gamipress_log'], 10, 2);
		\add_action('gamipress_after_render_log', [$this, 'add_closetag_to_gamipress_log'], 10, 2);
	}

	public function add_fields($user): void
	{
		$user_id          = $user->ID;
		$rank_earned_time = date('Y-m-d H:i:s', \gamipress_get_rank_earned_time($user_id, Utils::MEMBER_LV_POST_TYPE));

		$args = array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => array('shop_order'),
			'post_status' => array('wc-completed', 'wc-processing'),
		);
		$order_data = Utils::get_order_data_by_user_date($user_id, 0, $args);

		$sales_total = 'NT$ ' . $order_data['total'];
		$sales_total .= ' | 訂單 ' . $order_data['order_num'] . ' 筆';

		$user_registered = date('Y-m-d H:i:s', strtotime($user->user_registered) + 8 * 3600);

		$user_member_lv_id = \gamipress_get_user_rank_id($user_id, Utils::MEMBER_LV_POST_TYPE);
		$birthday          = \get_user_meta($user_id, 'birthday', true);

?>
		<h2>自訂欄位</h2>
		<table class="form-table" id="fieldset-yc_wallet">
			<tbody>
				<tr>
					<th>
						<label for="<?= Utils::MEMBER_LV_POST_TYPE ?>">會員等級</label>
					</th>
					<td>
						<select name="<?= Utils::MEMBER_LV_POST_TYPE ?>" id="<?= Utils::MEMBER_LV_POST_TYPE ?>" class="regular-text" value="<?= $user_member_lv_id ?>">
							<option value="">請選擇</option>
							<?php
							$member_lvs = get_posts([
								'post_type'      => Utils::MEMBER_LV_POST_TYPE,
								'posts_per_page' => -1,
								'post_status'    => 'publish',
								'orderby'        => 'menu_order',
								'order'          => 'ASC',
							]);

							foreach ($member_lvs as $member_lv) {
								$selected = ($user_member_lv_id == $member_lv->ID) ? 'selected' : '';
								echo '<option value="' . $member_lv->ID . '" ' . $selected . '>' . $member_lv->post_title . '</option>';
							}
							?>
						</select>
						<span class="description">上次變更時間：<?= $rank_earned_time ?></span>
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
						<input type="text" value="<?= $sales_total ?>" id="sales_total" name="sales_total" disabled="disabled" class="regular-text">
					</td>
				</tr>

				<tr class="user_register_time">
					<th>
						<label for="user_register_time">註冊時間</label>
					</th>
					<td>
						<input type="text" value="<?= $user_registered; ?>" id="user_register_time" name="user_register_time" disabled="disabled" class="regular-text">
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

	public function update_fields($user_id): void
	{
		if (!\current_user_can('edit_user', $user_id)) {
			return;
		}

		if (isset($_POST[Utils::MEMBER_LV_POST_TYPE])) {
			\update_user_meta($user_id, Utils::CURRENT_MEMBER_LV_META_KEY, $_POST[Utils::MEMBER_LV_POST_TYPE]);
		}
	}

	public function add_date_to_gamipress_log($log_id, $a): void
	{

		$results = $a['query']->results;

		foreach ($results as $result) {
			if ($result->log_id == $log_id) {
				$color = ($result->type == 'points_deduct') ? 'green' : 'red';
				echo '<span style="color:' . $color . '">' . $result->date;
				break;
			}
		}
	}

	public function add_closetag_to_gamipress_log($log_id, $a): void
	{
		echo '<span>';
	}
}
