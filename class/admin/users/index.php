<?php

declare(strict_types=1);

namespace J7\PowerMembership\Admin\Users;

require_once __DIR__ . '/class-user-columns.php';
require_once __DIR__ . '/class-user-edit.php';

class Init
{
	public function __construct()
	{
		new UserColumns();
		new UserEdit();
	}
}
