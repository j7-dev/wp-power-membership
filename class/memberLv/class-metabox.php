<?php

declare (strict_types = 1);
namespace J7\PowerMembership\MemberLv;

class Metabox extends \J7\PowerMembership\Utils
{

    public function __construct()
    {
        if (is_admin()) {
            add_action('load-post.php', [ $this, 'init_metabox' ]);
            add_action('load-post-new.php', [ $this, 'init_metabox' ]);
        }
    }

    public function init_metabox(): void
    {
        add_action('add_meta_boxes', [ $this, 'add_metabox' ]);
        add_action('save_post', [ $this, 'save_metabox' ], 10, 2);
    }

    public function add_metabox(): void
    {
        add_meta_box(
            Utils::MEMBER_LV_POST_TYPE . '_condition_metabox',
            '會員等級條件設定',
            array($this, 'render_metabox'),
            Utils::MEMBER_LV_POST_TYPE,
            'normal',
            'high'
        );
    }

    public function render_metabox($post)
    {
        ?>
<label for="threshold">會員升級門檻</label>
<input step="1000" min="0" value="500000" type="number" id="threshold" name="threshold" />
			<?php
// Add nonce for security and authentication.
        wp_nonce_field('custom_nonce_action', 'custom_nonce');
    }

    public function save_metabox($post_id, $post)
    {
        // Add nonce for security and authentication.
        $nonce_name   = isset($_POST[ 'custom_nonce' ]) ? $_POST[ 'custom_nonce' ] : '';
        $nonce_action = 'custom_nonce_action';

        // Check if nonce is valid.
        if (!wp_verify_nonce($nonce_name, $nonce_action)) {
            return;
        }

        // Check if user has permissions to save data.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if not an autosave.
        if (wp_is_post_autosave($post_id)) {
            return;
        }

        // Check if not a revision.
        if (wp_is_post_revision($post_id)) {
            return;
        }
    }

}

new Metabox();
