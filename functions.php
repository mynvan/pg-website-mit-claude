<?php
/**
 * Theme-Funktionen: Gebhart
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GEBHART_VERSION', wp_get_theme()->get( 'Version' ) );

require_once get_template_directory() . '/inc/referenzen.php';

/**
 * Grundeinstellungen des Themes.
 */
function gebhart_setup() {
	load_theme_textdomain( 'gebhart', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 88,
			'width'       => 320,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => 'Hauptmenü',
			'footer'  => 'Footer-Menü',
		)
	);

	add_image_size( 'gebhart-card', 960, 720, true );
	add_image_size( 'gebhart-wide', 1600, 900, true );
}
add_action( 'after_setup_theme', 'gebhart_setup' );

/**
 * Styles und Skripte. Keine externen Schriften: Georgia und Systemschrift.
 */
function gebhart_assets() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();

	wp_enqueue_style(
		'gebhart-style',
		get_stylesheet_uri(),
		array(),
		file_exists( $dir . '/style.css' ) ? filemtime( $dir . '/style.css' ) : GEBHART_VERSION
	);

	wp_enqueue_script(
		'gebhart-nav',
		$uri . '/assets/js/nav.js',
		array(),
		file_exists( $dir . '/assets/js/nav.js' ) ? filemtime( $dir . '/assets/js/nav.js' ) : GEBHART_VERSION,
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gebhart_assets' );

/**
 * Emoji-Skripte entfernen (weniger Last, keine externen Aufrufe).
 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/**
 * Block-Editor: Theme-Stile im Editor laden.
 */
function gebhart_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );
}
add_action( 'after_setup_theme', 'gebhart_editor_styles' );

/**
 * Block-Pattern-Kategorie.
 */
function gebhart_pattern_category() {
	register_block_pattern_category(
		'gebhart',
		array( 'label' => 'Gebhart' )
	);
}
add_action( 'init', 'gebhart_pattern_category' );

/**
 * Kurzer Auszug.
 */
add_filter(
	'excerpt_length',
	function () {
		return 28;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '…';
	}
);

/**
 * Lesezeit in Minuten (200 Wörter pro Minute).
 */
function gebhart_reading_time( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$words   = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) );
	return max( 1, (int) ceil( $words / 200 ) );
}

/**
 * Überschriften (h2) im Beitrag mit Anker versehen und Inhaltsverzeichnis erzeugen.
 *
 * @return array { content: string, toc: array<int, array{id:string,title:string}> }
 */
function gebhart_prepare_content( $html ) {
	$toc  = array();
	$used = array();

	$html = preg_replace_callback(
		'/<h2([^>]*)>(.*?)<\/h2>/is',
		function ( $m ) use ( &$toc, &$used ) {
			$title = trim( wp_strip_all_tags( $m[2] ) );
			if ( '' === $title ) {
				return $m[0];
			}
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $m[1], $idm ) ) {
				$id    = $idm[1];
				$attrs = $m[1];
			} else {
				$base = sanitize_title( $title );
				$id   = $base ? $base : 'abschnitt';
				$n    = 2;
				while ( in_array( $id, $used, true ) ) {
					$id = $base . '-' . $n;
					++$n;
				}
				$attrs = $m[1] . ' id="' . esc_attr( $id ) . '"';
			}
			$used[] = $id;
			$toc[]  = array(
				'id'    => $id,
				'title' => $title,
			);
			return '<h2' . $attrs . '>' . $m[2] . '</h2>';
		},
		$html
	);

	return array(
		'content' => $html,
		'toc'     => $toc,
	);
}

/**
 * Menü-Fallback, solange im Backend noch kein Menü zugewiesen ist.
 */
function gebhart_fallback_menu() {
	$posts_page = (int) get_option( 'page_for_posts' );
	$ref_link   = function_exists( 'post_type_exists' ) && post_type_exists( 'referenz' ) ? get_post_type_archive_link( 'referenz' ) : home_url( '/referenzen/' );
	$wissen     = $posts_page ? get_permalink( $posts_page ) : home_url( '/wissen/' );

	$items = array(
		array( 'Startseite', home_url( '/' ), array() ),
		array(
			'Leistungen',
			home_url( '/leistungen/' ),
			array(
				array( 'WordPress-Websites', home_url( '/wordpress-website/' ) ),
				array( 'Website-Relaunch', home_url( '/website-relaunch/' ) ),
				array( 'WordPress-Wartung & Betreuung', home_url( '/wordpress-wartung/' ) ),
				array( 'SEO', home_url( '/suchmaschinenoptimierung-seo/' ) ),
			),
		),
		array( 'Referenzen', $ref_link, array() ),
		array( 'Über mich', home_url( '/ueber-mich/' ), array() ),
		array( 'Wissen', $wissen, array() ),
	);

	echo '<ul class="menu">';
	foreach ( $items as $item ) {
		$has_children = ! empty( $item[2] );
		echo '<li class="menu-item' . ( $has_children ? ' menu-item-has-children' : '' ) . '">';
		echo '<a href="' . esc_url( $item[1] ) . '">' . esc_html( $item[0] ) . '</a>';
		if ( $has_children ) {
			echo '<ul class="sub-menu">';
			foreach ( $item[2] as $child ) {
				echo '<li class="menu-item"><a href="' . esc_url( $child[1] ) . '">' . esc_html( $child[0] ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Referenz-Feld lesen (funktioniert auch ohne aktives Plugin).
 */
function gebhart_ref( $key, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, $key, true );
}

/**
 * Referenz-Textfeld als HTML ausgeben.
 * Leerzeile = neuer Absatz, Zeile mit "## " = Zwischenüberschrift,
 * Zeilen mit "- " = Liste; "- Begriff: Text" setzt den Begriff fett.
 */
function gebhart_ref_format( $text ) {
	$text = str_replace( "\r\n", "\n", trim( (string) $text ) );
	if ( '' === $text ) {
		return '';
	}
	$out = '';
	foreach ( preg_split( "/\n\s*\n/", $text ) as $block ) {
		$para = array();
		$list = array();
		$flush_para = function () use ( &$para, &$out ) {
			if ( $para ) {
				$out .= '<p>' . implode( '<br>', array_map( 'esc_html', $para ) ) . '</p>';
				$para = array();
			}
		};
		$flush_list = function () use ( &$list, &$out ) {
			if ( $list ) {
				$out .= '<ul class="check-list">' . implode( '', $list ) . '</ul>';
				$list = array();
			}
		};
		foreach ( explode( "\n", $block ) as $line ) {
			$line = trim( $line );
			if ( 0 === strpos( $line, '## ' ) ) {
				$flush_para();
				$flush_list();
				$out .= '<h3>' . esc_html( substr( $line, 3 ) ) . '</h3>';
			} elseif ( preg_match( '/^[-–•]\s+(.*)$/u', $line, $m ) ) {
				$flush_para();
				$item = $m[1];
				if ( preg_match( '/^([^:]{2,60}):\s+(.+)$/u', $item, $kv ) ) {
					$list[] = '<li><strong>' . esc_html( $kv[1] ) . ':</strong> ' . esc_html( $kv[2] ) . '</li>';
				} else {
					$list[] = '<li>' . esc_html( $item ) . '</li>';
				}
			} elseif ( '' !== $line ) {
				$flush_list();
				$para[] = $line;
			}
		}
		$flush_para();
		$flush_list();
	}
	return $out;
}

/**
 * Shortcode [gebhart_referenzen limit="2"]: Referenzen als Zeilen, zum Beispiel auf der Startseite.
 */
function gebhart_referenzen_shortcode( $atts ) {
	if ( ! post_type_exists( 'referenz' ) ) {
		return '';
	}
	$atts = shortcode_atts(
		array(
			'limit'    => 2,
			'leistung' => '',     // Slug aus der Taxonomie "leistung", z. B. website-relaunch.
			'exclude'  => '',     // Komma-getrennte Referenz-Slugs, die nicht erscheinen sollen.
			'style'    => 'row',  // "row" = große Zeilen (Startseite), "mini" = kompakte Liste.
		),
		$atts,
		'gebhart_referenzen'
	);
	$args = array(
		'post_type'      => 'referenz',
		'posts_per_page' => max( 1, (int) $atts['limit'] ),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
		'no_found_rows'  => true,
	);
	if ( '' !== $atts['leistung'] ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'leistung',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_title', explode( ',', $atts['leistung'] ) ),
			),
		);
	}
	if ( '' !== $atts['exclude'] ) {
		$ids = array();
		foreach ( explode( ',', $atts['exclude'] ) as $slug ) {
			$p = get_page_by_path( sanitize_title( $slug ), OBJECT, 'referenz' );
			if ( $p ) {
				$ids[] = $p->ID;
			}
		}
		if ( $ids ) {
			$args['post__not_in'] = $ids;
		}
	}
	$query = new WP_Query( $args );
	if ( ! $query->have_posts() ) {
		return '';
	}
	ob_start();
	if ( 'mini' === $atts['style'] ) {
		echo '<div class="ref-minis">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$meta = gebhart_ref( 'ref_untertitel' );
			echo '<div class="ref-mini">';
			if ( has_post_thumbnail() ) {
				echo '<figure class="shot"><a href="' . esc_url( get_permalink() ) . '" aria-hidden="true" tabindex="-1">' . get_the_post_thumbnail( null, 'medium_large', array( 'alt' => '' ) ) . '</a></figure>';
			}
			echo '<div class="ref-row__text">';
			if ( $meta ) {
				echo '<p class="ref-row__meta">' . esc_html( $meta ) . '</p>';
			}
			echo '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
			if ( has_excerpt() ) {
				echo '<p>' . esc_html( get_the_excerpt() ) . '</p>';
			}
			echo '<p class="ref-mini__more"><a href="' . esc_url( get_permalink() ) . '">Projekt ansehen →</a></p>';
			echo '</div></div>';
		}
		echo '</div>';
	} else {
		echo '<div class="ref-list">';
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/referenz-row' );
		}
		echo '</div>';
	}
	wp_reset_postdata();
	return ob_get_clean();
}
add_shortcode( 'gebhart_referenzen', 'gebhart_referenzen_shortcode' );

