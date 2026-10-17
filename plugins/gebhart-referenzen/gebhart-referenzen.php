<?php
/**
 * Plugin Name: Gebhart Referenzen
 * Description: Inhaltstyp "Referenz" mit Taxonomie "Leistung" und festen Feldern (Kunde, Branche, Jahr, Leistungen, URL, Ausgangslage, Vorgehen, Ergebnis). Bleibt beim Theme-Wechsel erhalten.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Philipp Gebhart
 * Text Domain: gebhart-referenzen
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Felder der Referenz. Schlüssel => Konfiguration.
 * type: text | textarea | url
 */
function gebhart_ref_fields() {
	return array(
		'ref_untertitel'  => array(
			'label' => 'Untertitel',
			'type'  => 'text',
			'help'  => 'Kurze Zeile über dem Titel, zum Beispiel "Website und langfristige Zusammenarbeit".',
		),
		'ref_kunde'       => array(
			'label' => 'Kunde',
			'type'  => 'text',
			'help'  => '',
		),
		'ref_branche'     => array(
			'label' => 'Branche',
			'type'  => 'text',
			'help'  => '',
		),
		'ref_jahr'        => array(
			'label' => 'Jahr',
			'type'  => 'text',
			'help'  => '',
		),
		'ref_leistungen'  => array(
			'label' => 'Leistungen (Zeile)',
			'type'  => 'text',
			'help'  => 'Mit Mittelpunkt getrennt, zum Beispiel "Gestaltung · WordPress & Elementor · SEO".',
		),
		'ref_url'         => array(
			'label' => 'Projekt-URL',
			'type'  => 'url',
			'help'  => 'Adresse der Website des Kunden.',
		),
		'ref_ausgangslage' => array(
			'label' => 'Ausgangslage',
			'type'  => 'textarea',
			'help'  => 'Womit das Unternehmen zu dir kam.',
		),
		'ref_vorgehen'    => array(
			'label' => 'Vorgehen',
			'type'  => 'textarea',
			'help'  => 'Ablauf und dein konkreter Beitrag.',
		),
		'ref_ergebnis'    => array(
			'label' => 'Ergebnis',
			'type'  => 'textarea',
			'help'  => 'Nur Angaben, die belegt sind.',
		),
	);
}

/**
 * Inhaltstyp und Taxonomie registrieren.
 */
function gebhart_ref_register() {
	register_post_type(
		'referenz',
		array(
			'labels'        => array(
				'name'               => 'Referenzen',
				'singular_name'      => 'Referenz',
				'add_new'            => 'Neue Referenz',
				'add_new_item'       => 'Neue Referenz hinzufügen',
				'edit_item'          => 'Referenz bearbeiten',
				'new_item'           => 'Neue Referenz',
				'view_item'          => 'Referenz ansehen',
				'search_items'       => 'Referenzen durchsuchen',
				'not_found'          => 'Keine Referenzen gefunden',
				'not_found_in_trash' => 'Keine Referenzen im Papierkorb',
				'all_items'          => 'Alle Referenzen',
				'menu_name'          => 'Referenzen',
			),
			'public'        => true,
			'has_archive'   => 'referenzen',
			'rewrite'       => array(
				'slug'       => 'referenzen',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 20,
			'show_in_rest'  => true,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'custom-fields' ),
		)
	);

	register_taxonomy(
		'leistung',
		array( 'referenz' ),
		array(
			'labels'            => array(
				'name'          => 'Leistungen',
				'singular_name' => 'Leistung',
				'add_new_item'  => 'Neue Leistung hinzufügen',
				'edit_item'     => 'Leistung bearbeiten',
				'all_items'     => 'Alle Leistungen',
				'menu_name'     => 'Leistungen',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'       => 'leistung',
				'with_front' => false,
			),
		)
	);

	foreach ( gebhart_ref_fields() as $key => $field ) {
		register_post_meta(
			'referenz',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'url' === $field['type'] ? 'esc_url_raw' : ( 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}
}
add_action( 'init', 'gebhart_ref_register' );

/**
 * Meta-Box im Backend.
 */
function gebhart_ref_add_metabox() {
	add_meta_box(
		'gebhart_ref_felder',
		'Referenz-Angaben',
		'gebhart_ref_render_metabox',
		'referenz',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'gebhart_ref_add_metabox' );

function gebhart_ref_render_metabox( $post ) {
	wp_nonce_field( 'gebhart_ref_save', 'gebhart_ref_nonce' );
	echo '<style>.gref-row{margin:0 0 16px}.gref-row label{display:block;font-weight:600;margin:0 0 4px}.gref-row input[type=text],.gref-row input[type=url],.gref-row textarea{width:100%}.gref-help{color:#646970;font-size:12px;margin:4px 0 0}</style>';
	foreach ( gebhart_ref_fields() as $key => $field ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<div class="gref-row">';
		printf( '<label for="%1$s">%2$s</label>', esc_attr( $key ), esc_html( $field['label'] ) );
		if ( 'textarea' === $field['type'] ) {
			printf( '<textarea id="%1$s" name="%1$s" rows="5">%2$s</textarea>', esc_attr( $key ), esc_textarea( $value ) );
		} else {
			printf(
				'<input type="%1$s" id="%2$s" name="%2$s" value="%3$s">',
				'url' === $field['type'] ? 'url' : 'text',
				esc_attr( $key ),
				esc_attr( $value )
			);
		}
		if ( ! empty( $field['help'] ) ) {
			echo '<p class="gref-help">' . esc_html( $field['help'] ) . '</p>';
		}
		echo '</div>';
	}
	echo '<p class="gref-help">Der Editor darunter ist für zusätzlichen freien Text gedacht. Das Titelbild setzt du rechts bei "Beitragsbild".</p>';
}

function gebhart_ref_save( $post_id ) {
	if ( ! isset( $_POST['gebhart_ref_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gebhart_ref_nonce'] ) ), 'gebhart_ref_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( gebhart_ref_fields() as $key => $field ) {
		if ( ! isset( $_POST[ $key ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] );
		if ( 'url' === $field['type'] ) {
			$value = esc_url_raw( $raw );
		} elseif ( 'textarea' === $field['type'] ) {
			$value = sanitize_textarea_field( $raw );
		} else {
			$value = sanitize_text_field( $raw );
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_referenz', 'gebhart_ref_save' );

/**
 * Hilfsfunktion für Themes: Feldwert holen.
 */
function gebhart_ref_get( $key, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return (string) get_post_meta( $post_id, $key, true );
}

/**
 * Rewrite-Regeln beim Aktivieren und Deaktivieren aktualisieren.
 */
function gebhart_ref_activate() {
	gebhart_ref_register();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'gebhart_ref_activate' );

function gebhart_ref_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'gebhart_ref_deactivate' );
