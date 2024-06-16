<?php
/**
 * 初始化
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\Point;

use J7\PowerMembership\Plugin;
/**
 * Class Init
 */
final class Point {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'wpu_point_update_user_points', array( $this, 'create_user_log' ), 100, 4 );
	}


	/**
	 * Create user log
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points
	 * @param string  $point_slug - point slug
	 * @return void
	 */
	public function create_user_log( $user_id = 0, $args = array(), $points = 0, $point_slug = 'wpu_default_point' ) {
		Plugin::instance()->log_utils_instance->insert_user_log( $user_id, $args, $points, $point_slug );
	}
}

Point::instance();
