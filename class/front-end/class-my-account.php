<?php

declare(strict_types=1);

namespace J7\PowerMembership\FrontEnd;

use J7\PowerMembership\Utils;

/**
 * 前台 MyAccount 相關
 */

final class MyAccount
{

	public function __construct()
	{
		\add_filter('wc_get_template', [$this, 'override_wc_template'], 100, 5);
	}

	/**
	 * Override Woocommerce template
	 *
	 * @param string $located Located.
	 * @param string $template_name Template name.
	 * @param array  $args Args.
	 * @param string $template_path Template path.
	 * @param string $default_path Default path.
	 * @return string
	 */
	public function override_wc_template($located, $template_name, $args, $template_path, $default_path)
	{
		$plugin_template_file_path = Utils::get_plugin_dir() . "/templates/{$template_name}";

		if (file_exists($plugin_template_file_path)) {
			return $plugin_template_file_path;
		} else {
			return $located;
		}
	}
}

new MyAccount();
