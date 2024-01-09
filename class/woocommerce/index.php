<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce;

require_once __DIR__ . '/coupons/index.php';

class Init
{
	public function __construct()
	{
		new Coupons\Init();
	}
}
