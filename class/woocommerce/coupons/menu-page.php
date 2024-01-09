<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;

final class MenuPage
{

	public function __construct()
	{
		\add_action('admin_menu', [$this, 'add_coupon_submenu']);
	}

	public function add_coupon_submenu(): void
	{
		\add_submenu_page(
			'woocommerce-marketing',
			'設定',
			'設定',
			'manage_options',
			Utils::KEBAB . '-coupons-setting',
			[$this, 'add_coupon_submenu_callback']
		);
	}

	public function add_coupon_submenu_callback(): void
	{
?>
		<div class="wrap">
			<h1><?= Utils::APP_NAME ?> 折價券設定</h1>
			<form action="">
				<label for="">設定</label>
			</form>
			<p>Some content</p>
		</div>
<?php
	}
}
