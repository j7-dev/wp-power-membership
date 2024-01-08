<?php

declare (strict_types = 1);
namespace J7\PowerMembership\WooCommerce\Coupons\View;

use J7\PowerMembership\Utils;

class View
{
    public $show_one_coupon_only = true; // 隱藏小的 coupons，只顯示一個金額較大的 coupon

    public function __construct()
    {
        \add_action('wp_enqueue_scripts', [ $this, 'handle_coupon_enqueue' ]);
        \add_action('woocommerce_before_checkout_form', [ $this, 'show_available_coupons' ]);
    }

    public function handle_coupon_enqueue(): void
    {
        if (is_checkout()) {
            wp_enqueue_script('handle-coupon', Utils::get_plugin_url() . '/assets/js/handle-coupon.js', array('wc-checkout'), Utils::get_plugin_ver(), true);
        }
    }

    public function show_available_coupons()
    {

        $coupons = $this->get_coupons(); //取得網站一般優惠
        ?>
  <style>
    .list-group~.woocommerce-message {
      display: none !important;
    }
  </style>
  <?php if (!empty($coupons)):

            $coupons = $this->handle_show_one_coupon_only($coupons);

            ?>
																    <h2 class="">消費滿額折扣</h2>
																    <div class="list-group mb-2" style="border-radius: 5px;">
																      <?php foreach ($coupons as $coupon):
                $props = $this->get_coupon_props($coupon);

                ?>
																																        <label class="list-group-item list-group-item-action <?=$props[ 'disabled_bg' ]?>">
																																          <input data-type="normal_coupon" id="coupon-<?=$coupon->ID;?>" name="yf_normal_coupon" class="form-check-input me-1 normal_coupon" type="radio" value="<?=$coupon->post_title;?>" <?=$props[ 'disabled' ]?>>
																																          <?=$coupon->post_title . $coupon->post_excerpt . $props[ 'reason' ];?>
																																        </label>
																																      <?php endforeach;?>
																    </div>
																  <?php endif;?>
<?php
}

    public function get_coupons($type = 'normal')
    {

        $coupon_posts_without_minimum_amount = get_posts(array(
            'posts_per_page' => -1,
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation'                 => 'AND',
                'minimum_amount_clause'    => array(
                    'key'     => 'minimum_amount',
                    'compare' => 'NOT EXISTS',
                ),
                'reuqire_yf_reward_clause' => array(
                    'key'   => 'coupon_type',
                    'value' => $type,
                ),
            ),
        ));

        $coupon_posts_with_minimum_amount = get_posts(array(
            'posts_per_page' => -1,
            'meta_key'       => 'minimum_amount',
            'orderby'        => ($type === 'required_reward') ? [ 'meta_value_num', 'ID' ] : 'meta_value_num',
            'order'          => 'ASC',
            'post_type'      => 'shop_coupon',
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation'                 => 'AND',
                'minimum_amount_clause'    => array(
                    'key'     => 'minimum_amount',
                    'compare' => 'EXISTS',
                ),
                'reuqire_yf_reward_clause' => array(
                    'key'   => 'coupon_type',
                    'value' => $type,
                ),
            ),
        ));

        $coupon_posts = array_merge($coupon_posts_without_minimum_amount, $coupon_posts_with_minimum_amount);

        return $coupon_posts;

    }

    /**
     * 隱藏小的coupon
     * 只出現大的coupon
     */
    public function handle_show_one_coupon_only($coupons)
    {
        if (!$this->show_one_coupon_only) {
            return $coupons;
        }
        $cart_total = (int) WC()->cart->subtotal;

        foreach ($coupons as $key => $coupon) {
            $minimum_amount = (int) get_post_meta($coupon->ID, 'minimum_amount', true);
            // $minimum_amount = !empty($minimum_amount) ? $minimum_amount : 0;
            if ($cart_total >= $minimum_amount) {
                $meet[ $coupon->ID ] = abs($cart_total - $minimum_amount);
            } else {
                $not_meet[ $coupon->ID ] = abs($cart_total - $minimum_amount);
            }
        }
        $meet     = !empty($meet) ? $meet : [  ];
        $not_meet = !empty($not_meet) ? $not_meet : [  ];
        asort($meet); //from small to big
        asort($not_meet); // from small to big

        $biggest_coupon = array_slice($meet, 0, 1, true);
        $keys           = array_keys($biggest_coupon + $not_meet);
        foreach ($coupons as $key => $coupon) {
            if (!in_array($coupon->ID, $keys)) {
                unset($coupons[ $key ]);
            }
        }

        return $coupons;
    }

    public function get_coupon_props($coupon)
    {
        $cart_total     = (int) WC()->cart->subtotal;
        $coupon_amount  = (int) get_post_meta($coupon->ID, 'coupon_amount', true);
        $minimum_amount = (int) get_post_meta($coupon->ID, 'minimum_amount', true);

        $props = [  ];
        if ($cart_total < $minimum_amount) {

            $d                       = $minimum_amount - $cart_total;
            $shop_url                = site_url('shop');
            $props[ 'is_available' ] = false;
            $props[ 'reason' ]       = "，<span class='text-danger'>還差 ${d} 元</span>，<a href='${shop_url}'>再去多買幾件 》</a>";
            $props[ 'disabled' ]     = "disabled";
            $props[ 'disabled_bg' ]  = "bg-light cursor-not-allowed";
            return $props;
        } else {
            $props[ 'is_available' ] = true;
            $props[ 'reason' ]       = "";
            $props[ 'disabled' ]     = "";
            $props[ 'disabled_bg' ]  = "";
            return $props;
        }
    }
}

new View();
