<?php
/**
 * MemberLv
 */

declare( strict_types=1 );

namespace J7\PowerMembership\Resources\MemberLv;

/**
 * Class MemberLv
 */
final class MemberLv {

	/**
	 * ID
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Threshold
	 *
	 * @var int
	 */
	public $threshold;

	/**
	 * Order
	 *
	 * @var int
	 */
	public $order;


	/**
	 * Constructor
	 *
	 * @param \WP_Post $post Post.
	 */
	public function __construct( \WP_Post $post ) {
		$this->id        = (int) $post->ID;
		$this->name      = $post->post_title;
		$this->threshold = (float) \get_post_meta( $post->ID, Metabox::THRESHOLD_META_KEY, true );
		$this->order     = (int) $post->menu_order;
	}

	/**
	 * Get birthday award points by point id.
	 *
	 * @param int $point_id Point id.
	 *
	 * @return float
	 */
	public function get_bday_award_points( ?int $point_id ): float {
		if ( ! $point_id ) {
			$point_slug = \J7\WpUtils\Classes\WPUPointUtils::DEFAULT_POINT_SLUG;
		}

		return (float) \get_post_meta( $this->id, Metabox::AWARD_POINTS_USER_BDAY_FIELD_NAME . '_' . $point_id, true );
	}
}
