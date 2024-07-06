<?php

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils;


final class Metabox {

	public static $default_member_lv_id;
	const ACTION               = Utils::SNAKE . '_metabox';
	const THRESHOLD_META_KEY   = Utils::SNAKE . '_threshold';
	const LIMIT_TYPE_META_KEY  = Utils::SNAKE . '_limit_type';
	const LIMIT_VALUE_META_KEY = Utils::SNAKE . '_limit_value';
	const LIMIT_UNIT_META_KEY  = Utils::SNAKE . '_limit_unit';

	/**
	 * @var array
	 */
	public static $limit_type_options = [
		'unlimited' => '無期限',
		'fixed'     => '固定時間',
		'assigned'  => '指定時間',
	];

	/**
	 * @var array
	 */
	public static $limit_unit_options = [
		'day'   => '日',
		'month' => '月',
		'year'  => '年',
	];

	public function __construct() {
		\add_action('add_meta_boxes', [ $this, 'add_metabox' ], 10);
		\add_action('save_post', [ $this, 'save_metabox' ], 10, 2);
		\add_action('init', [ $this, 'create_default_member_lv' ], 30);
	}

	public function add_metabox(): void {
		\add_meta_box(
			Utils::SNAKE . '_metabox',
			'會員升級門檻',
			[ $this, 'render_metabox' ],
			Utils::MEMBER_LV_POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_metabox( $post ): void {
		$threshold   = \get_post_meta($post->ID, self::THRESHOLD_META_KEY, true);
		$threshold   = empty($threshold) ? 0 : (int) $threshold;
		$limit_type  = \get_post_meta($post->ID, self::LIMIT_TYPE_META_KEY, true);
		$limit_value = \get_post_meta($post->ID, self::LIMIT_VALUE_META_KEY, true);
		$limit_unit  = \get_post_meta($post->ID, self::LIMIT_UNIT_META_KEY, true);

		\ob_start();
		\woocommerce_wp_select(
			[
				'id'      => self::LIMIT_TYPE_META_KEY,
				'name'    =>self::LIMIT_TYPE_META_KEY,
				'class'   => 'w-36',
				'label'   => '',
				'value'   => $limit_type,
				'options' => self::$limit_type_options,
			]
		);
		$limit_type_html = \ob_get_clean();

		$lt_fixed_html  = '<div data-type="fixed" class="flex gap-4">';
		$lt_fixed_html .= sprintf(
		/*html*/'<input type="number" name="%1$s" value="%2$s"  min="0" step="1" class="w-20" />',
		self::LIMIT_VALUE_META_KEY,
		$limit_value
		);

		\ob_start();
		\woocommerce_wp_select(
			[
				'id'      => self::LIMIT_UNIT_META_KEY,
				'name'    =>self::LIMIT_UNIT_META_KEY,
				'class'   => 'w-12',
				'label'   => '',
				'value'   => $limit_unit,
				'options' => self::$limit_unit_options,
			]
		);
		$lt_fixed_html .= \ob_get_clean();
		$lt_fixed_html .= '</div>';

		$lt_assigned_html = sprintf(
			/*html*/'
			<div data-type="assigned">
				從
				<input type="date" name="%1$s" value="%2$s" max="%3$s" />
				<input type="hidden" name="%4$s" value="date" />
				統計至今
			</div>
			',
			self::LIMIT_VALUE_META_KEY,
			$limit_value,
			\wp_date('Y-m-d'),
			self::LIMIT_UNIT_META_KEY
			);

		$js = sprintf(
				/*html*/'
				<script>
				(function($){
					// copy dom
					const fixedHtml = $("div[data-type=\"fixed\"]").clone()
					const assignedHtml = $("div[data-type=\"assigned\"]").clone()
					const wrapper = $("#input-wrapper")
					init()

					$("#power_membership_limit_type").change(function(e){
							const value = e.target.value
							render(value)
					});

					function render(value){
						if("unlimited" === value){
								$("#pm div[data-type]").remove()
							}else if("fixed" === value){
								$("#pm div[data-type]").remove()
								wrapper.append(fixedHtml)
							}else if("assigned" === value){
								$("#pm div[data-type]").remove()
								wrapper.append(assignedHtml)
							}
					}

					function init(){
						$("#pm div[data-type]").remove()
						const type = $("#power_membership_limit_type").val()
						console.log(type)
						render(type)
					}
				})(jQuery)
				</script>
				'
				);

		printf(
		/*html*/'
		<div id="pm" class="grid grid-cols-[14rem_minmax(30rem,_1fr)] gap-y-4 w-fit">
			<label for="%1$s" class="block">會員累積消費升級門檻(NT$)</label>
			<input type="number" value="%2$d" name="%1$s" min="0" step="1" />
			<label for="%3$s" class="w-[14rem] block">計算累積消費期限</label>
			<div id="input-wrapper" class="flex items-center gap-x-2">
				%4$s
				%5$s
				%6$s
			</div>
		</div>
		%7$s
		',
		self::THRESHOLD_META_KEY,
		$threshold,
		self::LIMIT_TYPE_META_KEY,
		$limit_type_html,
		$lt_fixed_html,
		$lt_assigned_html,
		$js
		);
	}

	public function save_metabox( $post_id, $post ): void {
		// Check if user has permissions to save data.
		if (!\current_user_can('edit_post', $post_id)) {
			return;
		}

		$fields = [
			self::THRESHOLD_META_KEY,
			self::LIMIT_TYPE_META_KEY,
			self::LIMIT_VALUE_META_KEY,
			self::LIMIT_UNIT_META_KEY,
		];

		foreach ($fields as $field) {
			if (isset($_POST[ $field ])) {
				\update_post_meta($post_id, $field, \sanitize_text_field($_POST[ $field ]));
			}
		}
	}



	private function create_member_lv_post_type(): void {
		$post_type = Utils::MEMBER_LV_POST_TYPE;
		if (\post_type_exists($post_type)) {
			return;
		}

		// create member_lv Rank Type in Gamipress
		\wp_insert_post(
			[
				'post_title'  => '會員等級',
				'post_type'   => 'rank-type',
				'post_status' => 'publish',
				'post_name'   => Utils::MEMBER_LV_POST_TYPE,
				'meta_input'  => [
					'_gamipress_plural_name' => '會員等級',
				],
			]
		);
	}

	public function create_default_member_lv(): void {
		$post_type = Utils::MEMBER_LV_POST_TYPE;
		if (!\post_type_exists($post_type)) {
			$this->create_member_lv_post_type();
		}

		$slug = 'default';

		$page = get_page_by_path($slug, OBJECT, $post_type);
		if ($page) {
			self::$default_member_lv_id = $page->ID;
			return;
		} else {
			// create default member_lv
			$post_id                    = \wp_insert_post(
				[
					'post_title'  => '預設會員',
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'post_name'   => $slug,
					'meta_input'  => [
						self::THRESHOLD_META_KEY => '0',
					],
				]
			);
			self::$default_member_lv_id = $post_id;
			$this->set_all_users_default_member_lv($post_id);
		}
	}

	private function set_all_users_default_member_lv( int $default_member_lv_id ): void {
		global $wpdb;
		$prefix = $wpdb->prefix;
		// get all user ids
		$user_ids = $wpdb->get_col("SELECT ID FROM {$prefix}users");

		foreach ($user_ids as $user_id) {
			$member_lv = \get_user_meta($user_id, Utils::CURRENT_MEMBER_LV_META_KEY);
			if (empty($member_lv)) {
				\update_user_meta($user_id, Utils::CURRENT_MEMBER_LV_META_KEY, $default_member_lv_id);
			}
		}
	}
}
