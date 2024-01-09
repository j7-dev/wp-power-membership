<?php

declare(strict_types=1);

namespace J7\PowerMembership\MemberLv;

require_once __DIR__ . '/class-metabox.php';
require_once __DIR__ . '/class-membership-upgrade.php';

class Init
{
	public function __construct()
	{
		new Metabox();
		new MembershipUpgrade();
	}
}
