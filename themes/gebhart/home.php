<?php
/**
 * Beitragsseite (Wissen). Die Seite, die unter Einstellungen > Lesen als Beitragsseite gewählt ist,
 * liefert Titel und Einleitungstext aus dem Editor.
 */
get_header();

$page_id = (int) get_option( 'page_for_posts' );
$title   = $page_id ? get_the_title( $page_id ) : 'Wissen';
$intro   = '';
if ( $page_id ) {
	$intro = apply_filters( 'the_content', (string) get_post_field( 'post_content', $page_id ) );
}

get_template_part(
	'template-parts/wissen-list',
	null,
	array(
		'title' => $title,
		'intro' => $intro,
	)
);

get_footer();
