<?php
/**
 * GamiPress Logs Api
 */

declare(strict_types=1);

namespace J7\PowerMembership\Gamipress;

use J7\PowerMembership\Plugin;

/**
 *  GamiPress Api
 */
final class Api {
	use \J7\WpUtils\Traits\SingletonTrait;
	use \J7\WpUtils\Traits\ApiRegisterTrait;

	/**
	 * APIs
	 *
	 * @var array{endpoint:string,method:string,permission_callback: callable|null }[]
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
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'rest_api_init', [ $this, 'register_api_logs' ] );
	}

	/**
	 * Register Logs API
	 *
	 * @return void
	 */
	public function register_api_logs(): void {
		$this->register_apis(
			apis: $this->apis,
			namespace: Plugin::$kebab,
			default_permission_callback: fn() => \current_user_can( 'manage_options' ),
		);
	}

	/**
	 * Get logs callback
	 *
	 * @param \WP_REST_Request $request Request.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 * @phpstan-ignore-next-line
	 */
	public function get_logs_callback( $request ) { // phpcs:ignore
		$params  = $request->get_query_params();
		$since   = (int) $params['since'] ?? 0;
		$since  += 8 * HOUR_IN_SECONDS; // 轉換為台灣/香港時間
		$user_id = \get_current_user_id();
		$logs    = \gamipress_get_user_logs( $user_id, [], $since );

		return new \WP_REST_Response( $logs );
	}
}

Api::instance();
