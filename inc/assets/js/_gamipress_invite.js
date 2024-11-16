(function ($) {
	checkRef()
	setRefField()

	function setRefField() {
		const ref = localStorage.getItem('ref');
		$('#pm_ref').val(ref);
	}
	function checkRef() {
		// 取得網址 GET 上面的 ref 參數
		const ref = new URLSearchParams(window.location.search).get('ref');
		if (ref) {
			localStorage.setItem('ref', ref);
		}
	}


})(jQuery)