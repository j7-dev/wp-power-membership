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
	const THRESHOLD_META_KEY                = 'pm_threshold';
	const AWARD_POINTS_USER_BDAY_FIELD_NAME = 'pm_award_points_user_birthday';


	/**
	 * Constructor
	 */
	public function __construct() {
		\add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10, 2 );
		\add_action( 'save_post', array( $this, 'save_metabox' ), 10, 2 );
	}

	/**
	 * Add metabox
	 *
	 * @return void
	 */
	public function add_metabox( string $post_type, \WP_Post $post ): void {
		\add_meta_box(
			Plugin::$snake . '_metabox',
			'設定',
			array( $this, 'render_metabox' ),
			MemberLvInit::POST_TYPE,
			'normal',
			'high'
		);

		$all_points = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			\add_meta_box(
				Plugin::$snake . '_point_' . $point->slug . '_metabox',
				$point->name . '設定',
				function () use ( $post, $point ) {
					$this->render_point_metabox( $post, $point );
				},
				MemberLvInit::POST_TYPE,
				'normal',
				'low'
			);
		}
	}

	/**
	 * Render metabox
	 *
	 * @param \WP_Post $post Post.
	 * @return void
	 */
	public function render_metabox( $post ): void {

		$basic_fields = array(
			array(
				'label' => '會員累積消費升級門檻(NT$)',
				'name'  => self::THRESHOLD_META_KEY,
			),
			array(
				// TODO
				'label' => '🚧會員等級到期時間',
				'name'  => 'pm_expire_time',
			),
		);

		// phpcs:disable
		?>
		<div class="grid grid-cols-2 gap-4">
			<?php foreach($basic_fields as $field):
				$value = \get_post_meta( $post->ID, $field['name'], true );
				?>
				<label for="<?php echo $field['name']; ?>" class="w-[14rem] block"><?= $field['label'] ?></label>
				<input type="number" value="<?php echo $value; ?>" name="<?php echo $field['name']; ?>" min="0" class="" />
			<?php endforeach; ?>
		</div>
		<?php
		// phpcs:enable
	}


	/**
	 * Render point metabox
	 *
	 * @param \WP_Post  $post Post.
	 * @param \WPUPoint $point Point.
	 * @return void
	 */
	public function render_point_metabox( $post, $point ): void {

		$basic_fields = array(
			array(
				'label' => '會員生日當月1號就送',
				'name'  => self::AWARD_POINTS_USER_BDAY_FIELD_NAME . '_' . $point->slug,
			),
		);

		// phpcs:disable
		?>
		<div class="grid grid-cols-2 gap-4">
			<?php foreach($basic_fields as $field):
				$value = \get_post_meta( $post->ID, $field['name'], true );
				?>
				<label for="<?php echo $field['name']; ?>" class="w-[14rem] block"><?= $field['label'] ?></label>
				<input type="number" value="<?php echo $value; ?>" name="<?php echo $field['name']; ?>" min="0" class="" />
			<?php endforeach; ?>
		</div>
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
		$all_point_fields = array();
		$all_points       = Plugin::instance()->point_utils_instance->get_all_points();

		foreach ( $all_points as $point ) {
			$all_point_fields[] = self::AWARD_POINTS_USER_BDAY_FIELD_NAME . '_' . $point->slug;
		}

		$basic_fields = array(
			self::THRESHOLD_META_KEY,
		);

		$fields = array_merge( $basic_fields, $all_point_fields );

		foreach ( $fields as $field ) {
			// phpcs:disable
			if(isset( $_POST[ $field ] )){
				\update_post_meta( $post_id, $field, \sanitize_text_field( $_POST[ $field ] ) );
					}
			// phpcs:enable
		}
	}
}

Metabox::instance();
