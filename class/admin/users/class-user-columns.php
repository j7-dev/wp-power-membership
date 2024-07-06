<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

use J7\PowerMembership\Utils;

/**
 * TODO 排序好像沒有作用
 */

final class UserColumns {

	const TRANSIENT_KEY    = 'user_amount_by_member_lv';
	private $order_history = 4; // 秀幾個月前的訂單金額

	public function __construct() {
		// 設定欄位標題
		\add_filter('manage_users_columns', [ $this, 'set_users_column_titles' ], 10, 1);
		// 設定欄位值
		\add_filter('manage_users_custom_column', [ $this, 'set_users_column_values' ], 10, 3);

		// 排序
		\add_filter(
			'users_list_table_query_args',
			function ( $args ) {
				if (isset($_REQUEST['ts_all'])) {
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_total_sales_in_life';
					$args['order']    = $_REQUEST['ts_all'];
					return $args;
				}
				for ($i = 0; $i < 3; $i++) {
					if (isset($_REQUEST[ "ts{$i}" ])) {
						$args['orderby']  = 'meta_value_num';
						$args['meta_key'] = '_total_sales_in_' . $i . '_months_ago';
						$args['order']    = $_REQUEST[ "ts{$i}" ];
					}
				}
				return $args;
			},
			10,
			1
			);

		// 在 users page 篩選不同等級的用戶
		\add_action('manage_users_extra_tablenav', [ $this, 'render_member_filter_options' ]);
		\add_action('pre_get_users', [ $this, 'filter_users_by_member_lv' ]);
	}

