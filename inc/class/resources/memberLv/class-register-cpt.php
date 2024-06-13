<?php
/**
 * Custom Post Type: member_lv
 * DELETE 自訂PHP模板
 * DELETE 自訂rewrite
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Plugin;

/**
 * Class RegisterCPT
 */
final class RegisterCPT {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Rewrite
	 * DELETE
	 *
	 * @var array
	 */
	public $rewrite = array(
		'template_path' => 'test.php',
		'slug'          => 'test',
		'var'           => 'pm_test',
	);

	/**
	 * Constructor
	 */
	public function __construct() {

		\add_action( 'init', array( $this, 'init' ) );

		if ( ! empty( $args['rewrite'] ) ) {
			\add_filter( 'query_vars', array( $this, 'add_query_var' ) );
			\add_filter( 'template_include', array( $this, 'load_custom_template' ), 99 );
		}
	}

	/**
	 * Initialize
	 */
	public function init(): void {
		$this->register_cpt();

		// add {$this->post_type}/{slug}/test rewrite rule
		if ( ! empty( $this->rewrite ) ) {
			\add_rewrite_rule( '^power-membership/([^/]+)/' . $this->rewrite['slug'] . '/?$', 'index.php?post_type=power-membership&name=$matches[1]&' . $this->rewrite['var'] . '=1', 'top' );
			\flush_rewrite_rules();
		}
	}

	/**
	 * Register power-membership custom post type
	 */
	public static function register_cpt(): void {

		$labels = array(
			'name'                     => \esc_html__( '會員等級', 'power-membership' ),
			'singular_name'            => \esc_html__( 'member_lv', 'power-membership' ),
			'add_new'                  => \esc_html__( 'Add new', 'power-membership' ),
			'add_new_item'             => \esc_html__( 'Add new item', 'power-membership' ),
			'edit_item'                => \esc_html__( 'Edit', 'power-membership' ),
			'new_item'                 => \esc_html__( 'New', 'power-membership' ),
			'view_item'                => \esc_html__( 'View', 'power-membership' ),
			'view_items'               => \esc_html__( 'View', 'power-membership' ),
			'search_items'             => \esc_html__( 'Search power-membership', 'power-membership' ),
			'not_found'                => \esc_html__( 'Not Found', 'power-membership' ),
			'not_found_in_trash'       => \esc_html__( 'Not found in trash', 'power-membership' ),
			'parent_item_colon'        => \esc_html__( 'Parent item', 'power-membership' ),
			'all_items'                => \esc_html__( 'All', 'power-membership' ),
			'archives'                 => \esc_html__( 'member_lv archives', 'power-membership' ),
			'attributes'               => \esc_html__( 'member_lv attributes', 'power-membership' ),
			'insert_into_item'         => \esc_html__( 'Insert to this power-membership', 'power-membership' ),
			'uploaded_to_this_item'    => \esc_html__( 'Uploaded to this power-membership', 'power-membership' ),
			'featured_image'           => \esc_html__( 'Featured image', 'power-membership' ),
			'set_featured_image'       => \esc_html__( 'Set featured image', 'power-membership' ),
			'remove_featured_image'    => \esc_html__( 'Remove featured image', 'power-membership' ),
			'use_featured_image'       => \esc_html__( 'Use featured image', 'power-membership' ),
			'menu_name'                => \esc_html__( 'member_lv', 'power-membership' ),
			'filter_items_list'        => \esc_html__( 'Filter power-membership list', 'power-membership' ),
			'filter_by_date'           => \esc_html__( 'Filter by date', 'power-membership' ),
			'items_list_navigation'    => \esc_html__( 'member_lv list navigation', 'power-membership' ),
			'items_list'               => \esc_html__( 'member_lv list', 'power-membership' ),
			'item_published'           => \esc_html__( 'member_lv published', 'power-membership' ),
			'item_published_privately' => \esc_html__( 'member_lv published privately', 'power-membership' ),
			'item_reverted_to_draft'   => \esc_html__( 'member_lv reverted to draft', 'power-membership' ),
			'item_scheduled'           => \esc_html__( 'member_lv scheduled', 'power-membership' ),
			'item_updated'             => \esc_html__( 'member_lv updated', 'power-membership' ),
		);
		$args   = array(
			'label'                 => \esc_html__( '會員等級', 'power-membership' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'hierarchical'          => true,
			'exclude_from_search'   => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'show_in_admin_bar'     => false,
			'show_in_rest'          => true,
			'query_var'             => false,
			'can_export'            => true,
			'delete_with_user'      => true,
			'has_archive'           => false,
			'rest_base'             => '',
			'show_in_menu'          => false,
			'menu_position'         => 6,
			'menu_icon'             => 'dashicons-store',
			'capability_type'       => 'post',
			'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'author', 'page-attributes' ),
			'taxonomies'            => array(),
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'               => array(
				'with_front' => true,
			),
		);

		\register_post_type( Init::POST_TYPE, $args );
	}


	/**
	 * Add query var
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public function add_query_var( $vars ) {
		$vars[] = $this->rewrite['var'];
		return $vars;
	}

	/**
	 * Custom post type rewrite rules
	 *
	 * @param array $rules Rules.
	 * @return array
	 */
	public function custom_post_type_rewrite_rules( $rules ) {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
		return $rules;
	}


	/**
	 * Load custom template
	 * Set {Plugin::$kebab}/{slug}/report  php template
	 *
	 * @param string $template Template.
	 */
	public function load_custom_template( $template ) {
		$repor_template_path = Plugin::$dir . '/inc/templates/' . $this->rewrite['template_path'];

		if ( \get_query_var( $this->rewrite['var'] ) ) {
			if ( file_exists( $repor_template_path ) ) {
				return $repor_template_path;
			}
		}
		return $template;
	}
}

RegisterCPT::instance();