/**
 * Referenzen: Archiv in Reihenfolge der Sortierung (Attribut "Reihenfolge"), dann Datum.
 */
function gebhart_referenzen_order( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'referenz' ) || $query->is_tax( 'leistung' ) ) {
		$query->set( 'posts_per_page', 12 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			)
		);
	}
}
add_action( 'pre_get_posts', 'gebhart_referenzen_order' );

/**
 * Datum im deutschen Format.
 */
function gebhart_date( $post_id = 0 ) {
	return get_the_date( 'd.m.Y', $post_id ? $post_id : get_the_ID() );
}

/**
 * Link zur Projektanfrage.
 */
function gebhart_contact_url() {
	return home_url( '/projektanfrage/' );
}

/* ---------- Kontaktformular für die Projektanfrage ----------
 * Shortcode: [gebhart_kontaktformular]. Schutz: Honeypot, Begrenzung pro IP, Längen- und Link-Prüfung.
 * Empfänger: E-Mail-Adresse unter Einstellungen > Allgemein.
 */

function gebhart_form_topics() {
	return array( 'Neue Website', 'Website-Relaunch', 'Wartung & Betreuung', 'SEO', 'Etwas anderes' );
}

/* Kurzschlüssel aus Links (z. B. ?thema=wartung&paket=plus&hosting=1) für die Vorauswahl. */
function gebhart_form_topic_keys() {
	return array(
		'website'  => 'Neue Website',
		'relaunch' => 'Website-Relaunch',
		'wartung'  => 'Wartung & Betreuung',
		'seo'      => 'SEO',
	);
}

/* Platzhalter für "Dein Vorhaben", passend zum gewählten Thema. */
function gebhart_form_placeholders() {
	return array(
		'Neue Website'        => 'Was soll deine neue Website leisten? Gibt es einen Anlass oder einen Wunschtermin?',
		'Website-Relaunch'    => 'Was stört dich an deiner aktuellen Website? Was soll nach dem Relaunch besser werden?',
		'Wartung & Betreuung' => 'Womit läuft deine Website heute, und wo wird sie gehostet? Was möchtest du an mich abgeben?',
		'SEO'                 => 'Wofür möchtest du bei Google besser gefunden werden? In welcher Region sind deine Kunden?',
		'Etwas anderes'       => 'Beschreib kurz, worum es geht – ich melde mich mit einer ersten Einschätzung.',
	);
}

function gebhart_form_packages() {
	return array(
		''          => 'Noch offen',
		'betreuung' => 'Betreuung (39 € netto / Monat)',
		'plus'      => 'Betreuung Plus (79 € netto / Monat)',
	);
}

