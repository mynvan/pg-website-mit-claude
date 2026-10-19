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
	var hubs = document.querySelectorAll( '.hub, .journey, .ref-row' );
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

/* Referenzen: Umschalter */
( function () {
	document.querySelectorAll( '[data-reftabs]' ).forEach( function ( root ) {
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '[role="tab"]' ) );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '[role="tabpanel"]' ) );
		function show( i, focus ) {
			tabs.forEach( function ( t, j ) {
				t.setAttribute( 'aria-selected', j === i ? 'true' : 'false' );
				t.tabIndex = j === i ? 0 : -1;
			} );
			panels.forEach( function ( p, j ) {
				p.hidden = j !== i;
				p.classList.toggle( 'is-active', j === i );
			} );
			if ( focus ) { tabs[ i ].focus(); }
		}
		tabs.forEach( function ( t, i ) {
			t.addEventListener( 'click', function () { show( i ); } );
			t.addEventListener( 'keydown', function ( e ) {
				var n = e.key === 'ArrowRight' ? ( i + 1 ) % tabs.length : e.key === 'ArrowLeft' ? ( i + tabs.length - 1 ) % tabs.length : -1;
				if ( n > -1 ) { e.preventDefault(); show( n, true ); }
			} );
		} );
	} );
} )();

/* Relaunch: "Einmal pflegen. Mehrfach ausgeben." – drei Zustände, einmal automatisch, danach manuell */
( function () {
	var root = document.querySelector( '[data-keep]' );
	if ( ! root ) {
		return;
	}
	var tabs = Array.prototype.slice.call( root.querySelectorAll( '.keep__tabs button' ) );
	var play = root.querySelector( '.keep__play' );
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var state = 3, timer = null;

	function set( n ) {
		state = n;
		root.setAttribute( 'data-state', n );
		tabs.forEach( function ( b ) { b.setAttribute( 'aria-pressed', String( parseInt( b.getAttribute( 'data-s' ), 10 ) === n ) ); } );
	}
	function label() {
		play.textContent = timer ? 'Pause' : ( state >= 3 ? 'Nochmal abspielen' : 'Weiter' );
	}
	function stop() {
		clearTimeout( timer );
		timer = null;
	}
	function run() {
		stop();
		if ( state >= 3 ) {
			label();
			return;
		}
		timer = setTimeout( function () {
			timer = null;
			set( state + 1 );
			run();
		}, 2400 );
		label();
	}
	tabs.forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			stop();
			set( parseInt( b.getAttribute( 'data-s' ), 10 ) );
			if ( play ) { label(); }
		} );
	} );
	if ( reduce || ! play ) {
		return;
	}
	play.hidden = false;
	play.addEventListener( 'click', function () {
		if ( timer ) {
			stop();
		} else {
			if ( state >= 3 ) { set( 1 ); }
			run();
			return;
		}
		label();
	} );
	set( 1 );
	label();
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			if ( entries[ 0 ].isIntersecting ) {
				io.disconnect();
				run();
			}
		}, { threshold: 0.5 } );
		io.observe( root );
	} else {
		set( 3 );
	}
} )();

/* Wartung: gemeinsamer Hosting-Schalter für beide Preisspalten (ohne JS bleibt die Textzeile sichtbar). */
( function () {
	document.querySelectorAll( '[data-plans]' ).forEach( function ( root ) {
		var input = root.querySelector( '[data-hosting]' );
		var sw = root.querySelector( '.plans__switch' );
		var status = root.querySelector( '[data-plans-status]' );
		if ( ! input || ! sw ) {
			return;
		}
		root.classList.add( 'is-js' );
		sw.hidden = false;
		function update() {
			var on = input.checked;
			root.classList.toggle( 'is-hosting', on );
			var said = [];
			root.querySelectorAll( '.plan__price' ).forEach( function ( p ) {
				var v = p.getAttribute( on ? 'data-host' : 'data-base' );
				p.querySelector( '.plan__amount' ).textContent = v + ' €';
				said.push( v + ' €' );
			} );
			if ( status ) {
				status.textContent = ( on ? 'Mit Hosting inklusive E-Mail: ' : 'Ohne Hosting: ' ) + said.join( ' und ' ) + ' netto pro Monat.';
			}
		}
		input.addEventListener( 'change', update );
		update();
	} );
} )();

/* Wartung: Ablauf in vier Zuständen. Statisch beim Laden, Start per Button, einmal durch, danach manuell. */
( function () {
	var root = document.querySelector( '[data-care]' );
	if ( ! root ) {
		return;
	}
	var tabs = Array.prototype.slice.call( root.querySelectorAll( '.care__tabs button' ) );
	var play = root.querySelector( '.care__play' );
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var LAST = 4, state = 1, timer = null;

	function set( n ) {
		state = n;
		root.setAttribute( 'data-state', n );
		tabs.forEach( function ( b ) { b.setAttribute( 'aria-pressed', String( parseInt( b.getAttribute( 'data-s' ), 10 ) === n ) ); } );
	}
	function replay( n ) {
		root.classList.add( 'is-reset' );
		root.setAttribute( 'data-state', '0' );
		void root.offsetWidth;
		root.classList.remove( 'is-reset' );
		set( n );
	}
	function label() {
		if ( play ) {
			play.textContent = timer ? 'Pause' : ( state >= LAST ? 'Nochmal abspielen' : ( state > 1 ? 'Weiter abspielen' : 'Ablauf abspielen' ) );
		}
	}
	function stop() {
		clearTimeout( timer );
		timer = null;
	}
	function run() {
		stop();
		timer = setTimeout( function () {
			timer = null;
			if ( state < LAST ) {
				set( state + 1 );
				if ( state < LAST ) {
					run();
					return;
				}
			}
			label();
		}, 1800 );
		label();
	}
	tabs.forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			stop();
			replay( parseInt( b.getAttribute( 'data-s' ), 10 ) );
			label();
		} );
	} );
	if ( reduce || ! play ) {
		return;
	}
	play.hidden = false;
	play.addEventListener( 'click', function () {
		if ( timer ) {
			stop();
			label();
			return;
		}
		if ( state >= LAST ) {
			replay( 1 );
		}
		run();
	} );
	label();
} )();

/* Projektanfrage: Paketfelder nur bei "Wartung & Betreuung". */
( function () {
	var topic = document.getElementById( 'gf-topic' );
	var care = document.querySelector( '[data-care-fields]' );
	if ( ! topic || ! care ) {
		return;
	}
	var msg = document.getElementById( 'gf-msg' );
	var holders = {};
	try { holders = JSON.parse( ( msg && msg.getAttribute( 'data-placeholders' ) ) || '{}' ); } catch ( e ) {}
	function sync() {
		care.hidden = topic.value !== 'Wartung & Betreuung';
		if ( msg && holders[ topic.value ] ) {
			msg.placeholder = holders[ topic.value ];
		}
	}
	topic.addEventListener( 'change', sync );
	sync();
} )();

/* Wartung: Paket-Buttons übernehmen die Hosting-Auswahl in die Anfrage. */
( function () {
	var input = document.querySelector( '[data-plans] [data-hosting]' );
	if ( ! input ) {
		return;
	}
	var links = document.querySelectorAll( '[data-plans] a[data-paket]' );
	function sync() {
		links.forEach( function ( a ) {
			var url = new URL( a.href, window.location.href );
			if ( input.checked ) {
				url.searchParams.set( 'hosting', '1' );
			} else {
				url.searchParams.delete( 'hosting' );
			}
			a.href = url.pathname + url.search;
		} );
	}
	input.addEventListener( 'change', sync );
	sync();
} )();
