( function () {
	var toggle = document.querySelector( '.nav-toggle' );
	var nav = document.getElementById( 'site-navigation' );
	if ( toggle && nav ) {
	toggle.addEventListener( 'click', function () {
		var open = nav.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		}
	} );
	}
} )();

/* Hub-Grafik: wird eingeblendet und gezeichnet, sobald sie im Bild erscheint. */
( function () {
	var hubs = document.querySelectorAll( '.hub' );
	if ( ! hubs.length ) {
		return;
	}
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduce || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}
	var io = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			if ( entry.isIntersecting ) {
				entry.target.classList.add( 'is-visible' );
				io.unobserve( entry.target );
			}
		} );
	}, { threshold: 0.25 } );
	hubs.forEach( function ( hub ) {
		hub.classList.add( 'is-armed' );
		io.observe( hub );
	} );
} )();
