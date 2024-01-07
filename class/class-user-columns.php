<?php

declare (strict_types = 1);
namespace J7\PowerMembership;

class UserColumns extends Utils
{

    public $order_history = 4; // 秀幾個月前的訂單金額

    public function __construct()
    {
        //設定欄位標題
        \add_filter('manage_users_columns', [ $this, 'set_custom_edit_users_columns' ], 10, 1);
        //設定欄位值
        \add_filter('manage_users_custom_column', [ $this, 'custom_users_column' ], 10, 3);

        //排序
        \add_filter('users_list_table_query_args', function ($args) {
            if (isset($_REQUEST[ 'ts_all' ])) {
                $args[ 'orderby' ]  = 'meta_value_num';
                $args[ 'meta_key' ] = '_total_sales_in_life';
                $args[ 'order' ]    = $_REQUEST[ 'ts_all' ];
                return $args;
            }
            for ($i = 0; $i < 3; $i++) {
                if (isset($_REQUEST[ "ts{$i}" ])) {
                    $args[ 'orderby' ]  = 'meta_value_num';
                    $args[ 'meta_key' ] = '_total_sales_in_' . $i . '_months_ago';
                    $args[ 'order' ]    = $_REQUEST[ "ts{$i}" ];
                }
            }
            return $args;
        }, 10, 1);
    }

    public function set_custom_edit_users_columns($columns)
    {
        //$columns['user_id'] = 'User ID';
        $order                           = (@$_REQUEST[ 'ts_all' ] == 'DESC') ? 'ASC' : 'DESC';
        $columns[ 'total_order_amount' ] = "<a title='用戶註冊後至今累積總消費金額' href='?ts_all={$order}'>全部</a>";

        for ($i = 0; $i < $this->order_history; $i++) {
            $order    = (@$_REQUEST[ "ts{$i}" ] == 'DESC') ? 'ASC' : 'DESC';
            $the_date = date('Y年m', strtotime("-{$i} month"));
            //$month = current_time('m') - $i;
            $columns[ "ts{$i}" ] = "<a title='{$the_date} 月累積採購金額' href='?ts{$i}={$order}'>{$the_date} 月</a>";
        }

        return $columns;
    }

    public function custom_users_column($default_value, $column_name, $user_id)
    {
        for ($i = 0; $i < $this->order_history; $i++) {
            if ($column_name == "ts{$i}") {
                $order_data = $this->get_order_data_by_user_date($user_id, $i);

                if (!$order_data[ 'user_is_registered' ]) {
                    return '<span class="bg-gray-200 px-2 py-1 rounded-md text-xs">當時尚未註冊</span>';
                }
                $text = '';
                if (isset($order_data[ 'goal' ])) {
                    switch ($order_data[ 'goal' ]) {
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
                    };
                }
                $html = 'NT$ ' . $order_data[ 'total' ] . '<br>訂單' . $order_data[ 'order_num' ] . '筆<br>' . $text;

                return $html;
            }
        }

        if ($column_name == 'total_order_amount') {
            $args = array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => array('shop_order'),
                'post_status' => array('wc-completed', 'wc-processing'),
            );
            $order_data = $this->get_order_data_by_user_date($user_id, 0, $args);

            $html = 'NT$ ' . $order_data[ 'total' ] . '<br>訂單' . $order_data[ 'order_num' ] . '筆';
            return $html;
        }

    }
}

new UserColumns();