function gebhart_contact_form() {
	$status = isset( $_GET['anfrage'] ) ? sanitize_key( wp_unslash( $_GET['anfrage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	// phpcs:disable WordPress.Security.NonceVerification
	$keys      = gebhart_form_topic_keys();
	$pre_key   = isset( $_GET['thema'] ) ? sanitize_key( wp_unslash( $_GET['thema'] ) ) : '';
	$pre_topic = isset( $keys[ $pre_key ] ) ? $keys[ $pre_key ] : '';
	$pre_pack  = isset( $_GET['paket'] ) ? sanitize_key( wp_unslash( $_GET['paket'] ) ) : '';
	$pre_pack  = array_key_exists( $pre_pack, gebhart_form_packages() ) ? $pre_pack : '';
	$pre_host  = ! empty( $_GET['hosting'] );
	// phpcs:enable
	$care_open = 'Wartung & Betreuung' === $pre_topic;
	$holders   = gebhart_form_placeholders();
	$holder    = $holders[ $pre_topic ? $pre_topic : 'Neue Website' ];
	ob_start();

	if ( 'ok' === $status ) {
		echo '<div id="anfrage" class="form-note form-note--ok" role="status"><p><strong>Danke für deine Anfrage!</strong> Ich habe sie erhalten und melde mich bei dir.</p></div>';
		return ob_get_clean();
	}
	if ( 'fehler' === $status ) {
		echo '<div id="anfrage" class="form-note form-note--error" role="alert"><p>Das hat leider nicht geklappt. Bitte versuche es noch einmal oder schreibe mir direkt per E-Mail.</p></div>';
	}
	?>
	<form class="gebhart-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="gebhart_kontakt">
		<div class="gebhart-form__hp" aria-hidden="true">
			<label>Dieses Feld bitte leer lassen <input type="text" name="firma_zusatz" tabindex="-1" autocomplete="off"></label>
		</div>

		<div class="gebhart-form__row">
			<label for="gf-name">Name *</label>
			<input id="gf-name" type="text" name="ct_name" required maxlength="120" placeholder="Vor- und Nachname" autocomplete="name">
		</div>
		<div class="gebhart-form__row">
			<label for="gf-mail">E-Mail *</label>
			<input id="gf-mail" type="email" name="ct_mail" required maxlength="160" placeholder="name@firma.de" autocomplete="email">
		</div>
		<div class="gebhart-form__row">
			<label for="gf-tel">Telefon (optional)</label>
			<input id="gf-tel" type="tel" name="ct_tel" maxlength="60" placeholder="Für einen Rückruf" autocomplete="tel">
		</div>
		<div class="gebhart-form__row">
			<label for="gf-web">Aktuelle Website (optional)</label>
			<input id="gf-web" type="url" name="ct_web" maxlength="200" placeholder="https://">
		</div>
		<div class="gebhart-form__row">
			<label for="gf-topic">Worum geht es?</label>
			<select id="gf-topic" name="ct_topic">
				<?php foreach ( gebhart_form_topics() as $topic ) : ?>
					<option value="<?php echo esc_attr( $topic ); ?>"<?php selected( $topic, $pre_topic ); ?>><?php echo esc_html( $topic ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="gebhart-form__care" data-care-fields<?php echo $care_open ? '' : ' hidden'; ?>>
			<div class="gebhart-form__row">
				<label for="gf-paket">Betreuungspaket</label>
				<select id="gf-paket" name="ct_paket">
					<?php foreach ( gebhart_form_packages() as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $key, $pre_pack ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="gebhart-form__row gebhart-form__check">
				<label>
					<input type="checkbox" name="ct_hosting" value="1"<?php checked( $pre_host ); ?>>
					<span>Hosting inklusive E-Mail dazubuchen (+15 € netto / Monat)</span>
				</label>
			</div>
		</div>
		<div class="gebhart-form__row">
			<label for="gf-msg">Dein Vorhaben *</label>
			<textarea id="gf-msg" name="ct_msg" required rows="6" maxlength="4000" placeholder="<?php echo esc_attr( $holder ); ?>" data-placeholders="<?php echo esc_attr( wp_json_encode( $holders ) ); ?>"></textarea>
		</div>
		<div class="gebhart-form__row gebhart-form__check">
			<label>
				<input type="checkbox" name="ct_privacy" value="1" required>
				<span>Ich habe die <a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/datenschutzerklaerung/' ) ); ?>">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Bearbeitung der Anfrage einverstanden. *</span>
			</label>
		</div>
		<button class="btn" type="submit">Anfrage senden ↗</button>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'gebhart_kontaktformular', 'gebhart_contact_form' );

function gebhart_contact_handle() {
	$back = wp_get_referer();
	$back = $back ? remove_query_arg( 'anfrage', $back ) : home_url( '/projektanfrage/' );
	$done = function ( $state ) use ( $back ) {
		wp_safe_redirect( add_query_arg( 'anfrage', $state, $back ) . '#anfrage' );
		exit;
	};

	// Honeypot: Bots füllen das versteckte Feld aus. Sie bekommen "ok", damit sie es nicht weiter versuchen.
	if ( ! empty( $_POST['firma_zusatz'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$done( 'ok' );
	}

	// Pro IP höchstens 5 Anfragen pro Stunde.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key = 'gebhart_ct_' . md5( $ip );
	$cnt = (int) get_transient( $key );
	if ( $cnt >= 5 ) {
		$done( 'fehler' );
	}
	set_transient( $key, $cnt + 1, HOUR_IN_SECONDS );

	// phpcs:disable WordPress.Security.NonceVerification
	$name  = isset( $_POST['ct_name'] ) ? sanitize_text_field( wp_unslash( $_POST['ct_name'] ) ) : '';
	$mail  = isset( $_POST['ct_mail'] ) ? sanitize_email( wp_unslash( $_POST['ct_mail'] ) ) : '';
	$tel   = isset( $_POST['ct_tel'] ) ? sanitize_text_field( wp_unslash( $_POST['ct_tel'] ) ) : '';
	$web   = isset( $_POST['ct_web'] ) ? esc_url_raw( wp_unslash( $_POST['ct_web'] ) ) : '';
	$topic = isset( $_POST['ct_topic'] ) ? sanitize_text_field( wp_unslash( $_POST['ct_topic'] ) ) : '';
	$msg   = isset( $_POST['ct_msg'] ) ? sanitize_textarea_field( wp_unslash( $_POST['ct_msg'] ) ) : '';
	$priv  = ! empty( $_POST['ct_privacy'] );
	$pack  = isset( $_POST['ct_paket'] ) ? sanitize_key( wp_unslash( $_POST['ct_paket'] ) ) : '';
	$host  = ! empty( $_POST['ct_hosting'] );
	// phpcs:enable
	$packs = gebhart_form_packages();
	$pack  = array_key_exists( $pack, $packs ) ? $pack : '';

	if ( ! in_array( $topic, gebhart_form_topics(), true ) ) {
		$topic = '';
	}
	if ( '' === $name || ! is_email( $mail ) || '' === $msg || ! $priv || mb_strlen( $msg ) > 4000 || substr_count( strtolower( $msg ), 'http' ) > 3 ) {
		$done( 'fehler' );
	}

	$body  = 'Neue Projektanfrage über ' . home_url( '/' ) . "\n\n";
	$body .= "Name: $name\nE-Mail: $mail\nTelefon: " . ( $tel ? $tel : '–' ) . "\nWebsite: " . ( $web ? $web : '–' ) . "\nThema: " . ( $topic ? $topic : '–' ) . "\n\n";
	if ( 'Wartung & Betreuung' === $topic ) {
		$body .= 'Paket: ' . $packs[ $pack ] . "\nHosting inkl. E-Mail: " . ( $host ? 'ja' : 'nein' ) . "\n\n";
	}
	$body .= "Vorhaben:\n$msg\n\n";
	$body .= "Datenschutzerklärung zum Zeitpunkt der Anfrage bestätigt: ja\n";

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $mail . '>',
	);
	$sent = wp_mail( get_option( 'admin_email' ), 'Projektanfrage von ' . $name, $body, $headers );
	if ( ! $sent ) {
		$done( 'fehler' );
	}

	// Kopie an den Absender (nur Text, keine Links aus der Nachricht werden verändert).
	$copy  = 'Hallo ' . $name . ",\n\n";
	$copy .= "danke für deine Anfrage! Ich habe sie erhalten, schaue sie mir in Ruhe an und melde mich zeitnah bei dir.\n\n";
	$copy .= "Zur Sicherheit hier noch einmal deine Angaben:\n\n";
	$copy .= 'Thema: ' . ( $topic ? $topic : '–' ) . "\n";
	if ( 'Wartung & Betreuung' === $topic ) {
		$copy .= 'Paket: ' . $packs[ $pack ] . "\nHosting inkl. E-Mail: " . ( $host ? 'ja' : 'nein' ) . "\n";
	}
	$copy .= 'Website: ' . ( $web ? $web : '–' ) . "\nTelefon: " . ( $tel ? $tel : '–' ) . "\n\nDein Vorhaben:\n$msg\n\n";
	$copy .= "Etwas vergessen? Antworte einfach auf diese E-Mail.\n\nViele Grüße\nPhilipp Gebhart\n0177 8362302 · " . home_url( '/' ) . "\n";
	wp_mail(
		$mail,
		'Deine Anfrage bei Philipp Gebhart',
		$copy,
		array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: Philipp Gebhart <' . get_option( 'admin_email' ) . '>',
		)
	);

	// Danke-Seite: Angaben 30 Minuten unter einem Zufallsschlüssel merken (keine persönlichen Daten in der URL).
	$thanks = get_page_by_path( 'dankesseite' );
	if ( $thanks && 'publish' === $thanks->post_status ) {
		$token = wp_generate_password( 20, false, false );
		set_transient(
			'gebhart_ty_' . $token,
			array(
				'name'  => $name,
				'topic' => $topic,
				'pack'  => 'Wartung & Betreuung' === $topic ? $packs[ $pack ] : '',
				'host'  => 'Wartung & Betreuung' === $topic ? $host : null,
				'web'   => $web,
				'tel'   => $tel,
				'msg'   => $msg,
			),
			30 * MINUTE_IN_SECONDS
		);
		wp_safe_redirect( add_query_arg( 'a', $token, get_permalink( $thanks ) ) );
		exit;
	}
	$done( 'ok' );
}

/**
 * Danke-Seite: gemerkte Angaben aus der Anfrage (oder null).
 */
function gebhart_thanks_data() {
	static $data = false;
	if ( false === $data ) {
		$token = isset( $_GET['a'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', wp_unslash( $_GET['a'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$data  = $token ? get_transient( 'gebhart_ty_' . $token ) : null;
		$data  = is_array( $data ) ? $data : null;
	}
	return $data;
}

// [gebhart_danke_gruss]: persönliche Begrüßung mit Vorname, sonst neutral.
add_shortcode(
	'gebhart_danke_gruss',
	function () {
		$d = gebhart_thanks_data();
		if ( $d && '' !== $d['name'] ) {
			$first = strtok( $d['name'], ' ' );
			return '<p class="danke__lead">Hallo ' . esc_html( $first ) . ', ich habe deine Nachricht erhalten und kümmere mich persönlich darum. Eine Kopie deiner Anfrage ist auch per E-Mail an dich unterwegs.</p>';
		}
		return '<p class="danke__lead">Ich habe deine Nachricht erhalten und kümmere mich persönlich darum.</p>';
	}
);

// [gebhart_danke_angaben]: Zusammenfassung der abgeschickten Angaben.
add_shortcode(
	'gebhart_danke_angaben',
	function () {
		$d = gebhart_thanks_data();
		if ( ! $d ) {
			return '';
		}
		$rows = array(
			'Thema'       => $d['topic'],
			'Paket'       => $d['pack'],
			'Hosting'     => null === $d['host'] ? '' : ( $d['host'] ? 'inklusive E-Mail dazubuchen' : '' ),
			'Website'     => $d['web'],
			'Telefon'     => $d['tel'],
		);
		$out = '<div class="danke-wrap"><div class="danke-box"><p class="meta-label">Deine Angaben</p><dl class="danke-box__rows">';
		foreach ( $rows as $label => $val ) {
			if ( '' !== (string) $val ) {
				$out .= '<div><dt>' . esc_html( $label ) . '</dt><dd>' . esc_html( $val ) . '</dd></div>';
			}
		}
		$out .= '</dl><p class="meta-label">Dein Vorhaben</p><div class="danke-box__msg">' . nl2br( esc_html( $d['msg'] ) ) . '</div>';
		$out .= '<p class="danke-box__hint">Etwas vergessen? Schreib mir einfach an <a href="mailto:info@philippgebhart.de">info@philippgebhart.de</a>.</p></div></div>';
		return $out;
	}
);

// Danke-Seite nicht indexieren und nicht in die Sitemap aufnehmen.
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_page( 'dankesseite' ) ) {
			$robots['noindex']  = true;
			$robots['nofollow'] = true;
		}
		return $robots;
	}
);
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$thanks = get_page_by_path( 'dankesseite' );
			if ( $thanks ) {
				$args['post__not_in'] = array_merge( isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array(), array( $thanks->ID ) );
			}
		}
		return $args;
	},
	10,
	2
);
add_action( 'admin_post_nopriv_gebhart_kontakt', 'gebhart_contact_handle' );
add_action( 'admin_post_gebhart_kontakt', 'gebhart_contact_handle' );

/* ---------- SEO-Titel und Meta-Beschreibung ----------
 * Schlanke Felder für Seiten und Beiträge (Box "SEO" im Editor). Ist ein SEO-Plugin aktiv
 * (Yoast, Rank Math, SEOPress), übernimmt das Plugin und diese Ausgabe entfällt.
 */
function gebhart_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'SEOPRESS_VERSION' );
}

