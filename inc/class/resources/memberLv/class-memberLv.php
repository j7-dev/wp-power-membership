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
	 * Image Url
	 *
	 * @var string
	 */
	public $img_url;

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
	 * Limit type
	 * 限制類型，可為 'fixed' 或 'assigned'。
	 *
	 * @var string unlimited|fixed|assigned
	 */
	public $limit_type = 'unlimited';

	/**
	 * Limit value
	 * 限制值
	 *
	 * @var int
	 */
	public $limit_value = 1;

	/**
	 * Limit unit
	 * 限制單位，可為 'day'、'month' 或 'year'。
	 *
	 * @var string day|month|year
	 */
	public $limit_unit = 'year';


	/**
	 * Constructor
	 *
	 * @param \WP_Post $post Post.
	 */
	public function __construct( \WP_Post $post ) {
		$this->id          = (int) $post->ID;
		$this->name        = $post->post_title;
		$this->img_url     = \get_the_post_thumbnail_url( $this->id, 'thumbnail' );
		$this->threshold   = (float) \get_post_meta( $post->ID, Metabox::THRESHOLD_META_KEY, true );
		$this->order       = (int) $post->menu_order;
		$this->limit_type  = \get_post_meta( $post->ID, Metabox::LIMIT_TYPE_META_KEY, true ) ?: 'unlimited';
		$this->limit_value = (int) \get_post_meta( $post->ID, Metabox::LIMIT_VALUE_META_KEY, true ) ?: 1;
		$this->limit_unit  = \get_post_meta( $post->ID, Metabox::LIMIT_UNIT_META_KEY, true ) ?: 'year';
	}
}
