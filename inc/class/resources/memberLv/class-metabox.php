<?php
/**
 * Metabox
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Resources\MemberLv\Init as MemberLvInit;
use J7\PowerMembership\Plugin;

/**
 * Class Metabox
 */
final class Metabox {
	use \J7\WpUtils\Traits\SingletonTrait;

	const ACTION = 'pm_metabox';
	// 會員升級金額門檻
	const THRESHOLD_META_KEY = 'pm_threshold';

	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10 );
		\add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 */
	public function add_metabox(): void {
		\add_meta_box(
			Plugin::$snake . '_metabox',
			'會員升級門檻',
			array( $this, 'render_metabox' ),
			MemberLvInit::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render metabox
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function render_metabox( $post ): void {
		$threshold = \get_post_meta( $post->ID, self::THRESHOLD_META_KEY, true );
		$threshold = ! ! $threshold ? 0 : (int) $threshold;
		// phpcs:disable
		?>
		<div class="tailwindcss">
			<div class="flex items-center tailwind">
				<label for="<?php echo self::THRESHOLD_META_KEY; ?>" class="w-[14rem] block">會員累積消費升級門檻(NT$)</label>
				<input type="number" value="<?php echo $threshold; ?>" name="<?php echo self::THRESHOLD_META_KEY; ?>" min="0" step="1000" class="ml-8" />
			</div>
		</div>
		會員等級到期時間
		<?php
		// phpcs:enable
	}

	/**
	 * Save metabox
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function save_metabox( $post_id, $post ): void {
		// Check if user has permissions to save data.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// phpcs:disable
		$value = isset( $_POST[ self::THRESHOLD_META_KEY ] ) ? \sanitize_text_field( $_POST[ self::THRESHOLD_META_KEY ] ) : 0;
		$value = is_numeric( $value ) ? $value : 0;
		\update_post_meta( $post_id, self::THRESHOLD_META_KEY, $value );
		// phpcs:enable
	}
}

Metabox::instance();
