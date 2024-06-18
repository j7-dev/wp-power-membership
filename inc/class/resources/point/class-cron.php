<?php
/**
 * Cron
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\Point;

use J7\PowerMembership\Plugin;
use J7\WpUtils\Classes\WPUPointUtils;
use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Resources\MemberLv\Utils;

/**
 * Class Cron
 */
final class Cron {
	use \J7\WpUtils\Traits\SingletonTrait;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'init', array( $this, 'cron_init' ) );
	}

	/**
	 * Cron init
	 *
	 * @return void
	 */
	public function cron_init(): void {

		if ( ! \wp_next_scheduled( 'pm_daily_check' ) ) {
			\wp_schedule_event( time(), 'daily', 'pm_daily_check' );
		}

		// \add_action( 'pm_daily_check', 'yf_clear_monthly', 10 );
		// \add_action( 'pm_daily_check', 'yf_member_upgrade', 20 );
		// 清除已發過的註記
		// add_action('pm_daily_check', 'clear_last_reward_reward', 25);
		\add_action( 'pm_daily_check', array( $this, 'birthday_award' ), 30 );

		// \add_action( 'pm_daily_check', 'yf_reward_monthly', 50 );

		// DELETE 馬上執行
	}


	/**
	 * Birthday award
	 * ✅ TESTED
	 *
	 * @return void
	 */
	public function birthday_award(): void {
		// 只有每月1號才執行
		if ( '01' !== gmdate( 'd', time() + 8 * 3600 ) ) {
			return;
		}
		$user_ids = Base::get_user_ids_by_bday_month();
		foreach ( $user_ids as $user_id ) {
			Point::award_bday_by_user_id( (int) $user_id );
		}
	}
}

Cron::instance();
