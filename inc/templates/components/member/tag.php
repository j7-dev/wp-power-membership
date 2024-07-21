<?php
/**
 * 顯示會員等級 TAG
 */

use J7\PowerMembership\Resources\MemberLv\Utils as MemberLvUtils;

$default_args = [
	'member_lv' => MemberLvUtils::get_member_lv_by('user_id', \get_current_user_id()),
	'show_img'  => true,
];

$args = \wp_parse_args( $args, $default_args );

[
	'member_lv' => $member_lv,
	'show_img' => $show_img,
] = $args;

printf(
/*html*/'
<div class="flex gap-x-4 items-center">
		%1$s
		<span class="text-white bg-[#fb7258] rounded-xl text-xs px-3 py-1">%2$s</span>
</div>
',
( $member_lv->img_url && $show_img ) ? "<img src='{$member_lv->img_url}' alt='{$member_lv->name}' class='w-6 h-6 rounded-full'>" : '',
$member_lv->name,
);
