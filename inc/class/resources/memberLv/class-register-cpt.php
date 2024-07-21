<?php
/**
 * Custom Post Type: member_lv
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Resources\MemberLv\Utils as MemberLvUtils;

/**
 * Class RegisterCPT
 */
final class RegisterCPT {
	use \J7\WpUtils\Traits\SingletonTrait;



	/**
	 * Constructor
	 */
	public function __construct() {

		\add_action( 'init', [ $this, 'init' ] );

		\add_filter( 'manage_' . Init::POST_TYPE . '_posts_columns', [ $this, 'set_custom_columns' ] );
		\add_action( 'manage_' . Init::POST_TYPE . '_posts_custom_column', [ $this, 'set_custom_column_value' ], 10, 2 );

		\add_action( 'pre_get_posts', [ $this, 'order_by_menu_order' ] );
	}

	/**
	 * Initialize
	 */
	public function init(): void {
		$this->register_cpt();
	}

	/**
	 * Register power-membership custom post type
	 */
	public static function register_cpt(): void {

		$labels = [
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
		];
		$args   = [
			'label'                 => \esc_html__( '會員等級', 'power-membership' ),
			'labels'                => $labels,
			'description'           => '',
			'public'                => true,
			'hierarchical'          => false,
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
			'supports'              => [ 'title', 'editor', 'thumbnail', 'custom-fields', 'author', 'page-attributes' ],
			'taxonomies'            => [],
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'rewrite'               => [
				'with_front' => true,
			],
		];

		\register_post_type( Init::POST_TYPE, $args );
	}

	/**
	 * 設定自訂欄位
	 *
	 * @param array $columns 欄位
	 *
	 * @return array
	 */
	public function set_custom_columns( array $columns ): array {

		$new_columns = array_slice( $columns, 1, 1 ) + [
			'menu_order'   => '等級順序',
			'member_count' => '會員人數',
		] + array_slice( $columns, 1 );

		return $new_columns;
	}

	/**
	 * 設定自訂欄位的值
	 *
	 * @param string $column 欄位名稱
	 * @param int    $post_id 文章 ID
	 *
	 * @return void
	 */
	public function set_custom_column_value( string $column, $post_id ): void {
		switch ( $column ) {
			case 'menu_order':
				echo get_post_field( 'menu_order', $post_id );
				break;
			case 'member_count':
				echo MemberLvUtils::get_member_count_by( 'member_lv_id', $post_id );
				break;
		}
	}

	/**
	 * 讓後台的會員等級根據 menu order 順序排序查詢
	 *
	 * @param \WP_Query $query 查詢對象
	 *
	 * @return void
	 */
	public function order_by_menu_order( \WP_Query $query ): void {
		if (!is_admin()) {
			return;
		}

		if (Init::POST_TYPE !== $query->get('post_type')) {
			return;
		}

		$query->set( 'orderby', 'menu_order' );
		$query->set( 'order', 'ASC');
	}
}

RegisterCPT::instance();
