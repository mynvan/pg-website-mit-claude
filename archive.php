<?php
/**
 * Archive (Kategorie, Schlagwort, Datum) in der Wissen-Darstellung.
 */
get_header();

get_template_part(
	'template-parts/wissen-list',
	null,
	array(
		// Bei Kategorie und Schlagwort nur den Namen zeigen (ohne „Kategorie:“ und ohne HTML aus dem Archivtitel).
		'title' => ( is_category() || is_tag() ) ? single_term_title( '', false ) : wp_strip_all_tags( get_the_archive_title() ),
		'intro' => get_the_archive_description(),
	)
);

get_footer();
