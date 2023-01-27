var ready = function() {
	'use strict';
	/*!
	 * Vanille.js core
	 * Licensed MIT (https://github.com/yomli/vanille/blob/main/LICENSE)
	 */
	const $ = (q, d = document) =>
		/#\S+$/.test(q)
		? d.querySelector.bind(d)(q)
		: [...d.querySelectorAll.bind(d)(q)];

	const body = document.body;
	const html = document.documentElement;
/*
	Spoon: scroller-hide
	Hide scroller when viewport's height is superior to the content.
	Note that this is needed only for browsers not supporting the
	deprecated CSS 'clip'.
*/
;(function (window, document, undefined) {

	var scroller = $('.scroller'),
		scrollerButton = $('.scroller-link');
	if (!scroller || !scrollerButton) return;

	// Get scroller button height
	var scrollerHeight;
	for (var i = 0; i < scrollerButton.length; i++) {
		scrollerHeight = scrollerButton[i].offsetHeight;
	}

	window.addEventListener('resize', function(event) {
		// Get viewport and page height
		var vh = Math.max(html.clientHeight || 0, window.innerHeight || 0),
			height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);

		var display = ( (vh + scrollerHeight) >= height ) ? 'none' : 'block';
		for (var i = 0; i < scroller.length; i++) {
			scroller[i].style.display = display;
		}
	}, false);

	// Trigger a resize event on load
	var event = document.createEvent('HTMLEvents');
	event.initEvent('resize', true, false);
	document.dispatchEvent(event);

})(window, document);

/*
	Spoon: color-mode
	Will toggle the color mode
	Will make the change persistant
	See https://gomakethings.com/adding-a-night-mode-to-your-site-with-vanilla-javascript/
*/
;(function (window, document, undefined) {

	// Apply scheme by adding a body class
	// and make changes persistant
	function applyScheme(scheme) {
		switch (scheme) {
			case 'light':
				body.classList.remove('color-scheme-dark');
				body.classList.add('color-scheme-light');
				break;
			case 'dark':
				body.classList.remove('color-scheme-light');
				body.classList.add('color-scheme-dark');
				break;
			default:
				body.classList.remove('color-scheme-dark');
				body.classList.remove('color-scheme-light');
				break;
		}
		let systemScheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
		if (scheme === systemScheme) {
			localStorage.removeItem('color-scheme');
		} else {
			localStorage.setItem('color-scheme', scheme);
		}
	}

	// Get the theme to apply
	function getCurrentScheme() {
		let systemScheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
		let chosenScheme = localStorage.getItem('color-scheme') ? localStorage.getItem('color-scheme') : systemScheme;
		if (systemScheme === chosenScheme) localStorage.removeItem('color-scheme');
		return chosenScheme;
	}

	// Apply scheme at load
	if (!('localStorage' in window)) return;
	applyScheme(getCurrentScheme());

	// Toggle scheme by pressing button
	var colorButton = $('.color-scheme-toggle');
	if (!colorButton) return;
	[].forEach.call(colorButton, function(button) {
		button.addEventListener('click', function(event) {
			//event.preventDefault();
			// Toggle scheme
			let newScheme = (getCurrentScheme() === 'dark') ? 'light' : 'dark';
			applyScheme(newScheme);

			// Clear focus
			document.activeElement.blur();

		}, false);
	});

	// Apply scheme on system change
	window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => e.matches && applyScheme('dark'));
	window.matchMedia('(prefers-color-scheme: light)').addEventListener('change', (e) => e.matches && applyScheme('light'));

})(window, document);

}
window.addEventListener("load", ready, false);
