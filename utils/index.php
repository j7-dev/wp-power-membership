<?php
declare (strict_types = 1);
namespace J7\PowerMembership;

class Utils
{
    const APP_NAME            = 'Power Membership';
    const KEBAB               = 'power-membership';
    const SNAKE               = 'power_membership';
    const DEFAULT_IMAGE       = 'http://1.gravatar.com/avatar/1c39955b5fe5ae1bf51a77642f052848?s=96&d=mm&r=g';
    const MEMBER_LV_POST_TYPE = 'member_lv';
    // const ORDER_META_KEY = 'pp_create_site_responses';

    // protected const API_URL            = 'https://cloud.luke.cafe';
    // protected const API_URL = 'http://luke.local';

    // protected const USER_NAME          = 'j7.dev.gg';
    // protected const PASSWORD           = 'YQLj xV2R js9p IWYB VWxp oL2E';
    // protected const TEMPLATE_SERVER_ID = 2202;
    // protected const TRANSIENT_KEY      = 'pp_cloud_sites';

    /**
     * 取得客戶訂單
     * 時間參考
     * @ref https://wisdmlabs.com/blog/query-posts-or-comments-by-date-time/
     */
    public function get_order_data_by_user_date($user_id, $months_ago = 0, $args = array())
    {
        $user      = get_userdata($user_id);
        $that_date = strtotime("first day of -" . $months_ago . " month", time());
        $that_date = strtotime("first day of +1 month", $that_date);

        $user_registed_time = strtotime($user->data->user_registered);
        $is_registered      = ($user_registed_time >= $that_date) ? false : true;

        $month = date('m', strtotime("-{$months_ago} months", time()));
        $year  = date('Y', strtotime("-{$months_ago} months", time()));

        if (empty($args)) {
            $args = array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => array('shop_order'),
                'post_status' => array('wc-completed', 'wc-processing'),
                'date_query'  => array(
                    'year'  => $year,
                    'month' => $month,
                ),
            );
        }
        $customer_orders = get_posts($args);
        $total           = 0;
        foreach ($customer_orders as $customer_order) {
            $order = wc_get_order($customer_order);
            $total += $order->get_total();
        }
        $order_data[ 'total' ]              = $total;
        $order_data[ 'order_num' ]          = count($customer_orders);
        $order_data[ 'user_is_registered' ] = $is_registered;

        return $order_data;
    }

    public static function get_plugin_dir(): string
    {
        $plugin_dir = \untrailingslashit(\wp_normalize_path(\plugin_dir_path(__DIR__ . '../')));
        return $plugin_dir;
    }

    public static function get_plugin_url(): string
    {
        $plugin_url = \untrailingslashit(\plugin_dir_url(self::get_plugin_dir() . '/plugin.php'));
        return $plugin_url;
    }

    public static function get_plugin_ver(): string
    {
        $plugin_data = \get_plugin_data(self::get_plugin_dir() . '/plugin.php');
        $plugin_ver  = $plugin_data[ 'Version' ];
        return $plugin_ver;
    }
}