function gebhart_seo_register_meta() {
	foreach ( array( 'page', 'post' ) as $type ) {
		foreach ( array( '_gebhart_seo_title', '_gebhart_seo_desc' ) as $key ) {
			register_post_meta(
				$type,
				$key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
						return current_user_can( 'edit_post', $post_id );
					},
				)
			);
		}
	}
}
add_action( 'init', 'gebhart_seo_register_meta' );

function gebhart_seo_box() {
	if ( gebhart_seo_plugin_active() ) {
		return;
	}
	add_meta_box( 'gebhart-seo', 'SEO', 'gebhart_seo_box_render', array( 'page', 'post' ), 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'gebhart_seo_box' );

function gebhart_seo_box_render( $post ) {
	wp_nonce_field( 'gebhart_seo', 'gebhart_seo_nonce' );
	$title = get_post_meta( $post->ID, '_gebhart_seo_title', true );
	$desc  = get_post_meta( $post->ID, '_gebhart_seo_desc', true );
	?>
	<p><label for="gebhart-seo-title"><strong>SEO-Titel</strong> (leer = Seitentitel – Website-Name)</label><br>
	<input type="text" id="gebhart-seo-title" name="gebhart_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%" maxlength="70"></p>
	<p><label for="gebhart-seo-desc"><strong>Meta-Beschreibung</strong> (ca. 120–160 Zeichen)</label><br>
	<textarea id="gebhart-seo-desc" name="gebhart_seo_desc" rows="3" style="width:100%" maxlength="320"><?php echo esc_textarea( $desc ); ?></textarea></p>
	<?php
}

function gebhart_seo_save( $post_id ) {
	if ( ! isset( $_POST['gebhart_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gebhart_seo_nonce'] ) ), 'gebhart_seo' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 'gebhart_seo_title' => '_gebhart_seo_title', 'gebhart_seo_desc' => '_gebhart_seo_desc' ) as $field => $key ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post', 'gebhart_seo_save' );

add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( gebhart_seo_plugin_active() || ! is_singular() ) {
			return $title;
		}
		$custom = get_post_meta( get_queried_object_id(), '_gebhart_seo_title', true );
		return '' !== $custom ? $custom : $title;
	}
);

/**
 * Meta-Beschreibung der aktuellen Ansicht (auch für die Schema-Daten genutzt).
 */
function gebhart_current_description() {
	$desc = '';
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		$desc = get_post_meta( (int) get_option( 'page_for_posts' ), '_gebhart_seo_desc', true );
	} elseif ( is_post_type_archive( 'referenz' ) ) {
		$desc = 'Referenzen von Philipp Gebhart: WordPress-Websites, Relaunches, Betreuung und SEO für Unternehmen in Friedberg, Augsburg und Umgebung – mit Ausgangslage, Vorgehen und Ergebnis.';
	} elseif ( is_singular( 'referenz' ) ) {
		$desc = has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '';
	} elseif ( is_singular() ) {
		$desc = get_post_meta( get_queried_object_id(), '_gebhart_seo_desc', true );
		if ( '' === $desc && is_singular( 'post' ) && has_excerpt() ) {
			$desc = wp_strip_all_tags( get_the_excerpt() );
		}
	}
	return (string) $desc;
}

