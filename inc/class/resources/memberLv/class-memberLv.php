<?php
/**
 * MemberLv
 */

declare(strict_types=1);

namespace J7\PowerMembership\Resources\MemberLv;

use J7\PowerMembership\Resources\MemberLv\Metabox;
use J7\PowerMembership\Plugin;

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
	 * Get birthday award points by point slug.
	 *
	 * @param string|null $point_slug Point slug.
	 * @return float
	 */
	public function get_bday_award_points( ?string $point_slug ): float {
		if ( ! $point_slug ) {
			$point_slug = \J7\WpUtils\Classes\WPUPointUtils::DEFAULT_POINT_SLUG;
		}

		$bday_award_points = (float) \get_post_meta( $this->id, Metabox::AWARD_POINTS_USER_BDAY_FIELD_NAME . '_' . $point_slug, true );
		return $bday_award_points;
	}
}
