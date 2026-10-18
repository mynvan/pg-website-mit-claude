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

/* Ablauf-Animation: fünf Zustände, einmalige Wiedergabe beim Einblenden, Schritte und Pause bedienbar. */
( function () {
	var root = document.querySelector( '[data-process]' );
	if ( ! root ) {
		return;
	}
	var stage = root.querySelector( '.process__stage' );
	var steps = Array.prototype.slice.call( root.querySelectorAll( '.process__step' ) );
	var playBtn = root.querySelector( '.process__play' );
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var state = 5, timer = null, playing = false, paused = false;

	function set( n ) {
		state = n;
		stage.setAttribute( 'data-state', n );
		steps.forEach( function ( li ) {
			var on = parseInt( li.getAttribute( 'data-step' ), 10 ) === n;
			li.classList.toggle( 'is-active', on );
			if ( on ) {
				li.querySelector( '.process__btn' ).setAttribute( 'aria-current', 'step' );
			} else {
				li.querySelector( '.process__btn' ).removeAttribute( 'aria-current' );
			}
		} );
	}
	function label() {
		if ( ! playBtn ) { return; }
		playBtn.textContent = playing ? 'Pause' : ( paused ? 'Fortsetzen' : ( state === 5 ? 'Nochmal ansehen' : 'Ablauf abspielen' ) );
	}
	function stop() {
		window.clearTimeout( timer );
		timer = null;
		playing = false;
	}
	function tick() {
		timer = window.setTimeout( function () {
			if ( state < 5 ) {
				set( state + 1 );
			}
			if ( state < 5 ) {
				tick();
			} else {
				stop();
				paused = false;
				label();
			}
		}, 2000 );
	}
	function play( from ) {
		stop();
		paused = false;
		set( from );
		playing = true;
		label();
		tick();
	}

	steps.forEach( function ( li ) {
		li.querySelector( '.process__btn' ).addEventListener( 'click', function () {
			stop();
			paused = false;
			set( parseInt( li.getAttribute( 'data-step' ), 10 ) );
			label();
		} );
	} );

	if ( reduce ) {
		return;
	}
	set( 1 );
	if ( playBtn ) {
		playBtn.hidden = false;
		label();
		playBtn.addEventListener( 'click', function () {
			if ( playing ) {
				stop();
				paused = true;
				label();
			} else if ( paused ) {
				paused = false;
				playing = true;
				label();
				tick();
			} else {
				play( 1 );
			}
		} );
	}
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			if ( entries[ 0 ].isIntersecting ) {
				io.disconnect();
				play( 1 );
			}
		}, { threshold: 0.5 } );
		io.observe( stage );
	} else {
		set( 5 );
	}
} )();
