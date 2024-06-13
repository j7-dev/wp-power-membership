<?php
/**
 * LogFactory class
 * 能輕鬆創建 & 操作 log table
 *
 * @package J7\WpUtils
 */

namespace J7\WpUtils\Classes;

if ( class_exists( 'LogFactory' ) ) {
	return;
}

/**
 * Class LogFactory
 */
abstract class LogFactory {

	/**
	 * Log table name
	 *
	 * @var string
	 */
	public $table_name;

	/**
	 * Constructor
	 *
	 * @param string $table_name The log table name.
	 */
	public function __construct( string $table_name ) {
		$this->table_name = $table_name;
	}

	/**
	 * Get log types
	 *
	 * @return array
	 */
	public function get_log_types() {

		$log_types = array(
			'purchase', // 購買
			'modify', // 修改
			'cron', // 定時任務
		);

		return $log_types;
	}

	/**
	 * Retrieves logs from the database with pagination.
	 *
	 * This function fetches logs from a custom table, allowing for filtering based on
	 * user ID, modifier ID, and log type. It supports pagination and sorting.
	 *
	 * @param array $args {
	 *     Optional. An array of arguments to control the retrieval of logs.
	 *
	 *     @type int    $number       The number of logs to retrieve per page. Default 10.
	 *     @type int    $offset       The number of logs to offset (skip) in the query. Useful for pagination. Default 0.
	 *     @type string $orderby      The column by which to order the logs. Default 'date'.
	 *     @type string $order        The order direction ('ASC' or 'DESC'). Default 'DESC'.
	 *     @type string $user_id      The ID of the user to filter logs by. Default empty.
	 *     @type string $modified_by  The ID of the user who modified to filter logs by. Default empty.
	 *     @type string $type         The type of log to filter by. Default empty.
	 * }
	 * @return array Returns an associative array with two keys:
	 *               'list' — an array of log objects or arrays based on the query results,
	 *               'pagination' — an associative array with pagination details, including
	 *               total number of logs, total pages, current page, and logs per page.
	 */
	public function get_logs( $args = array() ) {
		global $wpdb;
		$defaults = array(
			'numberposts' => 10, // 每页显示的日志数量
			'offset'      => 0, // 跳过的日志数量
			'orderby'     => 'date', // 排序字段
			'order'       => 'DESC', // 排序方向
			'user_id'     => '', // 用户ID查询
			'modified_by' => '', // 修改者ID查询
			'type'        => '', // 日志类型查询
		);

		$args = \wp_parse_args( $args, $defaults );

		$numberposts = \absint( $args['numberposts'] );
		$offset      = \absint( $args['offset'] );
		$orderby     = \esc_sql( $args['orderby'] );
		$order       = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';
		$user_id     = \esc_sql( $args['user_id'] );
		$modified_by = \esc_sql( $args['modified_by'] );
		$type        = \esc_sql( $args['type'] );

		$table_name = $wpdb->prefix . $this->table_name;

		// 构建WHERE子句
		$where = array();
		if ( ! empty( $user_id ) ) {
			$where[] = "user_id = '$user_id'";
		}
		if ( ! empty( $modified_by ) ) {
			$where[] = "modified_by = '$modified_by'";
		}
		if ( ! empty( $type ) ) {
			$where[] = "type = '$type'";
		}
		$where_sql = count( $where ) > 0 ? ' WHERE ' . implode( ' AND ', $where ) : '';

		// 构建查询语句
		$query = "SELECT * FROM {$table_name}{$where_sql} ORDER BY {$orderby} {$order}";

		$total_query = str_replace( ' *', ' COUNT(*)', $query );

		if ( $numberposts != -1 ) {
			// Add ordering and limited pagination if not fetching all
			$query .= $wpdb->prepare( ' LIMIT %d, %d', $offset, $numberposts );
		}

		// 执行查询
		$results = $wpdb->get_results( $query );

		// 转换结果
		$logs = array_map(
			function ( $item ) {
				return $item; // 这里可以根据需要将项目转换为对象或保留为数组
			},
			$results
		);

		// 查询总记录数以计算分页信息
		$total = $wpdb->get_var( $total_query );

		if ( $numberposts != -1 ) {
			$total_pages = ceil( $total / $numberposts );
			$page_size   = $numberposts;
			$current     = (int) floor( $offset / $numberposts ) + 1;
		} else {
			$total_pages = 1;
			$page_size   = $total;
			$current     = 1;
		}

		// 准备分页信息
		$pagination = array(
			'total'      => (int) $total,
			'totalPages' => (int) $total_pages,
			'current'    => (int) $current,
			'pageSize'   => (int) $page_size,
		);

		$data = array(
			'list'       => $logs,
			'pagination' => $pagination,
		);

		return $data;
	}

	/**
	 * Insert a log entry into the database.
	 *
	 * @param int    $user_id     The ID of the user to log the entry for.
	 * @param array  $args        An array of arguments to log.
	 * @param float  $points      The points to log.
	 * @param string $points_slug The points slug to log.
	 *
	 * @return void
	 * @throws \WP_Error Exception.
	 * @throws \Exception Exception.
	 */
	public function insert_user_log( $user_id = 0, $args = array(), $points = 0, $points_slug = '' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . $this->table_name;

		if ( ! $points_slug ) {
			throw new \WP_Error( 'invalid_points_slug', '沒有指定 points slug' );
		}

		$modified_by = \absint( $args['modified_by'] );

		try {
			$new_balance = (float) ( $args['new_balance'] ?? \get_user_meta( $user_id, $points_slug, true ) );
			$result      = $wpdb->insert(
				$table_name,
				array(
					'title'         => \sanitize_text_field( $args['title'] ?? 'No Title' ),
					'type'          => \sanitize_text_field( $args['type'] ?? '' ),
					'user_id'       => $user_id,
					'modified_by'   => $modified_by,
					'point_slug'    => $points_slug,
					'point_changed' => number_format( $args['point_changed'] ?? '', 2 ),
					'new_balance'   => number_format( $new_balance, 2 ),
					'date'          => \current_time( 'mysql' ),
				)
			);

			if ( ! $result ) {
				throw new \WP_Error( 'insert_log_error', '插入 LOG 失敗' );
			}
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}
	}
}
