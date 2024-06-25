<?php

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils;


final class Metabox
{
	public static $default_member_lv_id;
	const ACTION             = Utils::SNAKE . '_metabox';
	const THRESHOLD_META_KEY = Utils::SNAKE . '_threshold';

	public function __construct()
	{
		\add_action('add_meta_boxes', [$this, 'add_metabox'], 10);
		\add_action('save_post', [$this, 'save_metabox'], 10, 2);
		\add_action('init', array($this, 'create_default_member_lv'), 30);
	}

	public function add_metabox(): void
	{
		\add_meta_box(
			Utils::SNAKE . '_metabox',
			'會員升級門檻',
			[$this, 'render_metabox'],
			Utils::MEMBER_LV_POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_metabox($post): void
	{
		$threshold = \get_post_meta($post->ID, self::THRESHOLD_META_KEY, true);
		$threshold = empty($threshold) ? 0 : (int) $threshold;
?>
		<div class="tailwindcss">
			<div class="tw-flex tw-items-center">
				<label for="<?= self::THRESHOLD_META_KEY ?>" class="tw-w-[14rem] tw-block">會員累積消費升級門檻(NT$)</label>
				<input type="number" value="<?= $threshold ?>" name="<?= self::THRESHOLD_META_KEY ?>" min="0" step="1000" class="tw-ml-8" />
			</div>
		</div>
<?php
	}

	public function save_metabox($post_id, $post): void
	{
		// Check if user has permissions to save data.
		if (!\current_user_can('edit_post', $post_id)) {
			return;
		}
		$value = isset($_POST[self::THRESHOLD_META_KEY]) ? \sanitize_text_field($_POST[self::THRESHOLD_META_KEY]) : 0;
		$value = is_numeric($value) ? $value : 0;
		\update_post_meta($post_id, self::THRESHOLD_META_KEY, $value);
	}



	private function create_member_lv_post_type(): void
	{
		$post_type = Utils::MEMBER_LV_POST_TYPE;
		if (\post_type_exists($post_type)) {
			return;
		}

		// create member_lv Rank Type in Gamipress
		\wp_insert_post(
			array(
				'post_title'  => '會員等級',
				'post_type'   => 'rank-type',
				'post_status' => 'publish',
				'post_name'   => Utils::MEMBER_LV_POST_TYPE,
				'meta_input'  => array(
					'_gamipress_plural_name' => '會員等級',
				),
			)
		);
	}

	public function create_default_member_lv(): void
	{
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
			$post_id = \wp_insert_post(
				array(
					'post_title'  => '預設會員',
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'post_name'   => $slug,
					'meta_input'  => array(
						self::THRESHOLD_META_KEY => '0',
					),
				)
			);
			self::$default_member_lv_id = $post_id;
			$this->set_all_users_default_member_lv($post_id);
		}
	}

	private function set_all_users_default_member_lv(int $default_member_lv_id): void
	{
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
