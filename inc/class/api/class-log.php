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
			'permission_callback' => 'is_user_logged_in',
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

	// $defaults = [
	// 'numberposts' => 10, // 每页显示的日志数量
	// 'offset'      => 0, // 跳过的日志数量
	// 'orderby'     => 'date', // 排序字段
	// 'order'       => 'DESC', // 排序方向
	// 'user_id'     => '', // 用户ID查询
	// 'modified_by' => '', // 修改者ID查询
	// 'type'        => '', // 日志类型查询
	// ];
	/**
	 * 獲取日誌的回調函數
	 *
	 * @param \WP_REST_Request $request REST 請求對象
	 *
	 * @return \WP_REST_Response 包含日誌的 REST 回應
	 */
	public function get_logs_callback( \WP_REST_Request $request ): \WP_REST_Response {

		$params = $request->get_query_params();

		$params = array_map( [ WP::class, 'sanitize_text_field_deep' ], $params );

		[
			'list'       => $logs,
			'pagination' => $pagination,
		] = Plugin::instance()->log_service_instance->get_logs( $params );

		[
			'total'      => $total,
			'totalPages' => $total_pages,
			// 'current'    => $current,
			// 'pageSize'   => $page_size
		] = $pagination;

		$response = new \WP_REST_Response( $logs );

		// // set pagination in header
		$response->header( 'X-WP-Total', (string) $total );
		$response->header( 'X-WP-TotalPages', (string) $total_pages );

		return $response;
	}
}

Log::instance();
