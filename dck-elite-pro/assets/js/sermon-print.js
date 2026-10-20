(function () {
	function openAll() {
		var ds = document.querySelectorAll('.dck-sermon-single details');
		for (var i = 0; i < ds.length; i++) { ds[i].open = true; }
	}
	window.addEventListener('beforeprint', openAll);
	var btn = document.querySelector('.dck-print-sermon');
	if (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			openAll();
			window.print();
		});
	}
})();
