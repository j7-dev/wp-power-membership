<?php
[
	'coupon'           => $coupon,
	'props'              => $props,
] = $args;

?>

<label class="block px-2 py-1 <?= $props['disabled_bg'] ?>">
	<input data-type="normal_coupon" id="coupon-<?= $coupon->get_id(); ?>" name="yf_normal_coupon" class="mr-2 normal_coupon" type="radio" value="<?= $coupon->get_code(); ?>" <?= $props['disabled'] ?>>
	<span class="dashicons dashicons-tag <?= $props['disabled'] === 'disabled' ? 'text-gray-400' : 'text-red-400' ?>"></span>
	<?= $coupon->get_code() . $coupon->get_description() . $props['reason']; ?>
</label>