<?php
/**
 * Archive (Kategorie, Schlagwort, Datum) in der Wissen-Darstellung.
 */
get_header();

get_template_part(
	'template-parts/wissen-list',
	null,
	array(
		'title' => get_the_archive_title(),
		'intro' => get_the_archive_description(),
	)
);

get_footer();
