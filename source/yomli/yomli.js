

var ready = function() {
	'use strict';
/* See comments of https://dev.to/mrahmadawais/use-instead-of-document-queryselector-all-in-javascript-without-jquery-3ef1 */
// const $ = (css, parent = document) => parent.querySelector(css);
// const $$ = (css, parent = document) => Array.from(parent.querySelectorAll(css));
const $ = (q, d = document) => 
	/#\S+$/.test(q)							// check if query asks for ID
	? d.querySelector.bind(d)(q)			// if so, return one element
	: [...d.querySelectorAll.bind(d)(q)];	// else, return all elements in an array.

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
	// Get value in localStorage and apply body class
	if (!('localStorage' in window)) return;
	var darkMode = localStorage.getItem('color-scheme-dark');
	
	body.className += (!darkMode) ? ' color-scheme-light' : ' color-scheme-dark';
	
	// Get button
	var colorModeButton = $('.color-scheme-toggle');
	if (!colorModeButton) return;
		
	// When clicked, toggle color mode
	[].forEach.call(colorModeButton, function(button) {
		button.addEventListener('click', function(event) {
			//event.preventDefault();
			body.classList.toggle('color-scheme-dark');
			body.classList.toggle('color-scheme-light');

			// Clear focus
			document.activeElement.blur();
			
			// Make night mode persistant
			if(body.classList.contains('color-scheme-dark')) {
				localStorage.setItem('color-scheme-dark', true);
				return;
			}
			localStorage.removeItem('color-scheme-dark');
		}, false);

	});
		
})(window, document);
}
window.addEventListener("load", ready, false);
