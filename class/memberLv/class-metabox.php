<?php

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

use J7\PowerMembership\Utils;

class Metabox
{

	const ACTION             = Utils::SNAKE . '_metabox';
	const THRESHOLD_META_KEY = 'threshold';

	public function __construct()
	{
		\add_action('add_meta_boxes', [$this, 'add_metabox'], 100);
		\add_action('save_post', [$this, 'save_metabox'], 100, 2);
	}

	public function add_metabox()
	{
		add_meta_box(
			Utils::SNAKE . '_metabox',
			'會員升級門檻',
			[$this, 'render_metabox'],
			Utils::MEMBER_LV_POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render_metabox($post)
	{
?>
		<div class="tw-flex tw-items-center">
			<label for="<?= self::THRESHOLD_META_KEY ?>" class="tw-w-[14rem] tw-block">會員累積消費升級門檻(NT$)</label>
			<input type="number" name="<?= self::THRESHOLD_META_KEY ?>" min="0" step="1000" class="tw-ml-8" />
		</div>
<?php
	}

	public function save_metabox($post_id, $post)
	{

		// Check if user has permissions to save data.
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		$value = isset($_POST['threshold']) ? \sanitize_text_field($_POST['threshold']) : 0;
		$value = is_numeric($value) ? $value : 0;
		\update_post_meta($post_id, self::THRESHOLD_META_KEY, $value);
	}
}
