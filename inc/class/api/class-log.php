<?php
/**
 * Log API
 */

declare(strict_types=1);

namespace J7\PowerMembership\Api;

use J7\WpUtils\Classes\WP;
use J7\PowerMembership\Plugin;

/**
 * Class Api
 */
final class Log {
	use \J7\WpUtils\Traits\SingletonTrait;
	use \J7\WpUtils\Traits\ApiRegisterTrait;

	/**
	 * APIs
	 *
	 * @var array{endpoint: string, method: string, permission_callback: ?callable}[]
	 * - endpoint: string
	 * - method: 'get' | 'post' | 'patch' | 'delete'
	 * - permission_callback : callable
	 */
	protected $apis = [
		[
			'endpoint'            => 'logs',
			'method'              => 'get',
			'permission_callback' => null,
		],

	];

	/**
	 * Constructor.
	 */
	public function __construct() {
		\add_action( 'rest_api_init', [ $this, 'register_api_products' ] );
	}

	/**
	 * Register products API
	 *
	 * @return void
	 */
	public function register_api_products(): void {
		$this->register_apis(
			apis: $this->apis,
			namespace: Plugin::$kebab,
			default_permission_callback: fn() => \current_user_can( 'manage_options' ),
		);
	}


	public function get_logs_callback( \WP_REST_Request $request ): \WP_REST_Response {

		$params = $request->get_query_params();

		$params = array_map( [ WP::class, 'sanitize_text_field_deep' ], $params );

		$default_args = [
			'search_columns' => [ 'ID', 'user_login', 'user_email', 'user_nicename', 'display_name' ],
			'posts_per_page' => 10,
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'offset'         => 0,
			'paged'          => 1,
			'count_total'    => true,
		];

		$args = \wp_parse_args(
			$params,
			$default_args,
		);

		if ( ! empty( $args['search'] ) ) {
			$args['search'] = '*' . $args['search'] . '*'; // 模糊搜尋
		}

		// Create the WP_User_Query object
		$wp_user_query = new \WP_User_Query( $args );

		/**
		 * @var \WP_User[] $users
		 */
		$users = $wp_user_query->get_results();

		$total       = $wp_user_query->get_total();
		$total_pages = \floor( $total / $args['posts_per_page'] ) + 1;

		$formatted_users = array_map( [ $this, 'format_user_details' ], $users );

		$response = new \WP_REST_Response( $formatted_users );

		// // set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );

		return $response;
	}
}

Log::instance();
