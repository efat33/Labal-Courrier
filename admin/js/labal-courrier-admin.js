(function ($) {
	'use strict';

	/**
	 * When the window is loaded:
	 *
	 * 
	 */

	$(window).load(function () {
		if ($(".collapse.show").length) {
			$('html, body').animate({
				scrollTop: $(".collapse.show").offset().top - 100
			}, 500);
		}
	});

	$(function () {
		// console.log($('.form-rate'));
		// $('.form-rate').submit(function (e) {
		// 	e.preventDefault();
		// 	let data = $(this).serialize();
		// })
	});


})(jQuery);
