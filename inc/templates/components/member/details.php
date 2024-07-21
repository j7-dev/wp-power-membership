<?php
/**
 * 顯示會員等級相關資訊
 */

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\PowerMembership\Resources\MemberLv\Utils as MemberLvUtils;
use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Plugin;


$default_args = [
	'user_id' => \get_current_user_id(),
];

$args = \wp_parse_args( $args, $default_args );

[
	'user_id' => $user_id,
] = $args;

$user = \get_user_by( 'ID', $user_id );

/**
 * 顯示會員等級
 */

$current_member_lv = MemberLvUtils::get_member_lv_by('user_id', $user_id);

if (! $current_member_lv) {
	$current_member_lv_id = \get_user_meta( $user_id, MemberLvInit::POST_TYPE, true ) ?: MemberLvInit::instance()->default_member_lv_id;
	echo "找不到 ID:{$current_member_lv} 的會員等級";
	return;
}

$current_member_lv_img_url = $current_member_lv->img_url;
$next_member_lv            = MemberLvUtils::get_next_member_lv_by( 'user_id', $user_id );

$next_rank_html = '您已是最高等級';
if ($next_member_lv) {
	$next_threshold        = (float) $next_member_lv->threshold;
	$next_threshold_html   = \wc_price( $next_threshold );
	$next_limit_type       = $next_member_lv->limit_type;
	$next_limit_value      = $next_member_lv->limit_value;
	$next_limit_unit       = $next_member_lv->limit_unit;
	$next_limit_unit_label = match ( $next_limit_unit ) {
		'month' => '月',
		'year' => '年',
		default => '日'
	};
	$next_member_lv_img_url = $next_member_lv->img_url;

	// condition_html
	$condition_html = $next_member_lv ? sprintf(
	/*html*/    '需要%1$s累積消費 %2$s 元',
	match ( $next_limit_type ) {
		'fixed' => "在最近 {$next_limit_value} {$next_limit_unit_label} 內，",
		'assigned' => "在 {$next_limit_value} 以前，",
		default => ''
	},
	$next_threshold_html,
	) : '';

	// current_condition_html
	$timestamp  = Base::calc_timestamp( $next_limit_type, $next_limit_value, $next_limit_unit );
	$order_data = Base::get_order_data_by_timestamp( $user_id, $timestamp );
	$acc_amount = (int) $order_data['total'];
	$diff       = (int) $next_threshold - $acc_amount;
	$diff_html  = \wc_price( $diff );

	$current_condition_html = $next_member_lv ? sprintf(
		/*html*/    '目前已累積 %1$s 元%2$s',
	\wc_price( $acc_amount ) . " (合計 {$order_data['order_num']} 筆訂單) ",
	$diff > 0 ? "，還差 {$diff_html} 元" : '',
	) : '';


	$next_rank_html = sprintf(
	/*html*/'
								<div><span class="">下個等級</span></div>
								<div>%1$s</div>' .
								( $next_threshold ? /*html*/'
									<div>
										<span class="">升級條件</span>
									</div>
									<div>%2$s</div>
									<div> <span class="">目前累積</span></div>
									<div>%3$s</div>
								' : '' ),
								Plugin::get(
									'member/tag',
									[
										'member_lv' => $next_member_lv,
									],
									false
									),
	$condition_html,
	$current_condition_html
	);
}

$html = sprintf(
	/*html*/'
	<div><span class="">會員等級</span></div>
	<div>%1$s</div>
',
Plugin::get('member/tag', null, false),
);

$html .= $next_rank_html;

$html .= sprintf(
	/*html*/'
	<div><span class="">會籍到期日</span></div>
	<div>%1$s</div>
	<div><span class="">加入日期</span></div>
	<div>%2$s</div>
',
'2025-12-31', // TODO
$user->user_registered,
);

echo '<div class="grid-table  grid grid-cols-[10rem_1fr] mt-8 mb-16">';
echo $html;
echo '</div>';
