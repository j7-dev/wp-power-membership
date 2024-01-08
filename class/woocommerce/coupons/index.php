<?php

declare (strict_types = 1);
namespace J7\PowerMembership\WooCommerce;

use J7\PowerMembership\Utils;

class Coupons
{
    const SELECT_FIELD_NAME = 'allowed_membership_ids';

    public function __construct()
    {
        \add_action('woocommerce_coupon_options_usage_restriction', [ $this, 'add_fields' ], 10, 2);
        \add_action('woocommerce_coupon_options_save', [ $this, 'update_fields' ], 10, 2);
    }

    public function add_fields($coupon_id, $coupon): void
    {
        ?>
					<p class="form-field">
					<label for="<?=self::SELECT_FIELD_NAME?>"><?php _e('允許的會員等級', Utils::SNAKE);?></label>
					<select id="<?=self::SELECT_FIELD_NAME?>" name="<?=self::SELECT_FIELD_NAME . '[]'?>" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e('無須會員等級', Utils::SNAKE);?>">
						<?php
$member_lvs = gamipress_get_ranks([
            'post_type' => Utils::MEMBER_LV_POST_TYPE,
         ]);
        $member_lv_ids = $coupon->get_meta(self::SELECT_FIELD_NAME);
        $member_lv_ids = is_array($member_lv_ids) ? $member_lv_ids : [  ];

        if ($member_lvs) {
            foreach ($member_lvs as $member_lv) {
                echo '<option value="' . esc_attr($member_lv->ID) . '"' . wc_selected($member_lv->ID, $member_lv_ids) . '>' . esc_html($member_lv->post_title) . '</option>';
            }
        }
        ?>
					</select>
					<?php echo wc_help_tip(__('只有指定的會員等級才可以使用此優惠', Utils::SNAKE)); ?>
				</p>
			<?php
}

    public function update_fields($coupon_id, $coupon): void
    {
        $allowed_membership_ids = isset($_POST[ self::SELECT_FIELD_NAME ]) ? (array) $_POST[ self::SELECT_FIELD_NAME ] : array();

        $coupon->update_meta_data(self::SELECT_FIELD_NAME, $allowed_membership_ids);
        $coupon->save();

    }
}

new Coupons();