<?php
/**
 * Ladezeit: Hero-Bild der Startseite früh laden, auf Mobilgeräten die kleinere Bildgröße verwenden.
 * Das Hero-Bild steht als Hintergrundbild im Seiteninhalt und wird sonst erst spät entdeckt.
 */

/**
 * Liefert URLs des Hero-Bilds der Startseite (volle Größe und 1024er-Variante) oder null.
 */
function gebhart_hero_image() {
	if ( ! is_front_page() ) {
		return null;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( ! preg_match( '#background-image:\s*url\(\s*[\'"&;apos]*([^\'")\s&]+?\.(?:jpe?g|png|webp))#i', $content, $m ) ) {
		return null;
	}
	$full = esc_url_raw( $m[1] );
	if ( 0 === strpos( $full, '/' ) ) {
		$full = home_url( $full );
	}
	$id   = attachment_url_to_postid( $full );
	$mid  = $id ? wp_get_attachment_image_src( $id, 'large' ) : null;
	return array(
		'full'  => $full,
		'small' => ( $mid && ! empty( $mid[0] ) ) ? $mid[0] : $full,
	);
}

add_action(
	'wp_head',
	static function () {
		$img = gebhart_hero_image();
		if ( ! $img ) {
			return;
		}
		if ( $img['small'] !== $img['full'] ) {
			printf( "<link rel=\"preload\" as=\"image\" href=\"%s\" media=\"(max-width: 899px)\" fetchpriority=\"high\">\n", esc_url( $img['small'] ) );
			printf( "<link rel=\"preload\" as=\"image\" href=\"%s\" media=\"(min-width: 900px)\" fetchpriority=\"high\">\n", esc_url( $img['full'] ) );
			printf( "<style>@media (max-width: 899px){.hero[style*=\"background-image\"]{background-image:url(%s)!important}}</style>\n", esc_url( $img['small'] ) );
		} else {
			printf( "<link rel=\"preload\" as=\"image\" href=\"%s\" fetchpriority=\"high\">\n", esc_url( $img['full'] ) );
		}
	},
	1
);
