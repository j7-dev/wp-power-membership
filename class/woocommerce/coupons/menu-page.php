<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;
use J7\WpToolkit\Option;

final class MenuPage
{
	public function __construct()
	{

		$config = new Option('plugin-prefix');
		$config->addMenu(
			array(
				'page_title' => __('Plugin Name Settings', 'plugin-name'),
				'menu_title' => __('Plugin Name', 'plugin-name'),
				'capability' => 'manage_options',
				'slug'       => 'plugin-name',
				'icon'       => 'dashicons-performance',
				'position'   => 10,
				'submenu'    => false,
				// 'parent'     => 'edit.php',
			)
		);
		$config->addTab(
			array(
				array(
					'id'    => 'general_section',
					'title' => __('General Settings', 'plugin-name'),
					'desc'  => __('These are general settings for Plugin Name', 'plugin-name'),
				),
				array(
					'id'    => 'advance_section',
					'title' => __('Advanced Settings', 'plugin-name'),
					'desc'  => __('These are advance settings for Plugin Name', 'plugin-name')
				)
			)
		);
		$config->register();
	}
}
