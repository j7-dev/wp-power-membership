<?php
/**
 * 用戶欄位
 */

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

use J7\PowerMembership\Utils\Base;

/**
 * 用戶欄位
 * TODO 排序好像沒有作用
 */
final class UserColumns {
	use \J7\WpUtils\Traits\SingletonTrait;

	const TRANSIENT_KEY = 'user_amount_by_member_lv';
	/**
	 * 秀幾個月前的訂單金額
	 *
	 * @var int
	 */
	private $order_history = 4;

	/**
	 * 建構子
	 */
	public function __construct() {
		// 設定欄位標題
		\add_filter('manage_users_columns', [ $this, 'set_users_column_titles' ], 10, 1);
		// 設定欄位值
		\add_filter('manage_users_custom_column', [ $this, 'set_users_column_values' ], 10, 3);

		// 排序
		\add_filter(
			'users_list_table_query_args',
			function ( $args ) {
				if (isset($_REQUEST['ts_all'])) { // phpcs:ignore
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_total_sales_in_life';
					$args['order']    = $_REQUEST['ts_all']; //phpcs:ignore
					return $args;
				}
				for ($i = 0; $i < 3; $i++) {
					if (isset($_REQUEST[ "ts{$i}" ])) {
						$args['orderby']  = 'meta_value_num';
						$args['meta_key'] = '_total_sales_in_' . $i . '_months_ago';
						$args['order']    = $_REQUEST[ "ts{$i}" ]; //phpcs:ignore
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

	/**
	 * 計算用戶數量
	 *
	 * @return array
	 */
	private function count_users_by_member_lv(): array {
		global $wpdb;
		$table_name               = $wpdb->prefix . 'usermeta';
		$meta_key                 = Base::CURRENT_MEMBER_LV_META_KEY;
		$user_amount_by_member_lv = [];

		$member_lvs = \get_posts(
			[
				'post_type'      => Base::MEMBER_LV_POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
			);
		foreach ($member_lvs as $member_lv) {
			$meta_value = $member_lv->ID;
			// phpcs:disable
			$query = $wpdb->prepare(
				"
						SELECT COUNT(DISTINCT user_id) as user_count
						FROM $table_name
						WHERE meta_key = %s AND meta_value = %s
						",
				$meta_key,
				$meta_value
				);
			// phpcs:enable
			// 执行查询
			$result = $wpdb->get_row($query); //phpcs:ignore

			// 获取结果
			$user_count = $result->user_count;

			$user_amount_by_member_lv[ $member_lv->ID ] = $user_count;
		}

		\set_transient(self::TRANSIENT_KEY, $user_amount_by_member_lv, Base::CACHE_TIME);
		return $user_amount_by_member_lv;
	}

	/**
	 * 取得用戶數量
	 *
	 * @return array
	 */
	public function get_user_amount_by_member_lv(): array {
		$user_amount_by_member_lv = \get_transient(self::TRANSIENT_KEY);
		if (false === $user_amount_by_member_lv) {
			return $this->count_users_by_member_lv();
		}
		return $user_amount_by_member_lv;
	}

	/**
	 * 設定用戶欄位標題
	 *
	 * @param array $columns 欄位
	 * @return array
	 */
	public function set_users_column_titles( $columns ): array {
		// $columns['user_id'] = 'User ID';
		$order                                = ( @$_REQUEST['ts_all'] == 'DESC' ) ? 'ASC' : 'DESC'; // phpcs:ignore
		$columns[ Base::MEMBER_LV_POST_TYPE ] = '會員等級';
		$columns['total_order_amount']        = "<a title='用戶註冊後至今累積總消費金額' href='?ts_all={$order}'>全部</a>";

		for ($i = 0; $i < $this->order_history; $i++) {
			$order    = ( @$_REQUEST[ "ts{$i}" ] == 'DESC' ) ? 'ASC' : 'DESC'; // phpcs:ignore
			$the_date = date('Y年m', strtotime("-{$i} month"));
			// $month = current_time('m') - $i;
			$columns[ "ts{$i}" ] = "<a title='{$the_date} 月累積採購金額' href='?ts{$i}={$order}'>{$the_date} 月</a>";
		}

		return $columns;
	}

	/**
	 * 設定用戶欄位值
	 *
	 * @param string $default_value 預設值
	 * @param string $column_name 欄位名稱
	 * @param int    $user_id 用戶 ID
	 * @return string
	 */
	public function set_users_column_values( $default_value, $column_name, $user_id ) {
		for ($i = 0; $i < $this->order_history; $i++) {
			if ($column_name == "ts{$i}") {
				$order_data = Base::get_order_data_by_user_date($user_id, $i);

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
				$html = $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆<br>' . $text;

				return $html;
			}
		}

		if ($column_name == 'total_order_amount') {
			$args       = [
				'limit'       => -1,
				'customer_id' => $user_id,
				'status'      => [ 'wc-completed', 'wc-processing' ],
			];
			$order_data = Base::get_order_data_by_user_date($user_id, 0, $args);

			$html = $order_data['total'] . '<br>訂單' . $order_data['order_num'] . '筆';
			return $html;
		}

		if ($column_name == Base::MEMBER_LV_POST_TYPE) {
			$value        = '';
			$member_lv_id = \gamipress_get_user_rank_id($user_id, Base::MEMBER_LV_POST_TYPE);
			$value        = \get_the_title($member_lv_id);
			return $value;
		}

		return $default_value;
	}

	/**
	 * 渲染會員等級篩選選單
	 */
	public function render_member_filter_options(): void {
		$member_lvs = get_posts(
			[
				'post_type'      => Base::MEMBER_LV_POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
			);

		$get_member_lv            = $_GET[ Base::MEMBER_LV_POST_TYPE ] ?? 0; // phpcs:ignore
		$user_amount_by_member_lv = $this->get_user_amount_by_member_lv();

		?>

		<form method="GET">
			<select name="member_lv">
				<option value="0">篩選會員等級</option>
		<?php foreach ($member_lvs as $member_lv) : ?>
					<option value="<?php echo esc_attr($member_lv->ID); ?>" <?php selected($get_member_lv, $member_lv->ID); ?>><?php echo esc_html($member_lv->post_title) . ' (' . $user_amount_by_member_lv[ $member_lv->ID ] . ')'; ?></option>
				<?php endforeach; ?>
			</select>

			<input type="submit" class="button" value="篩選">

		</form>

		<?php
	}

	/**
	 * 篩選用戶
	 *
	 * @param \WP_Query $query 查詢
	 */
	public function filter_users_by_member_lv( $query ): void {
		if (!is_admin()) {
			return;
		}
		global $pagenow;
		if ('users.php' === $pagenow) {
			if (!empty($_GET[ Base::MEMBER_LV_POST_TYPE ])) {
				$value      = $_GET[ Base::MEMBER_LV_POST_TYPE ]; //phpcs:ignore
				$meta_query = [
					[
						'key'     => Base::CURRENT_MEMBER_LV_META_KEY,
						'value'   => $value,
						'compare' => '=',
					],
				];

				$query->set('meta_query', $meta_query);
			}
		}
	}
}
