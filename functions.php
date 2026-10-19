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
	$atts  = shortcode_atts( array( 'limit' => 2 ), $atts, 'gebhart_referenzen' );
	$query = new WP_Query(
		array(
			'post_type'      => 'referenz',
			'posts_per_page' => max( 1, (int) $atts['limit'] ),
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'no_found_rows'  => true,
		)
	);
	if ( ! $query->have_posts() ) {
		return '';
	}
	ob_start();
	echo '<div class="ref-list">';
	while ( $query->have_posts() ) {
		$query->the_post();
		get_template_part( 'template-parts/referenz-row' );
	}
	echo '</div>';
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
				<span>Ich habe die <a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/datenschutz/' ) ); ?>">Datenschutzerklärung</a> gelesen und bin mit der Verarbeitung meiner Angaben zur Bearbeitung der Anfrage einverstanden. *</span>
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
	$done( $sent ? 'ok' : 'fehler' );
}
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

add_action(
	'wp_head',
	function () {
		if ( gebhart_seo_plugin_active() || ! is_singular() ) {
			return;
		}
		$desc = get_post_meta( get_queried_object_id(), '_gebhart_seo_desc', true );
		if ( '' !== $desc ) {
			echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
		}
	},
	1
);
