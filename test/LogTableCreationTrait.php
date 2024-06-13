<?php
/**
 * LogTableCreationTrait class
 * 創建 log table
 * 因為 create table 這種操作，感覺還是挺危險的，所以寫成抽象類，只能被繼承還有內部使用
 *
 * @package J7\WpUtils
 */

namespace J7\WpUtils\Classes;

if ( trait_exists( 'LogTableCreationTrait' ) ) {
	return;
}

/**
 * Class LogTableCreationTrait
 */
trait LogTableCreationTrait {

	/**
	 * Create database log table
	 *
	 * @param string $table_name Table name.
	 *
	 * @return void
	 * @throws \Exception Exception.
	 */
	final protected function create_log_table( string $table_name ): void {
		try {
			global $wpdb;

			$table_name = $wpdb->prefix . $table_name;

			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE $table_name (
									id mediumint(9) NOT NULL AUTO_INCREMENT,
									title text NOT NULL,
									type tinytext NOT NULL,
									user_id bigint(20) NOT NULL,
									point_slug tinytext NOT NULL,
									point_changed tinytext NOT NULL,
									new_balance tinytext NOT NULL,
									modified_by bigint(20) NOT NULL,
									date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
									PRIMARY KEY  (id)
							) $charset_collate;";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$result = \dbDelta( $sql );
		} catch ( \Throwable $th ) {
			throw new \Exception( $th->getMessage() );
		}
	}
}