add_action(
	'wp_head',
	function () {
		if ( gebhart_seo_plugin_active() ) {
			return;
		}
		$desc = gebhart_current_description();
		if ( '' !== $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
	},
	1
);


/* ---------- Strukturierte Daten (Schema.org, JSON-LD) ----------
 * Ein zusammenhängender @graph: Unternehmen (ProfessionalService), Person, WebSite,
 * die aktuelle Seite mit Breadcrumb sowie je nach Ansicht Service oder BlogPosting.
 * FAQ-Daten stehen direkt im Inhalt der Seiten, auf denen die Fragen sichtbar sind.
 * Ist ein SEO-Plugin aktiv, entfällt diese Ausgabe.
 */
/**
 * Text für JSON-LD: ohne HTML, HTML-Entities (z. B. &#038;) in echte Zeichen umgewandelt.
 */
function gebhart_schema_text( $text ) {
	return trim( html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

function gebhart_schema_graph() {
	$home     = trailingslashit( home_url() );
	$biz_id   = $home . '#business';
	$person   = $home . '#person';
	$site_id  = $home . '#website';
	$portrait = $home . 'wp-content/uploads/2026/10/philipp-gebhart-portrait-01.webp';

	$services = array(
		'wordpress-website'            => 'WordPress-Website',
		'website-relaunch'             => 'Website-Relaunch',
		'wordpress-wartung'            => 'WordPress-Wartung & Betreuung',
		'suchmaschinenoptimierung-seo' => 'Suchmaschinenoptimierung (SEO)',
	);
	$offers = array();
	foreach ( $services as $slug => $name ) {
		$page = get_page_by_path( $slug );
		if ( $page ) {
			$offers[] = array(
				'@type'       => 'Offer',
				'itemOffered' => array(
					'@type' => 'Service',
					'@id'   => get_permalink( $page ) . '#service',
					'name'  => $name,
					'url'   => get_permalink( $page ),
				),
			);
		}
	}

	$graph = array();

	$graph[] = array(
		'@type'                     => 'ProfessionalService',
		'@id'                       => $biz_id,
		'name'                      => 'Philipp Gebhart – Webdesign & WordPress',
		'alternateName'             => 'philippgebhart.de',
		'description'               => 'WordPress-Websites, Relaunch, Wartung und SEO für Unternehmen, Selbstständige und Praxen – persönlich betreut aus Friedberg bei Augsburg.',
		'url'                       => $home,
		'image'                     => $portrait,
		'logo'                      => get_site_icon_url( 512 ) ? get_site_icon_url( 512 ) : $portrait,
		'telephone'                 => '+49 177 8362302',
		'email'                     => 'info@philippgebhart.de',
		'vatID'                     => 'DE323183104',
		'address'                   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => 'Christophberg 1',
			'postalCode'      => '86316',
			'addressLocality' => 'Friedberg',
			'addressRegion'   => 'Bayern',
			'addressCountry'  => 'DE',
		),
		'geo'                       => array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => 48.341769,
			'longitude' => 11.001211,
		),
		'hasMap'                    => 'https://maps.google.com/?cid=2312351749588598698',
		'areaServed'                => array(
			array( '@type' => 'City', 'name' => 'Friedberg' ),
			array( '@type' => 'City', 'name' => 'Augsburg' ),
			array( '@type' => 'City', 'name' => 'Aichach' ),
			array( '@type' => 'AdministrativeArea', 'name' => 'Landkreis Aichach-Friedberg' ),
		),
		'openingHoursSpecification' => array(
			array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ),
				'opens'     => '09:00',
				'closes'    => '18:00',
			),
		),
		'contactPoint'              => array(
			'@type'             => 'ContactPoint',
			'contactType'       => 'customer service',
			'telephone'         => '+49 177 8362302',
			'email'             => 'info@philippgebhart.de',
			'url'               => $home . 'projektanfrage/',
			'availableLanguage' => 'de',
			'areaServed'        => 'DE',
		),
		'founder'                   => array( '@id' => $person ),
		'sameAs'                    => array(
			'https://maps.google.com/?cid=2312351749588598698',
			'https://www.google.com/search?kgmid=/g/11c1rtc7n9',
		),
		'hasOfferCatalog'           => array(
			'@type'           => 'OfferCatalog',
			'name'            => 'Leistungen',
			'itemListElement' => $offers,
		),
	);

	$graph[] = array(
		'@type'       => 'Person',
		'@id'         => $person,
		'name'        => 'Philipp Gebhart',
		'jobTitle'    => 'Webdesigner',
		'description' => 'Webdesigner aus Friedberg bei Augsburg mit Hintergrund im Online-Marketing. Seit 2011 eigene Webagentur, seit über zehn Jahren Dozent an der VHS Augsburg.',
		'image'       => $portrait,
		'url'         => $home . 'ueber-mich/',
		'worksFor'    => array( '@id' => $biz_id ),
		'knowsAbout'  => array( 'WordPress', 'Webdesign', 'Website-Relaunch', 'Suchmaschinenoptimierung', 'Online-Marketing', 'Google Ads' ),
		'sameAs'      => array( 'https://www.linkedin.com/in/philipp-gebhart-169130aa/' ),
		'affiliation' => array(
			'@type' => 'EducationalOrganization',
			'name'  => 'Volkshochschule Augsburg',
		),
		'hasOccupation' => array(
			array( '@type' => 'Occupation', 'name' => 'Webdesigner' ),
			array( '@type' => 'Occupation', 'name' => 'Dozent für WordPress und Website-Erstellung' ),
		),
		'hasCredential' => array(
			'@type'              => 'EducationalOccupationalCredential',
			'name'               => 'Google-Analytics-Zertifizierung',
			'credentialCategory' => 'certificate',
			'recognizedBy'       => array( '@type' => 'Organization', 'name' => 'Google' ),
		),
	);

	$graph[] = array(
		'@type'      => 'WebSite',
		'@id'        => $site_id,
		'url'        => $home,
		'name'       => 'Philipp Gebhart',
		'inLanguage' => 'de-DE',
		'publisher'  => array( '@id' => $biz_id ),
	);

	// Aktuelle Seite und Breadcrumb.
	$url   = '';
	$name  = gebhart_schema_text( wp_get_document_title() );
	$type  = 'WebPage';
	$crumb = array( array( 'Startseite', $home ) );

	if ( is_front_page() ) {
		$url = $home;
	} elseif ( is_home() ) {
		$url   = get_permalink( (int) get_option( 'page_for_posts' ) );
		$type  = 'CollectionPage';
		$crumb[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), $url );
	} elseif ( is_post_type_archive( 'referenz' ) ) {
		$url   = get_post_type_archive_link( 'referenz' );
		$type  = 'CollectionPage';
		$crumb[] = array( 'Referenzen', $url );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term  = get_queried_object();
		$url   = get_term_link( $term );
		$type  = 'CollectionPage';
		if ( ! is_tax() && get_option( 'page_for_posts' ) ) {
			$crumb[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), get_permalink( (int) get_option( 'page_for_posts' ) ) );
		} elseif ( is_tax( 'leistung' ) ) {
			$crumb[] = array( 'Referenzen', get_post_type_archive_link( 'referenz' ) );
		}
		$crumb[] = array( $term->name, $url );
	} elseif ( is_singular() ) {
		$post_id = get_queried_object_id();
		$url     = get_permalink( $post_id );
		if ( is_singular( 'post' ) && get_option( 'page_for_posts' ) ) {
			$crumb[] = array( get_the_title( (int) get_option( 'page_for_posts' ) ), get_permalink( (int) get_option( 'page_for_posts' ) ) );
		} elseif ( is_singular( 'referenz' ) ) {
			$crumb[] = array( 'Referenzen', get_post_type_archive_link( 'referenz' ) );
		} else {
			foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $anc ) {
				$crumb[] = array( get_the_title( $anc ), get_permalink( $anc ) );
			}
		}
		$crumb[] = array( get_the_title( $post_id ), $url );
		$slug    = get_post_field( 'post_name', $post_id );
		if ( 'ueber-mich' === $slug ) {
			$type = 'AboutPage';
		} elseif ( 'projektanfrage' === $slug ) {
			$type = 'ContactPage';
		}
	}

	if ( ! $url || is_wp_error( $url ) || is_404() || is_search() ) {
		return $graph;
	}

	$page = array(
		'@type'      => $type,
		'@id'        => $url . '#webpage',
		'url'        => $url,
		'name'       => $name,
		'inLanguage' => 'de-DE',
		'isPartOf'   => array( '@id' => $site_id ),
	);
	$desc = gebhart_current_description();
	if ( '' !== $desc ) {
		$page['description'] = gebhart_schema_text( $desc );
	}
	if ( is_front_page() ) {
		$page['about'] = array( '@id' => $biz_id );
	} elseif ( 'AboutPage' === $type ) {
		$page['mainEntity'] = array( '@id' => $person );
	}
	if ( is_singular() && has_post_thumbnail() ) {
		$page['primaryImageOfPage'] = get_the_post_thumbnail_url( get_queried_object_id(), 'full' );
	}
	if ( count( $crumb ) > 1 ) {
		$items = array();
		foreach ( $crumb as $i => $c ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => gebhart_schema_text( $c[0] ),
				'item'     => $c[1],
			);
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'@id'             => $url . '#breadcrumb',
			'itemListElement' => $items,
		);
		$page['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	}
	$graph[] = $page;

	// Leistungsseite: Service.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $services[ $slug ] ) ) {
			$service = array(
				'@type'      => 'Service',
				'@id'        => $url . '#service',
				'name'       => $services[ $slug ],
				'url'        => $url,
				'provider'   => array( '@id' => $biz_id ),
				'areaServed' => array( 'Friedberg', 'Augsburg', 'Aichach', 'Bayern' ),
			);
			if ( '' !== $desc ) {
				$service['description'] = gebhart_schema_text( $desc );
			}
			if ( 'wordpress-wartung' === $slug ) {
				$service['offers'] = gebhart_schema_care_offers( $url );
			}
			$graph[] = $service;
		}
	}

	// Wissensbeitrag: BlogPosting.
	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$posting = array(
			'@type'            => 'BlogPosting',
			'@id'              => $url . '#article',
			'headline'         => gebhart_schema_text( get_the_title( $post_id ) ),
			'datePublished'    => get_the_date( 'c', $post_id ),
			'dateModified'     => get_the_modified_date( 'c', $post_id ),
			'author'           => array( '@id' => $person ),
			'publisher'        => array( '@id' => $biz_id ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'inLanguage'       => 'de-DE',
		);
		if ( '' !== $desc ) {
			$posting['description'] = $desc;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			$posting['image'] = get_the_post_thumbnail_url( $post_id, 'gebhart-wide' );
		}
		$cats = get_the_category( $post_id );
		if ( ! empty( $cats ) ) {
			$posting['articleSection'] = $cats[0]->name;
			$posting['about']          = array_map(
				function ( $c ) {
					return array( '@type' => 'Thing', 'name' => $c->name );
				},
				$cats
			);
		}
		$words                  = count( preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ), -1, PREG_SPLIT_NO_EMPTY ) );
		$posting['wordCount']   = $words;
		$posting['timeRequired'] = 'PT' . gebhart_reading_time( $post_id ) . 'M';
		$tags = get_the_tags( $post_id );
		if ( $tags && ! is_wp_error( $tags ) ) {
			$posting['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
		}
		if ( get_option( 'page_for_posts' ) ) {
			$posting['isPartOf'] = array( '@id' => get_permalink( (int) get_option( 'page_for_posts' ) ) . '#blog' );
		}
		$graph[] = $posting;
	}

	// Referenz: Arbeitsbeispiel (CreativeWork) mit Kunde und Leistungen.
	if ( is_singular( 'referenz' ) ) {
		$post_id = get_queried_object_id();
		$work    = array(
			'@type'            => 'CreativeWork',
			'@id'              => $url . '#work',
			'name'             => gebhart_schema_text( get_the_title( $post_id ) ),
			'url'              => $url,
			'creator'          => array( '@id' => $person ),
			'publisher'        => array( '@id' => $biz_id ),
			'mainEntityOfPage' => array( '@id' => $url . '#webpage' ),
			'inLanguage'       => 'de-DE',
		);
		if ( '' !== $desc ) {
			$work['description'] = gebhart_schema_text( $desc );
		}
		$jahr = gebhart_ref( 'ref_jahr', $post_id );
		if ( preg_match( '/\b(19|20)\d{2}\b/', $jahr, $m ) ) {
			$work['dateCreated'] = $m[0];
		}
		$leistungen = gebhart_ref( 'ref_leistungen', $post_id );
		if ( '' !== $leistungen ) {
			$work['keywords'] = str_replace( array( ' · ', '·' ), ', ', gebhart_schema_text( $leistungen ) );
		}
		$kunde = gebhart_ref( 'ref_kunde', $post_id );
		if ( '' !== $kunde ) {
			$client = array(
				'@type' => 'Organization',
				'name'  => gebhart_schema_text( $kunde ),
			);
			$site = gebhart_ref( 'ref_url', $post_id );
			if ( '' !== $site ) {
				$client['url'] = esc_url_raw( preg_match( '#^https?://#', $site ) ? $site : 'https://' . $site );
			}
			$work['about'] = $client;
		}
		if ( has_post_thumbnail( $post_id ) ) {
			$work['image'] = get_the_post_thumbnail_url( $post_id, 'large' );
		}
		$graph[] = $work;
		foreach ( $graph as $i => $node ) {
			if ( isset( $node['@id'] ) && $url . '#webpage' === $node['@id'] ) {
				$graph[ $i ]['mainEntity'] = array( '@id' => $url . '#work' );
			}
		}
	}

	// Referenzen-Übersicht: Liste der Arbeitsbeispiele in der angezeigten Reihenfolge.
	if ( is_post_type_archive( 'referenz' ) ) {
		$ids   = get_posts(
			array(
				'post_type'      => 'referenz',
				'posts_per_page' => 50,
				'fields'         => 'ids',
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
				'no_found_rows'  => true,
			)
		);
		$items = array();
		foreach ( $ids as $i => $rid ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $rid ),
				'name'     => gebhart_schema_text( get_the_title( $rid ) ),
			);
		}
		if ( $items ) {
			$graph[] = array(
				'@type'           => 'ItemList',
				'@id'             => $url . '#list',
				'name'            => 'Referenzen',
				'itemListElement' => $items,
			);
			foreach ( $graph as $i => $node ) {
				if ( isset( $node['@id'] ) && $url . '#webpage' === $node['@id'] ) {
					$graph[ $i ]['mainEntity'] = array( '@id' => $url . '#list' );
				}
			}
		}
	}

	// Wissen-Übersicht: Blog mit den neuesten Beiträgen.
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		$latest = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'no_found_rows'  => true,
			)
		);
		$blog   = array(
			'@type'      => 'Blog',
			'@id'        => $url . '#blog',
			'name'       => 'Wissen – Philipp Gebhart',
			'url'        => $url,
			'inLanguage' => 'de-DE',
			'publisher'  => array( '@id' => $biz_id ),
			'author'     => array( '@id' => $person ),
			'blogPost'   => array(),
		);
		if ( '' !== $desc ) {
			$blog['description'] = gebhart_schema_text( $desc );
		}
		foreach ( $latest as $lp ) {
			$blog['blogPost'][] = array(
				'@type'         => 'BlogPosting',
				'@id'           => get_permalink( $lp ) . '#article',
				'headline'      => gebhart_schema_text( get_the_title( $lp ) ),
				'url'           => get_permalink( $lp ),
				'datePublished' => get_the_date( 'c', $lp ),
			);
		}
		$graph[] = $blog;
		foreach ( $graph as $i => $node ) {
			if ( isset( $node['@id'] ) && $url . '#webpage' === $node['@id'] ) {
				$graph[ $i ]['mainEntity'] = array( '@id' => $url . '#blog' );
			}
		}
	}

	return $graph;
}

