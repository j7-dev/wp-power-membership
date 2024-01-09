<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

require_once __DIR__ . '/metabox.php';
require_once __DIR__ . '/view.php';

class Init
{
	public function __construct()
	{
		new Metabox();
		new View();
	}
}
