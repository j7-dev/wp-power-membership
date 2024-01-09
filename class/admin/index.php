<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin;

require_once __DIR__ . '/ui/index.php';
require_once __DIR__ . '/users/index.php';

class Init
{
	public function __construct()
	{
		new UI();
		new Users\Init();
	}
}
