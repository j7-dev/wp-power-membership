(function ($) {

	wait('#discount_type', init);

	function init() {
		const AWARD_DEDUCT_VALUE = 'award_deduct';
		const $discount_type = $('#discount_type');
		const $coupon_amount_field = $('.coupon_amount_field');
		$discount_type.trigger('change');
		$discount_type.on('change', function () {
			const isAwardDeduct = AWARD_DEDUCT_VALUE === $(this).val();
			const label = isAwardDeduct ? '購物金折抵百分比 %' : '折價券金額';
			const tip = isAwardDeduct ? '如果最多折抵 20%，請輸入20' : '折價券金額';

			$coupon_amount_field.find('label').text(label);
			$coupon_amount_field.find('.woocommerce-help-tip').attr('aria-label', tip);
		});
	}

	function wait(selector, callback) {
		const interval = setInterval(() => {
			if ($(selector).length) {
				clearInterval(interval);
				callback();
			}
		}, 300);
	}
})(jQuery)