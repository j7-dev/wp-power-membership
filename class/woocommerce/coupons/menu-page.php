<?php

declare(strict_types=1);

namespace J7\PowerMembership\WooCommerce\Coupons;

use J7\PowerMembership\Utils;

final class MenuPage extends \PowerPlugin_AdminPageFramework
{
	// public function setUp()
	// {

	// 	// Create the root menu - specifies to which parent menu to add.
	// 	$this->setRootMenuPage(Utils::APP_NAME, 'dashicons-admin-generic', 100);

	// 	// Add the sub menus and the pages.
	// 	$this->addSubMenuItems(
	// 		array(
	// 			'title'     => '1. My First Setting Page',  // page and menu title
	// 			'page_slug' => 'my_first_settings_page'     // page slug
	// 		)
	// 	);
	// }



	public function __construct()
	{
		parent::__construct();
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
		$this->addSettingFields(
			array(    // Single text field
				'field_id'      => 'my_text_field',
				'type'          => 'text',
				'title'         => 'Text',
				'description'   => 'Type something here.',
			),
			array(    // Text Area
				'field_id'      => 'my_textarea_field',
				'type'          => 'textarea',
				'title'         => 'Single Text Area',
				'description'   => 'Type a text string here.',
				'default'       => 'Hello World! This is set as the default string.',
			),
			array( // Submit button
				'field_id'      => 'submit_button',
				'type'          => 'submit',
			)
		);
	}
}