/**
 * Angebote der Wartungsseite (Preise wie auf der Seite sichtbar, netto).
 */
function gebhart_schema_care_offers( $url ) {
	$monthly = function ( $name, $price, $desc ) use ( $url ) {
		return array(
			'@type'              => 'Offer',
			'name'               => $name,
			'description'        => $desc,
			'url'                => $url,
			'price'              => $price,
			'priceCurrency'      => 'EUR',
			'priceSpecification' => array(
				'@type'                 => 'UnitPriceSpecification',
				'price'                 => $price,
				'priceCurrency'         => 'EUR',
				'valueAddedTaxIncluded' => false,
				'unitCode'              => 'MON',
				'unitText'              => 'Monat',
			),
		);
	};
	$offers   = array(
		$monthly( 'Betreuung', 39, 'Technische Pflege: Updates, Sicherungen, Funktionsprüfung und fester Ansprechpartner.' ),
		$monthly( 'Betreuung Plus', 79, 'Technische Pflege plus bis zu 30 Minuten Änderungszeit pro Monat für kleine Anpassungen.' ),
		$monthly( 'Hosting inklusive E-Mail (Zusatzoption)', 15, 'Website-Hosting, SSL und E-Mail-Postfächer, zu beiden Paketen ergänzbar.' ),
	);
	$offers[] = array(
		'@type'              => 'Offer',
		'name'               => 'Zusatzarbeiten',
		'description'        => 'Arbeiten über die Wartung hinaus nach vorheriger Abstimmung.',
		'url'                => $url,
		'price'              => 80,
		'priceCurrency'      => 'EUR',
		'priceSpecification' => array(
			'@type'                 => 'UnitPriceSpecification',
			'price'                 => 80,
			'priceCurrency'         => 'EUR',
			'valueAddedTaxIncluded' => false,
			'unitCode'              => 'HUR',
			'unitText'              => 'Stunde',
		),
	);
	return $offers;
}

