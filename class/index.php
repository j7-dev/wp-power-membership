<?php

declare(strict_types=1);

namespace J7\PowerMembership;

use J7\PowerMembership\Utils;
use J7\PowerMembership\MemberLv\Metabox;
use J7\PowerMembership\Admin\Menu\Settings;


final class Bootstrap {

	private $tailwind_screen_ids = [ Utils::MEMBER_LV_POST_TYPE, 'user-edit', 'users' ];

	public function __construct() {
		require_once __DIR__ . '/admin/index.php';
		require_once __DIR__ . '/memberLv/index.php';
		require_once __DIR__ . '/woocommerce/index.php';
		require_once __DIR__ . '/gamipress/index.php';
		require_once __DIR__ . '/gamipress/invite.php';
		require_once __DIR__ . '/front-end/class-membership.php';

		\add_action('admin_enqueue_scripts', [ $this, 'add_static_assets' ]);
		\add_action('admin_head', [ $this, 'add_tailwind_config' ], 1000);
	}



	public function add_static_assets( $hook ): void {
		if (!is_admin()) {
			return;
		}

		global $power_plugins_settings;
		$is_simple_admin = $power_plugins_settings[ Settings::ENABLE_SIMPLE_ADMIN_FIELD_NAME ];

		$screen = \get_current_screen();

		if (in_array($screen->id, $this->tailwind_screen_ids)) {
			\wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com', [], '3.4.0');
		}
		if ('users.php' == $hook) {
			if ($is_simple_admin) {
				\wp_enqueue_script(
					'users',
					Utils::get_plugin_url() . '/assets/js/admin-users.js',
					[],
					Utils::get_plugin_ver(),
					[
						'strategy' => 'async',
					]
					);
			}
		}
		if ('user-edit.php' == $hook || 'profile.php' == $hook) {
			if ($is_simple_admin) {
				\wp_enqueue_script(
					'user-edit',
					Utils::get_plugin_url() . '/assets/js/admin-user-edit.js',
					[],
					Utils::get_plugin_ver(),
					[
						'strategy' => 'async',
					]
					);
			}
		}
		if (Utils::MEMBER_LV_POST_TYPE === $screen->id) {
			\wp_enqueue_script(
				Utils::MEMBER_LV_POST_TYPE,
				Utils::get_plugin_url() . '/assets/js/member_lv.js',
				[ 'jquery' ],
				Utils::get_plugin_ver(),
				[
					'strategy' => 'async',
				]
				);
		}
		\wp_localize_script(
			Utils::MEMBER_LV_POST_TYPE,
			Utils::MEMBER_LV_POST_TYPE . '_data',
			[
				'default_member_lv_id' => Metabox::$default_member_lv_id,
			]
			);
	}

	public function add_tailwind_config(): void {
		$screen = \get_current_screen();
		if (in_array($screen->id, $this->tailwind_screen_ids)) :
			?>
			<script>
				tailwind.config = {
					important: '.tailwindcss',
					corePlugins: {
						preflight: false,
						container: false, // conflicted with WordPress
					},
					content: ['./js/src/**/*.{js,ts,jsx,tsx}', './inc/**/*.php'],
					theme: {
						animation: {
							// why need this? because elementor plugin might conflict with same animate keyframe name
						// we override the animation name with this
						pulse: 'tw-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
					},
					extend: {
						colors: {
							primary: '#1677ff',
						},
						screens: {
							sm: '576px', // iphone SE
							md: '810px', // ipad Portrait
							lg: '1080px', // ipad Landscape
							xl: '1280px', // mac air
							xxl: '1440px',
						},
						keyframes: {
							'tw-pulse': {
								'50%': { opacity: '0.5' },
							},
						},
					},
				},
				plugins: [
					function ({ addUtilities }) {
						const newUtilities = {
							'.rtl': {
								direction: 'rtl',
							},

							// classes conflicted with WordPress
							'.tw-hidden': {
								display: 'none',
							},
							'.tw-columns-1': {
								columnCount: 1,
							},
							'.tw-columns-2': {
								columnCount: 2,
							},
							'.tw-fixed': {
								position: 'fixed',
							},
							'.tw-inline': {
								display: 'inline'
							}
						}
						addUtilities(newUtilities, ['responsive', 'hover'])
					},
				],
				safelist: [],
				blocklist: ['fixed', 'columns-1', 'columns-2', 'hidden', 'inline'],
				}
			</script>
			<?php
		endif;
	}
}
