<?php
/**
 * PointBase class
 * 能輕鬆創建點數
 *
 * @package J7\WpUtils
 */

namespace J7\WpUtils\Classes;

use J7\WpUtils\Classes\LogBase;

if ( class_exists( 'PointBase' ) ) {
	return;
}


/**
 * Class Point
 */
abstract class PointBase {
	/**
	 * Points slug
	 *
	 * @var string
	 */
	public $points_slug;

	/**
	 * Log instance
	 *
	 * @var LogBase
	 */
	public $log_instance;

	/**
	 * Constructor
	 *
	 * @param string $points_slug  Points slug
	 */
	public function __construct( string $points_slug, LogBase $log_instance ) {
		$this->points_slug  = $points_slug;
		$this->log_instance = $log_instance;
	}

	/**
	 * 加多少點
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points
	 * @return float updated points value
	 */
	public function award_points_to_user( $user_id = 0, $args = array(), $points = 0 ) {

		// If points are negative, turn them to positive
		if ( $points < 0 ) {
			$points *= -1;
		}

		// Use current user's ID if none specified
		if ( ! $user_id ) {
			$user_id = \get_current_user_id();
		}
		$points_slug    = $this->points_slug;
		$current_points = (float) \get_user_meta( $user_id, $points_slug, true );
		$points         = $current_points + $points;

		return $this->update_user_points( $user_id, $args, $points );
	}

	/**
	 * 扣多少點
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points
	 * @return float updated points value
	 */
	public function deduct_points_to_user( $user_id = 0, $args = array(), $points = 0 ) {
		// If points are positive, turn them to negative
		if ( $points > 0 ) {
			$points *= -1;
		}

		// Use current user's ID if none specified
		if ( ! $user_id ) {
			$user_id = \get_current_user_id();
		}
		$points_slug    = $this->points_slug;
		$current_points = (float) \get_user_meta( $user_id, $points_slug, true );
		$points         = $current_points + $points;

		return $this->update_user_points( $user_id, $args, $points );
	}

	/**
	 * 直接更新點數到某個值
	 *  TODO Mysql transaction
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points

	 * @return float updated points value
	 */
	public function update_user_points( $user_id = 0, $args = array(), $points = 0 ) {

		// Initialize args
		$args = \wp_parse_args(
			$args,
			array(
				'title' => '',
				'type'  => '',
			)
		);

		// Use current user's ID if none specified
		if ( ! $user_id ) {
			$user_id = \get_current_user_id();
		}

		$points_slug = $this->points_slug;

		$before_points = (float) \get_user_meta( $user_id, $points_slug, true );
		$after_points  = $points;
		$point_changed = $after_points - $before_points;

		\update_user_meta( $user_id, $points_slug, $points );
		$args['new_balance']   = $points;
		$args['point_changed'] = $point_changed;

		$this->create_user_log( $user_id, $args, $points );

		return (float) $points;
	}

	/**
	 * Create user log
	 *
	 * @param integer $user_id - user id
	 * @param array   $args - args
	 * @param float   $points - points
	 * @return void
	 */
	public function create_user_log( $user_id = 0, $args = array(), $points = 0 ) {
		$this->log_instance->insert_user_log( $user_id, $args, $points, $this->points_slug );
	}
}
