<?php
/**
 * Metabox
 * DELETE
 */

declare( strict_types=1 );

namespace J7\PowerMembership\Resources\Point;

use J7\PowerMembership\Plugin;
use J7\WpUtils\Classes\PointService;

/**
 * Class Metabox
 */
final class Metabox {
	use \J7\WpUtils\Traits\SingletonTrait;

	// 會員註冊完成就送
	const AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME = 'pm_award_points_after_user_register';


	const ACTION = 'pm_metabox';

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
			'設定',
			array( $this, 'render_metabox' ),
			PointService::POST_TYPE,
			'normal',
			'high'
		);
	}

	/**
	 * Render metabox
	 *
	 * @param \WP_Post $post Post.
	 *
	 * @return void
	 */
	public function render_metabox( $post ): void {
		$basic_fields = array(
			array(
				'label' => '會員註冊完成就送',
				'name'  => self::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME,
			),
		);

		// phpcs:disable
		?>

        <div class="grid grid-cols-2 gap-4">
			<?php
			foreach ( $basic_fields as $field ):
				$value = \get_post_meta( $post->ID, $field['name'], true );
				?>
                <label for="<?php
				echo $field['name']; ?>" class="w-[14rem] block"><?= $field['label'] ?></label>
                <input type="number" value="<?php
				echo $value; ?>" name="<?php
				echo $field['name']; ?>" min="0" class=""/>
			<?php
			endforeach; ?>
        </div>

		<?php
		// phpcs:enable
	}

	/**
	 * Save metabox
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post.
	 *
	 * @return void
	 */
	public function save_metabox( $post_id, $post ): void {
		// Check if user has permissions to save data.
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = array(
			self::AWARD_POINTS_AFTER_USER_REGISTER_FIELD_NAME,
		);

		foreach ( $fields as $field ) {
			// phpcs:disable
			if ( isset( $_POST[ $field ] ) ) {
				\update_post_meta( $post_id, $field, \sanitize_text_field( $_POST[ $field ] ) );
			}
			// phpcs:enable
		}
	}
}

Metabox::instance();
