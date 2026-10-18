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

/* Rezensions-Slider: Pfeile zum Blättern, Wischen und Tastatur funktionieren nativ. */
( function () {
	document.querySelectorAll( '.review-slider' ).forEach( function ( slider ) {
		var nav = document.createElement( 'div' );
		nav.className = 'review-nav';
		var prev = document.createElement( 'button' );
		var next = document.createElement( 'button' );
		prev.type = next.type = 'button';
		prev.setAttribute( 'aria-label', 'Vorherige Rezension' );
		next.setAttribute( 'aria-label', 'Nächste Rezension' );
		prev.textContent = '\u2190';
		next.textContent = '\u2192';
		nav.appendChild( prev );
		nav.appendChild( next );
		slider.parentNode.insertBefore( nav, slider.nextSibling );
		var more = document.querySelector( '.review__more' );
		if ( more ) {
			nav.appendChild( more );
		}
		slider.setAttribute( 'tabindex', '0' );
		slider.setAttribute( 'aria-label', 'Kundenstimmen' );
		function step() {
			var c = slider.querySelector( '.review' );
			return c ? c.getBoundingClientRect().width + 24 : 300;
		}
		function update() {
			prev.disabled = slider.scrollLeft < 4;
			next.disabled = slider.scrollLeft + slider.clientWidth >= slider.scrollWidth - 4;
		}
		prev.addEventListener( 'click', function () { slider.scrollBy( { left: -step() } ); } );
		next.addEventListener( 'click', function () { slider.scrollBy( { left: step() } ); } );
		slider.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	} );
} )();

/* Hero-Overlay: SVG-Animationen starten bei jedem Seitenaufruf neu.
   Der Browser teilt sich sonst ein bereits geladenes SVG-Bild, dessen Animation dann nicht neu beginnt. */
( function () {
	if ( ! document.querySelector( '.hero[style*="background-image"]' ) ) {
		return;
	}
	var root = document.documentElement;
	var v = window.getComputedStyle( root ).getPropertyValue( '--deco-hero' ).trim();
	var m = v.match( /^url\((["']?)(.*)\1\)$/ );
	if ( ! m ) {
		return;
	}
	var stamp = encodeURIComponent( '<!--' + Date.now() + '-->' );
	root.style.setProperty( '--deco-hero', 'url("' + m[2] + stamp + '")' );
} )();

/* Hub-Grafik: Dauerbewegung pausiert, solange die Grafik nicht im Bild ist. */
( function () {
	var hubs = document.querySelectorAll( '.hub' );
	if ( ! hubs.length || ! ( 'IntersectionObserver' in window ) ) {
		return;
	}
	var io = new IntersectionObserver( function ( entries ) {
		entries.forEach( function ( entry ) {
			entry.target.classList.toggle( 'is-offscreen', ! entry.isIntersecting );
		} );
	} );
	hubs.forEach( function ( hub ) { io.observe( hub ); } );
} )();
