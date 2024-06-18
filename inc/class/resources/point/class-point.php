<?php
/**
 * 初始化
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\Point;

use J7\PowerMembership\Plugin;
use J7\WpUtils\Classes\WPUPointUtils;
use J7\PowerMembership\Resources\MemberLv\Metabox;
use J7\PowerMembership\Resources\MemberLv\Utils;


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

		\add_action( 'user_register', array( $this, 'award_after_user_register' ), 10, 2 );
	}

	/**
	 * Get log types
	 * PENDING
	 *
	 * @return array
	 */
	public function get_log_types(): array {
		return array(
			'system' => '系統',
			'admin'  => '管理員',
			'user'   => '使用者',
		);
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
	public function create_user_log( int $user_id = 0, array $args = array(), float $points = 0, string $point_slug = 'wpu_default_point' ): void {
		Plugin::instance()->log_utils_instance->insert_user_log( $user_id, $args, $points, $point_slug );
	}

	/**
	 * 首次成為會員送 XX 購物金
	 *
	 * @param integer $user_id - user id
	 * @param array   $userdata - user data
	 * @return void
	 */
	public function award_after_user_register( int $user_id, array $userdata ): void {

		$all_points = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			$award_points = (float) \get_post_meta( $point->id, Metabox::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME, true );
			if ( ! $award_points ) {
				continue;
			}

			$point->award_points_to_user(
				$user_id,
				array(
					'title' => '首次成為會員送購物金',
					'type'  => 'system',
				),
				$award_points
			);

		}
	}

	/**
	 * 對指定會員發放生日禮金
	 *
	 * @param int $user_id - user id
	 * @return void
	 */
	public static function award_bday_by_user_id( int $user_id ): void {
		$user           = \get_userdata( $user_id );
		$user_member_lv = Utils::get_member_lv_by( 'user_id', $user_id );
		$all_points     = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			$award_points = $user_member_lv?->get_bday_award_points( $point->slug );
			if ( ! $award_points ) {
				continue;
			}

			// TODO $allow_bday_reward = allow_bday_reward( $user_id );
			$allow_bday_reward = true;

			if ( $allow_bday_reward ) {
				// Award the points to the user
				$point->award_points_to_user(
					(int) $user_id,
					array(
						'title' => "生日禮金發放 {$point->name} {$award_points} 點 - {$user->display_name} ({$user_member_lv->name})",
						'type'  => 'system',
					),
					$award_points
				);

				\update_user_meta( $user_id, 'last_' . $point->slug . '_birthday_awarded_on', date( 'Y-m-d H:i:s', strtotime( '+8 hours' ) ) );

			}
			// else 不發放生日禮金

		}
	}
}

Point::instance();