add_action(
	'wp_head',
	function () {
		if ( gebhart_seo_plugin_active() ) {
			return;
		}
		$data = array(
			'@context' => 'https://schema.org',
			'@graph'   => gebhart_schema_graph(),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
	},
	5
);

/* ---------- Vorschau beim Teilen (Open Graph, Twitter Card) ----------
 * Titel und Beschreibung aus den SEO-Feldern. Bild: Beitragsbild bei Beiträgen und Referenzen,
 * sonst das Standardbild (Startseiten-Hero mit Claim, 1200 × 630).
 * Ist ein SEO-Plugin aktiv, entfällt diese Ausgabe.
 */
define( 'GEBHART_OG_DEFAULT_IMAGE', 153 );

function gebhart_current_url() {
	if ( is_front_page() ) {
		return trailingslashit( home_url() );
	}
	if ( is_home() && get_option( 'page_for_posts' ) ) {
		return get_permalink( (int) get_option( 'page_for_posts' ) );
	}
	if ( is_post_type_archive( 'referenz' ) ) {
		return get_post_type_archive_link( 'referenz' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		return is_wp_error( $link ) ? '' : $link;
	}
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	return '';
}

add_action(
	'wp_head',
	function () {
		if ( gebhart_seo_plugin_active() || is_404() || is_search() ) {
			return;
		}
		$url = gebhart_current_url();
		if ( ! $url ) {
			return;
		}
		$title = gebhart_schema_text( wp_get_document_title() );
		$desc  = gebhart_current_description();
		if ( '' === $desc && get_option( 'page_on_front' ) ) {
			$desc = get_post_meta( (int) get_option( 'page_on_front' ), '_gebhart_seo_desc', true );
		}
		$desc = gebhart_schema_text( $desc );

		$img_id = GEBHART_OG_DEFAULT_IMAGE;
		if ( ( is_singular( 'post' ) || is_singular( 'referenz' ) ) && has_post_thumbnail() ) {
			$img_id = get_post_thumbnail_id( get_queried_object_id() );
		}
		$img = wp_get_attachment_image_src( $img_id, $img_id === GEBHART_OG_DEFAULT_IMAGE ? 'full' : 'gebhart-wide' );
		$alt = $img ? get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

		$tags = array(
			'og:locale'      => 'de_DE',
			'og:site_name'   => 'Philipp Gebhart',
			'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
			'og:title'       => $title,
			'og:description' => $desc,
			'og:url'         => $url,
		);
		if ( $img ) {
			$tags['og:image']        = $img[0];
			$tags['og:image:width']  = $img[1];
			$tags['og:image:height'] = $img[2];
			if ( $alt ) {
				$tags['og:image:alt'] = $alt;
			}
		}
		if ( is_singular( 'post' ) ) {
			$tags['article:published_time'] = get_the_date( 'c' );
			$tags['article:modified_time']  = get_the_modified_date( 'c' );
		}
		foreach ( $tags as $prop => $val ) {
			if ( '' !== (string) $val ) {
				echo '<meta property="' . esc_attr( $prop ) . '" content="' . esc_attr( $val ) . '">' . "\n";
			}
		}
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	},
	4
);

/* ---------- Canonical für Übersichten ----------
 * Einzelseiten bekommen den Canonical von WordPress selbst. Für Wissen, Referenzen und
 * Themenseiten setzt das Theme ihn; Folgeseiten (page/2/ …) verweisen auf sich selbst.
 */
add_action(
	'wp_head',
	function () {
		if ( gebhart_seo_plugin_active() || is_singular() || is_404() || is_search() ) {
			return;
		}
		$url = gebhart_current_url();
		if ( ! $url ) {
			return;
		}
		$paged = (int) get_query_var( 'paged' );
		if ( $paged > 1 ) {
			$url = trailingslashit( $url ) . user_trailingslashit( 'page/' . $paged, 'paged' );
		}
		echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
	},
	3
);


/* ---------- Logo (Bildmarke „pG“ als Vektorgrafik) ----------
 * Aus philipp-gebhart-logo-schwarz.png vektorisiert. Farbe folgt currentColor.
 */
function gebhart_logo_mark( $class = 'site-logo__mark' ) {
	return '<svg class="' . esc_attr( $class ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="39.0 19.0 118.9 159.9" aria-hidden="true" focusable="false"><path fill="currentColor" fill-rule="evenodd" d="M64.20,177.82C65.07,177.45 65.00,182.03 65.00,126.91C65.00,77.06 65.00,77.06 65.50,76.00C65.99,74.96 66.00,74.91 66.00,73.50C66.00,72.09 66.01,72.04 66.50,71.00C66.78,70.41 67.00,69.74 67.00,69.50C67.00,69.26 67.22,68.59 67.50,68.00C67.79,67.38 68.00,66.75 68.00,66.49C68.00,65.85 69.24,63.38 69.99,62.51C70.32,62.13 70.78,61.45 71.00,61.00C71.22,60.55 71.67,59.88 72.00,59.50C72.33,59.12 72.80,58.40 73.05,57.90C73.71,56.59 77.61,52.70 78.93,52.04C79.50,51.75 80.23,51.21 81.00,50.50C82.15,49.44 82.29,49.35 85.50,47.75C88.81,46.09 88.81,46.09 90.00,46.00C91.01,45.92 91.32,45.84 92.00,45.48C92.81,45.06 92.81,45.06 100.50,45.06C108.19,45.06 108.19,45.06 108.95,45.46C109.36,45.68 110.06,45.92 110.50,46.00C110.94,46.08 111.61,46.30 112.00,46.50C112.39,46.70 113.06,46.92 113.50,47.00C114.52,47.19 116.60,48.22 117.50,49.00C117.88,49.33 118.55,49.78 119.00,50.00C119.50,50.25 120.28,50.83 121.00,51.50C121.76,52.20 122.50,52.75 123.06,53.03C124.09,53.55 125.48,54.93 126.04,55.98C126.62,57.08 125.42,57.00 141.00,57.00C156.11,57.00 155.42,57.03 155.75,56.25C155.91,55.85 155.88,55.77 153.26,50.52C151.24,46.49 150.45,45.02 150.00,44.50C149.68,44.12 149.21,43.42 148.97,42.94C148.63,42.27 148.16,41.69 147.00,40.50C145.78,39.25 145.38,38.74 144.99,37.97C144.29,36.59 141.40,33.71 140.00,32.99C139.27,32.62 138.71,32.18 137.50,31.00C136.33,29.87 135.72,29.37 135.06,29.04C134.58,28.79 133.88,28.33 133.50,28.00C132.51,27.15 124.62,23.20 123.50,23.00C123.06,22.92 122.39,22.70 122.00,22.50C121.61,22.30 120.94,22.08 120.50,22.00C120.06,21.92 119.39,21.70 119.00,21.50C118.61,21.30 117.94,21.08 117.50,21.00C117.06,20.92 116.36,20.68 115.95,20.46C115.19,20.06 115.19,20.06 101.50,20.06C87.81,20.06 87.81,20.06 87.00,20.48C86.32,20.84 86.01,20.92 85.00,21.00C83.85,21.09 83.75,21.13 82.05,21.98C80.89,22.56 80.02,22.91 79.50,23.00C78.88,23.11 77.38,23.81 72.39,26.31C66.06,29.48 66.06,29.48 64.50,31.00C63.28,32.18 62.73,32.62 61.99,33.00C60.53,33.75 54.74,39.52 54.02,40.97C53.62,41.75 53.22,42.25 52.00,43.50C50.84,44.69 50.37,45.27 50.03,45.94C49.79,46.42 49.33,47.12 49.00,47.50C48.65,47.91 48.04,48.92 47.50,50.00C46.94,51.11 46.36,52.09 45.98,52.52C45.29,53.32 45.00,53.92 45.00,54.54C45.00,54.84 44.53,55.91 43.50,58.00C42.42,60.18 42.00,61.15 42.00,61.48C42.00,61.75 41.79,62.38 41.50,63.00C41.06,63.94 41.00,64.17 41.00,65.00C41.00,65.83 40.94,66.06 40.50,67.00C40.00,68.06 40.00,68.06 40.00,122.41C40.00,182.32 39.93,177.44 40.78,177.81C41.40,178.08 63.55,178.09 64.20,177.82ZM107.38,142.01C109.10,141.92 109.23,141.90 110.00,141.50C110.77,141.11 110.91,141.08 112.50,141.00C114.06,140.93 114.25,140.89 114.95,140.53C115.36,140.32 116.06,140.08 116.50,140.00C116.94,139.92 117.61,139.70 118.00,139.50C118.39,139.30 119.06,139.08 119.50,139.00C120.58,138.80 124.55,136.82 125.50,136.00C125.97,135.60 127.08,134.96 129.00,134.00C130.91,133.04 132.03,132.40 132.50,132.00C132.88,131.67 133.58,131.21 134.06,130.97C134.79,130.60 135.36,130.11 137.50,128.00C139.39,126.13 140.25,125.38 140.77,125.12C141.49,124.75 141.77,124.45 142.22,123.56C142.36,123.29 143.16,122.36 144.00,121.50C145.18,120.29 145.63,119.73 146.01,119.00C146.33,118.38 146.83,117.71 147.50,117.00C148.15,116.32 148.67,115.62 148.96,115.06C149.21,114.58 149.68,113.88 150.00,113.50C150.82,112.55 154.00,106.20 154.00,105.51C154.00,105.18 154.29,104.46 155.00,103.00C155.68,101.61 156.00,100.82 156.00,100.51C156.00,100.25 156.21,99.61 156.50,99.00C157.00,97.94 157.00,97.94 157.00,87.59C157.00,76.08 157.03,76.53 156.19,76.18C155.48,75.88 112.98,75.97 112.44,76.27C111.60,76.73 109.51,78.98 109.00,79.98C108.67,80.63 108.19,81.28 107.50,82.00C106.84,82.69 106.32,83.39 106.00,84.00C105.68,84.61 105.16,85.31 104.50,86.00C103.85,86.68 103.33,87.38 103.04,87.94C102.79,88.42 102.33,89.12 102.00,89.50C101.67,89.88 101.21,90.58 100.97,91.06C100.63,91.73 100.17,92.30 99.00,93.50C97.06,95.48 96.74,96.13 97.18,97.18C97.51,97.98 96.54,97.93 112.89,98.00C127.38,98.06 127.86,98.07 128.11,98.30C128.26,98.43 128.38,98.67 128.38,98.83C128.38,99.34 127.66,100.74 127.02,101.48C126.68,101.87 126.20,102.60 125.95,103.10C125.54,103.92 125.12,104.38 121.73,107.76C117.97,111.52 117.97,111.52 113.58,113.72C109.19,115.92 109.19,115.92 107.00,116.00C104.81,116.07 104.81,116.07 104.00,116.51C103.19,116.94 103.19,116.94 86.56,117.00C68.38,117.07 69.51,117.01 69.18,117.82C68.91,118.45 68.91,140.55 69.18,141.18C69.51,141.99 68.37,141.93 86.69,142.00C95.90,142.04 103.92,142.07 104.50,142.09C105.08,142.10 106.38,142.06 107.38,142.01Z"/></svg>';
}
