<?php
/**
 * 301-Weiterleitungen alter Adressen (Stand vor dem Relaunch 2026).
 * Pfade ohne Schrägstrich am Ende, kleingeschrieben. Ziel immer relativ zur Startseite.
 * /impressum/ und /datenschutzerklaerung/ behalten ihre Adressen und stehen hier bewusst nicht.
 */

function gebhart_redirect_map() {
	return array(
		'/kontakt'                    => '/projektanfrage/',
		'/suchmaschinenwerbung-sea'   => '/suchmaschinenoptimierung-seo/',
		'/wordpress-coaching'         => '/ueber-mich/',
		'/wordpress-seminare'         => '/ueber-mich/',
	);
}

add_action(
	'template_redirect',
	static function () {
		if ( is_admin() || ! in_array( $_SERVER['REQUEST_METHOD'] ?? 'GET', array( 'GET', 'HEAD' ), true ) ) {
			return;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path = strtolower( untrailingslashit( (string) wp_parse_url( $uri, PHP_URL_PATH ) ) );

		// Unterverzeichnis-Installationen: Basispfad der Website abziehen.
		$base = strtolower( untrailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) ) );
		if ( '' !== $base && 0 === strpos( $path, $base ) ) {
			$path = substr( $path, strlen( $base ) );
		}

		$target = null;
		$map    = gebhart_redirect_map();
		if ( isset( $map[ $path ] ) ) {
			$target = $map[ $path ];
		} elseif ( '/author/pgebhart' === $path || 0 === strpos( $path, '/author/pgebhart/' ) ) {
			$target = '/ueber-mich/';
		}

		if ( $target ) {
			wp_redirect( home_url( $target ), 301 );
			exit;
		}
	},
	0
);