	private function count_users_by_member_lv(): array {
		global $wpdb;
		$table_name               = $wpdb->prefix . 'usermeta';
		$meta_key                 = Utils::CURRENT_MEMBER_LV_META_KEY;
		$user_amount_by_member_lv = [];

		$member_lvs = \get_posts(
			[
				'post_type'      => Utils::MEMBER_LV_POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
			);
		foreach ($member_lvs as $member_lv) {
			$meta_value = $member_lv->ID;
			// 使用 $wpdb->prepare 防止 SQL 注入攻击
			$query = $wpdb->prepare(
				"
						SELECT COUNT(DISTINCT user_id) as user_count
						FROM $table_name
						WHERE meta_key = %s AND meta_value = %s
						",
				$meta_key,
				$meta_value
				);

			// 执行查询
			$result = $wpdb->get_row($query);

			// 获取结果
			$user_count = $result->user_count;

			$user_amount_by_member_lv[ $member_lv->ID ] = $user_count;
		}

		\set_transient(self::TRANSIENT_KEY, $user_amount_by_member_lv, Utils::CACHE_TIME);
		return $user_amount_by_member_lv;
	}

	public function get_user_amount_by_member_lv(): array {
		$user_amount_by_member_lv = \get_transient(self::TRANSIENT_KEY);
		if (false === $user_amount_by_member_lv) {
			return $this->count_users_by_member_lv();
		}
		return $user_amount_by_member_lv;
	}

	public function set_users_column_titles( $columns ): array {
		// $columns['user_id'] = 'User ID';
		$order                                 = ( @$_REQUEST['ts_all'] == 'DESC' ) ? 'ASC' : 'DESC';
		$columns[ Utils::MEMBER_LV_POST_TYPE ] = '會員等級';
		$columns['total_order_amount']         = "<a title='用戶註冊後至今累積總消費金額' href='?ts_all={$order}'>全部</a>";

		for ($i = 0; $i < $this->order_history; $i++) {
			$order    = ( @$_REQUEST[ "ts{$i}" ] == 'DESC' ) ? 'ASC' : 'DESC';
			$the_date = date('Y年m', strtotime("-{$i} month"));
			// $month = current_time('m') - $i;
			$columns[ "ts{$i}" ] = "<a title='{$the_date} 月累積採購金額' href='?ts{$i}={$order}'>{$the_date} 月</a>";
		}

		return $columns;
	}

	public function set_users_column_values( $default_value, $column_name, $user_id ) {
		for ($i = 0; $i < $this->order_history; $i++) {
			if ($column_name == "ts{$i}") {
				$order_data = Utils::get_order_data_by_user_date($user_id, $i);

				if (!$order_data['user_is_registered']) {
					return '<span class="bg-gray-200 px-2 py-1 rounded-md text-xs">當時尚未註冊</span>';
				}
				$text = '';
				if (isset($order_data['goal'])) {
					switch ($order_data['goal']) {
						case 'no_goal':
							$text = '';
							break;
						case 'yes':
							$text = '<span class="bg-teal-200 px-2 py-1 rounded-md text-xs">達標<span>';
							break;
						case 'no':
							$text = '<span class="bg-red-200 px-2 py-1 rounded-md text-xs">不達標<span>';
							break;
						default:
							$text = '';
							break;
					}
				}
				$html = 'NT$ ' . $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆<br>' . $text;

				return $html;
			}
		}

		if ($column_name == 'total_order_amount') {
			$args       = [
				'numberposts' => -1,
				'meta_key'    => '_customer_user',
				'meta_value'  => $user_id,
				'post_type'   => [ 'shop_order' ],
				'post_status' => [ 'wc-completed', 'wc-processing' ],
			];
			$order_data = Utils::get_order_data_by_user_date($user_id, 0, $args);

			$html = 'NT$ ' . $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆';
			return $html;
		}

		if ($column_name == Utils::MEMBER_LV_POST_TYPE) {
			$value        = '';
			$member_lv_id = \gamipress_get_user_rank_id($user_id, Utils::MEMBER_LV_POST_TYPE);
			$value        = \get_the_title($member_lv_id);
			return $value;
		}

		return $default_value;
	}

	function render_member_filter_options( $which ): void {

		$member_lvs = get_posts(
			[
				'post_type'      => Utils::MEMBER_LV_POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
			);

		$get_member_lv            = $_GET[ Utils::MEMBER_LV_POST_TYPE ] ?? 0;
		$user_amount_by_member_lv = $this->get_user_amount_by_member_lv();

		?>
			<select name="<?php echo Utils::MEMBER_LV_POST_TYPE . '_' . $which; ?>" id="<?php echo Utils::MEMBER_LV_POST_TYPE . '_' . $which; ?>">
				<option value="0">篩選會員等級</option>
		<?php foreach ($member_lvs as $member_lv) : ?>
					<option value="<?php echo esc_attr($member_lv->ID); ?>" <?php selected($get_member_lv, $member_lv->ID); ?>><?php echo esc_html($member_lv->post_title) . ' (' . $user_amount_by_member_lv[ $member_lv->ID ] . ')'; ?></option>
				<?php endforeach; ?>
			</select>
		<?php
		\submit_button(\__( '篩選' ), null, $which, false);

		// 批次調整會員等級

		?>
		<select name="<?php echo 'set_' . Utils::MEMBER_LV_POST_TYPE . '_' . $which; ?>" id="<?php echo 'set_' . Utils::MEMBER_LV_POST_TYPE . '_' . $which; ?>">
			<option value="0">批次調整會員等級</option>
		<?php foreach ($member_lvs as $member_lv) : ?>
				<option value="<?php echo esc_attr($member_lv->ID); ?>" <?php selected($get_member_lv, $member_lv->ID); ?>><?php echo esc_html($member_lv->post_title) . ' (' . $user_amount_by_member_lv[ $member_lv->ID ] . ')'; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		\submit_button(\__( '批次調整會員等級' ), null, $which, false);
	}

	public function filter_users_by_member_lv( $query ): void {
		if (!is_admin()) {
			return;
		}
		global $pagenow;

		// 篩選會員等級

		$member_lv = $_GET[ Utils::MEMBER_LV_POST_TYPE . '_top' ] ?? $_GET[ Utils::MEMBER_LV_POST_TYPE . '_bottom' ] ?? 0;

		if ('users.php' === $pagenow && !!$member_lv) {
			$meta_query = [
				[
					'key'     => Utils::CURRENT_MEMBER_LV_META_KEY,
					'value'   => $member_lv,
					'compare' => '=',
				],
			];

			$query->set('meta_query', $meta_query);

		}

		// 批次調整會員等級

		$set_member_lv = $_GET[ 'set_' . Utils::MEMBER_LV_POST_TYPE . '_top' ] ?? $_GET[ 'set_' . Utils::MEMBER_LV_POST_TYPE . '_bottom' ] ?? 0;
		$user_ids      = $_GET['users'] ?? [];

		if ('users.php' === $pagenow && !!$set_member_lv && !!$user_ids) {
			foreach ($user_ids as $user_id) {
				update_user_meta($user_id, Utils::CURRENT_MEMBER_LV_META_KEY, $set_member_lv);
			}
		}
	}
}
