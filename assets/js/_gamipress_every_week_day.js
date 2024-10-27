(function ($) {
	console.log('gamipress_every_week_day');

	const $trigerTypes = $("select[id^='select-trigger-type']")
	$trigerTypes.each(function () {
		const $li = $(this).closest('li');
		showRepeatRow($(this).val(), $li);
	});
	$trigerTypes.on('change', function () {
		const $li = $(this).closest('li');
		showRepeatRow($(this).val(), $li);
	});


	$('.requirements-list').on('update_requirement_data', '.requirement-row', function (e, requirement_details, requirement) {

		console.log(requirement);
		console.log(requirement_details);
		// Add expiration fields
		requirement_details._gamipress_every_week_day = requirement.find('select[id^="_gamipress_every_week_day"]').val();
		requirement_details._gamipress_ratio = requirement.find('._gamipress_ratio input').val();
		requirement_details.points = requirement.find('input[name="requirement-points"]').val();


	});

	function showRepeatRow(type, $li) {
		const $rows = $li.find('._gamipress_every_week_day-row, ._gamipress_ratio-row');
		if ('pm_award_by_order_amount' !== type) {
			$rows.each(function () {
				$(this).find('._gamipress_ratio input, select[id^="_gamipress_every_week_day"]').val('')
				$(this).hide();
			});
			return;
		}

		$rows.show();
	}

})(jQuery);