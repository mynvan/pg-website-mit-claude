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

/* Hub-Grafik und Besucherweg: werden eingeblendet und gezeichnet, sobald sie im Bild erscheinen. */
( function () {
	var hubs = document.querySelectorAll( '.hub, .journey' );
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

/* Hero-Overlays: SVG-Animationen starten bei jedem Seitenaufruf neu.
   Der Browser teilt sich sonst ein bereits geladenes SVG-Bild, dessen Animation dann nicht neu beginnt. */
( function () {
	if ( ! document.querySelector( '.hero[style*="background-image"], .hero--arcs' ) ) {
		return;
	}
	var root = document.documentElement;
	var stamp = encodeURIComponent( '<!--' + Date.now() + '-->' );
	[ '--deco-hero', '--deco-arcs-live' ].forEach( function ( name ) {
		var v = window.getComputedStyle( root ).getPropertyValue( name ).trim();
		var m = v.match( /^url\((["']?)(.*)\1\)$/ );
		if ( m ) {
			root.style.setProperty( name, 'url("' + m[2] + stamp + '")' );
		}
	} );
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

/* Ablauf-Animation: folgt dem Scrollen. Der Schritt, der die Leselinie erreicht, bestimmt die Grafik; Klick spielt ihn erneut ab. */
( function () {
	var root = document.querySelector( '[data-process]' );
	if ( ! root ) {
		return;
	}
	var stage = root.querySelector( '.process__stage' );
	var steps = Array.prototype.slice.call( root.querySelectorAll( '.process__step' ) );
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var state = 5, ticking = false;

	function set( n ) {
		state = n;
		stage.setAttribute( 'data-state', n );
		steps.forEach( function ( li ) {
			var on = parseInt( li.getAttribute( 'data-step' ), 10 ) === n;
			li.classList.toggle( 'is-active', on );
			var b = li.querySelector( '.process__btn' );
			if ( on ) {
				b.setAttribute( 'aria-current', 'step' );
			} else {
				b.removeAttribute( 'aria-current' );
			}
		} );
	}
	if ( reduce ) {
		return;
	}
	function line() {
		var r = stage.getBoundingClientRect();
		return window.innerWidth < 900 ? r.bottom + 56 : window.innerHeight * 0.4;
	}
	function update() {
		ticking = false;
		var y = line(), n = 1;
		steps.forEach( function ( li, i ) {
			if ( li.getBoundingClientRect().top <= y ) {
				n = i + 1;
			}
		} );
		if ( n !== state ) {
			set( n );
		}
	}
	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}
	steps.forEach( function ( li ) {
		li.addEventListener( 'click', function () {
			var n = parseInt( li.getAttribute( 'data-step' ), 10 );
			set( 0 );
			void stage.offsetWidth;
			set( n );
		} );
	} );
	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll );
	set( 1 );
	update();
} )();

/* FAQ: weiches Auf- und Zuklappen (Höhe per Web Animations API, unterbrechbar). */
( function () {
	var items = document.querySelectorAll( '.faq__item' );
	if ( ! items.length || ! Element.prototype.animate ) {
		return;
	}
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( reduce ) {
		return;
	}
	var EASE = 'cubic-bezier(0.23, 1, 0.32, 1)';
	items.forEach( function ( d ) {
		var summary = d.querySelector( 'summary' );
		var answer = d.querySelector( '.faq__a' );
		var anim = null;
		summary.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var start = d.offsetHeight;
			var closing = d.open && ! d.classList.contains( 'is-closing' );
			if ( anim ) {
				anim.cancel();
			}
			d.style.overflow = 'hidden';
			var end;
			if ( closing ) {
				d.classList.add( 'is-closing' );
				end = summary.offsetHeight + ( d.offsetHeight - d.clientHeight );
			} else {
				d.classList.remove( 'is-closing' );
				d.open = true;
				end = d.offsetHeight;
				answer.animate( { opacity: [ 0, 1 ] }, { duration: 260, easing: EASE } );
			}
			anim = d.animate( { height: [ start + 'px', end + 'px' ] }, { duration: 300, easing: EASE } );
			anim.onfinish = function () {
				if ( closing ) {
					d.open = false;
					d.classList.remove( 'is-closing' );
				}
				d.style.overflow = '';
				anim = null;
			};
		} );
	} );
} )();
