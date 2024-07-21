<?php
/**
 * Metabox
 */

declare( strict_types=1 );

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Plugin;
use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\WpUtils\Traits\SingletonTrait;


/**
 * Class Metabox
 */
final class Metabox {
	use SingletonTrait;

	const ACTION                            = 'pm_metabox';
	const THRESHOLD_META_KEY                = 'pm_threshold'; // 會員升級金額門檻
	const AWARD_POINTS_USER_BDAY_FIELD_NAME = 'pm_award_points_user_birthday'; // 會員生日購物金
	const LIMIT_TYPE_META_KEY               = 'pm_limit_type';
	const LIMIT_VALUE_META_KEY              = 'pm_limit_value';
	const LIMIT_UNIT_META_KEY               = 'pm_limit_unit';


	/**
	 * @var array
	 */
	public static $limit_type_options = [
		'unlimited' => '無期限',
		'fixed'     => '固定時間',
		'repeated'  => '重複時間', // TODO: 未來功能
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

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'add_meta_boxes', [ $this, 'add_metabox' ], 10, 1 );
		\add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 */
	public function add_metabox(): void {
		\add_meta_box(
			Plugin::$snake . '_metabox',
			'設定',
			[ $this, 'render_metabox' ],
			MemberLvInit::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render metabox
	 *
	 * @param \WP_Post $post Post.
	 *
	 * @return void
	 */
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
				'class'   => 'w-40',
				'label'   => '',
				'value'   => $limit_type,
				'options' => self::$limit_type_options,
			]
		);
		$limit_type_html = \ob_get_clean();

		$lt_fixed_html  = '<div data-type="fixed" class="flex gap-4 items-center">從最近';
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
		$lt_fixed_html .= '累積計算</div>';

		$lt_assigned_html = sprintf(
			/*html*/'
			<div data-type="assigned">
				從
				<input type="date" name="%1$s" value="%2$s" max="%3$s" />
				<input type="hidden" name="%4$s" value="date" />
				累積至今計算
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

					$("#pm_limit_type").change(function(e){
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
						const type = $("#pm_limit_type").val()
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

	/**
	 * Save metabox
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post.
	 *
	 * @return void
	 */
	public function save_metabox( $post_id, $post ): void {
		// Check if user has permissions to save data.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$all_point_fields = [];
		$all_points       = Plugin::instance()->point_service_instance->get_all_points();

		// 因為生日點數可能有多種不同的點數型態
		foreach ( $all_points as $point ) {
			$all_point_fields[] = self::AWARD_POINTS_USER_BDAY_FIELD_NAME . '_' . $point->slug;
		}

		$basic_fields = [
			self::THRESHOLD_META_KEY,
			self::LIMIT_TYPE_META_KEY,
			self::LIMIT_VALUE_META_KEY,
			self::LIMIT_UNIT_META_KEY,
		];

		$fields = array_merge( $basic_fields, $all_point_fields );

		foreach ( $fields as $field ) {
			// phpcs:disable
			if ( isset( $_POST[ $field ] ) ) {
				\update_post_meta( $post_id, $field, \sanitize_text_field( $_POST[ $field ] ) );
			}
			// phpcs:enable
		}
	}
}

Metabox::instance();
