<?php
/**
 * 初始化
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Utils\Base;
use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;

/**
 * Class Init
 */
final class Init {
	use \J7\WpUtils\Traits\SingletonTrait;

	const POST_TYPE = 'member_lv';

	/**
	 * Default member_lv id
	 *
	 * @var int
	 */
	public $default_member_lv_id;

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'init', array( $this, 'init' ), 30 );
		\add_action( 'admin_menu', array( $this, 'menu_page' ), 10 );
		\add_action( 'save_post_' . self::POST_TYPE, array( $this, 'delete_transient', 100, 3 ) );
	}

	/**
	 * Initialize
	 *
	 * @return void
	 */
	public function init(): void {
		$this->create_default_member_lv();
	}

	/**
	 * 創建預設會員等級
	 * 如果沒有 default 會員等級，則創建一個
	 *
	 * @return void
	 */
	private function create_default_member_lv(): void {
		$post_type = self::POST_TYPE;

		$slug = 'default';

		$page = \get_page_by_path( $slug, OBJECT, $post_type );
		if ( $page ) {
			$this->default_member_lv_id = $page->ID;
			return;
		} else {
			// create default member_lv
			$post_id                    = \wp_insert_post(
				array(
					'post_title'  => '預設會員',
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'post_name'   => $slug,
					'meta_input'  => array(
						Metabox::THRESHOLD_META_KEY => '0',
					),
				)
			);
			$this->default_member_lv_id = $post_id;
			$this->set_all_users_default_member_lv( $post_id );
		}
	}

	/**
	 * 把所有沒有會員等級的 user 套用預設會員等級
	 *
	 * @param int $default_member_lv_id Default member_lv id.
	 * @return void
	 */
	private function set_all_users_default_member_lv( int $default_member_lv_id ): void {
		global $wpdb;
		$prefix = $wpdb->prefix;
		// get all user ids
		//phpcs:disable
		$user_ids = $wpdb->get_col( "SELECT ID FROM {$prefix}users" );
		//phpcs:enable

		foreach ( $user_ids as $user_id ) {
			$member_lv = \get_user_meta( $user_id, Base::CURRENT_MEMBER_LV_META_KEY );
			if ( empty( $member_lv ) ) {
				\update_user_meta( $user_id, Base::CURRENT_MEMBER_LV_META_KEY, $default_member_lv_id );
				\update_user_meta( $user_id, Base::MEMBER_LV_EARNED_TIME_META_KEY, time() );
			}
		}
	}

	/**
	 * 新增會員等級子選單
	 *
	 * @return void
	 */
	public function menu_page(): void {
		\add_submenu_page(
			'users.php',
			__( '會員等級', 'power-membership' ),
			__( '會員等級', 'power-membership' ),
			'edit_users',
			'edit.php?post_type=' . MemberLvInit::POST_TYPE,
			'',
			200
		);
	}

	/**
	 * 刪除快取
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post.
	 * @param bool     $update Whether this is an existing post being updated.
	 * @return void
	 */
	public function delete_transient( int $post_id, \WP_Post $post, bool $update ): void {
		\delete_transient( Utils::MEMBER_LVS_BY_ORDER_TRANSIENT_KEY );
	}
}

Init::instance();
