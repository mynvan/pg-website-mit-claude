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
