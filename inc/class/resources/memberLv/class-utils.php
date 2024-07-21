<?php
/**
 * Utils
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;

/**
 * Class Utils
 */
abstract class Utils {

	/**
	 * 取得會員等級 by
	 *
	 * @param string     $field 欄位 'user_id' or 'member_lv_id'
	 * @param int|string $value 欄位值
	 * @return MemberLv|null
	 */
	public static function get_member_lv_by( string $field, int|string $value ): ?MemberLv {
		$member_lvs        = self::get_member_lvs();
		$current_member_lv = null;

		switch ( $field ) {
			case 'user_id':
				$member_lv_id      = \get_user_meta( $value, MemberLvInit::POST_TYPE, true );
				$current_member_lv = self::get_member_lv( (int) $member_lv_id, $member_lvs );
				break;
			case 'member_lv_id':
				$current_member_lv = self::get_member_lv( (int) $value, $member_lvs );
				break;

			default:
				$current_member_lv = null;
				break;
		}

		return $current_member_lv;
	}

	/**
	 * 取得所有的會員物件，按照 menu_order 排序
	 *
	 * @param string|null $status 文章狀態
	 *
	 * @return array MemberLv[]
	 * - id: int
	 * - name: string
	 * - threshold: int
	 * - order: int
	 */
	public static function get_member_lvs( ?string $status = 'publish' ): array {

		$member_lv_posts = \get_posts(
			[
				'post_type'      => MemberLvInit::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => $status,
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			]
		);

		if ( ! $member_lv_posts ) {
			return [];
		}

		$member_lv_array = array_map( fn( $post ) => new MemberLv( $post ), $member_lv_posts );

		return $member_lv_array;
	}

	/**
	 * 取得會員等級
	 *
	 * @param int   $member_lv_id 當前會員等級ID
	 * @param array $member_lvs 會員等級陣列
	 * @return MemberLv|null
	 */
	private static function get_member_lv( int $member_lv_id, array $member_lvs ): ?MemberLv {
		$current_member_lv = null;
		foreach ( $member_lvs as $member_lv ) {
			if ( $member_lv->id === $member_lv_id ) {
				$current_member_lv = $member_lv;
				break;
			}
		}

		return $current_member_lv;
	}

	/**
	 * 取得下個會員等級 by
	 *
	 * @param string     $field 欄位 'user_id' or 'member_lv_id'
	 * @param int|string $value 欄位值
	 * @return MemberLv|null
	 */
	public static function get_next_member_lv_by( string $field, int|string $value ): ?MemberLv {
		$member_lvs = self::get_member_lvs();

		switch ( $field ) {
			case 'user_id':
				$member_lv_id   = \get_user_meta( $value, MemberLvInit::POST_TYPE, true );
				$next_member_lv = self::get_next_member_lv( (int) $member_lv_id, $member_lvs );
				break;
			case 'member_lv_id':
				$next_member_lv = self::get_next_member_lv( (int) $value, $member_lvs );
				break;

			default:
				$next_member_lv = null;
				break;
		}

		return $next_member_lv;
	}

	/**
	 * 取得下一個會員等級
	 *
	 * @param int   $member_lv_id 當前會員等級ID
	 * @param array $member_lvs 會員等級陣列
	 * @return MemberLv|null
	 */
	private static function get_next_member_lv( int $member_lv_id, array $member_lvs ): ?MemberLv {
		$current_order = self::get_member_lv( $member_lv_id, $member_lvs )?->order ?? null;
		if ( null === $current_order ) {
			return null;
		}

		$next_member_lv = null;
		$current_index  = null;
		foreach ( $member_lvs as $key => $member_lv ) {
			if ( $member_lv->id === $member_lv_id ) {
				$current_index = $key;
				break;
			}
		}

		// 如果找不到對應的member_lv或是最後一個，就回傳 null
		if ( null === $current_index || ( count( $member_lvs ) === $current_index + 1 ) ) {
			return null;
		}

		$next_member_lv = $member_lvs[ $current_index + 1 ];

		return $next_member_lv;
	}

	/**
	 * 取得上個會員等級 by
	 *
	 * @param string     $field 欄位 'user_id' or 'member_lv_id'
	 * @param int|string $value 欄位值
	 * @return MemberLv|null
	 */
	public static function get_prev_member_lv_by( string $field, int|string $value ): ?MemberLv {
		$member_lvs = self::get_member_lvs();

		switch ( $field ) {
			case 'user_id':
				$member_lv_id   = \get_user_meta( $value, MemberLvInit::POST_TYPE, true );
				$prev_member_lv = self::get_prev_member_lv( (int) $member_lv_id, $member_lvs );
				break;
			case 'member_lv_id':
				$prev_member_lv = self::get_prev_member_lv( (int) $value, $member_lvs );
				break;

			default:
				// code...
				break;
		}

		return $prev_member_lv;
	}

	/**
	 * 取得上一個會員等級
	 *
	 * @param int   $member_lv_id 當前會員等級ID
	 * @param array $member_lvs 會員等級陣列
	 * @return MemberLv|null
	 */
	private static function get_prev_member_lv( int $member_lv_id, array $member_lvs ): ?MemberLv {

		$current_order = self::get_member_lv( $member_lv_id, $member_lvs )?->order ?? null;
		if ( null === $current_order ) {
			return null;
		}

		$prev_member_lv = null;
		$current_index  = null;
		foreach ( $member_lvs as $key => $member_lv ) {
			if ( $member_lv->id === $member_lv_id ) {
				$current_index = $key;
				break;
			}
		}

		// 如果找不到對應的member_lv或是第一個，就回傳 null
		if ( null === $current_index || 0 === $current_index ) {
			return null;
		}

		$prev_member_lv = $member_lvs[ $current_index - 1 ];

		return $prev_member_lv;
	}


	/**
	 * 取得某個會員等級的會員數量
	 *
	 * @param string     $field 欄位 'user_id' or 'member_lv_id'
	 * @param int|string $value 欄位值
	 * @return int
	 */
	public static function get_member_count_by( string $field = 'member_lv_id', int|string $value ): int {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$member_lv_id = match ( $field ) {
			'member_lv_id' => $value,
		};

		$member_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %1$s WHERE meta_key = "%2$s" AND meta_value = "%3$d"', //phpcs:ignore
				"{$prefix}usermeta",
				MemberLvInit::POST_TYPE,
				$member_lv_id
			)
		);

		return $member_count;
	}
}
