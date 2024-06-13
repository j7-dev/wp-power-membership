<?php
/**
 * PointFactory class
 * 能輕鬆創建點數
 *
 * @package J7\WpUtils
 */

namespace J7\WpUtils\Classes;

use J7\WpUtils\Classes\LogFactory;

if ( class_exists( 'PointFactory' ) ) {
	return;
}


/**
 * Class Point
 */
final class PointFactory {
	/**
	 * Points slug
	 *
	 * @var string
	 */
	public $point_slug;

	/**
	 * Log instance
	 *
	 * @var LogFactory
	 */
	public $log_instance;

	/**
	 * Constructor
	 *
	 * @param string     $point_slug  Points slug
	 * @param LogFactory $log_instance Log instance
	 */
	public function __construct( string $point_slug, LogFactory $log_instance ) {
		$this->point_slug   = $point_slug;
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
		$point_slug     = $this->point_slug;
		$current_points = (float) \get_user_meta( $user_id, $point_slug, true );
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
		$point_slug     = $this->point_slug;
		$current_points = (float) \get_user_meta( $user_id, $point_slug, true );
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

		$point_slug = $this->point_slug;

		$before_points = (float) \get_user_meta( $user_id, $point_slug, true );
		$after_points  = $points;
		$point_changed = $after_points - $before_points;

		\update_user_meta( $user_id, $point_slug, $points );
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
		$this->log_instance->insert_user_log( $user_id, $args, $points, $this->point_slug );
	}
}
