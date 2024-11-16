(function ($) {

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
		// Add expiration fields
		requirement_details._gamipress_every_week_day = requirement.find('select[id^="_gamipress_every_week_day"]').val();

		const startHour = requirement.find('.start_time input[name="hour"]').val();
		const startMinute = requirement.find('.start_time input[name="minute"]').val();
		requirement_details._gamipress_every_week_day_start_time = (startHour ? startHour.padStart(2, '0') : '00') + ':' + (startMinute ? startMinute.padStart(2, '0') : '00');

		const endHour = requirement.find('.end_time input[name="hour"]').val();
		const endMinute = requirement.find('.end_time input[name="minute"]').val();
		requirement_details._gamipress_every_week_day_end_time = (endHour ? endHour.padStart(2, '0') : '00') + ':' + (endMinute ? endMinute.padStart(2, '0') : '00');

		requirement_details._gamipress_ratio = requirement.find('._gamipress_ratio input').val();
		requirement_details.points = requirement.find('input[name="requirement-points"]').val();


	});


	$('.requirements-list').on('change', function () {
		const $li = $(this).children('li').last();
		const $trigerType = $li.find("select[id^='select-trigger-type']");
		showRepeatRow($trigerType.val(), $li);
		$trigerType.on('change', function () {
			showRepeatRow($trigerType.val(), $li);
		});
	})

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