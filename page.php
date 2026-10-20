<?php
/**
 * Seiten: Startseite, Leistungen, Über mich, Kontakt und andere.
 * Auf der Startseite erscheint der Titel nicht, dort steht die Überschrift im Inhalt.
 */
get_header();

$gebhart_content = '';
while ( have_posts() ) :
	the_post();
	$gebhart_content = (string) get_post_field( 'post_content', get_the_ID() );
	// Hat die Seite im Inhalt eine eigene H1, entfällt der automatische Titel.
	$gebhart_has_h1 = false !== strpos( $gebhart_content, '<h1' );
	?>

	<?php if ( ! is_front_page() && ! $gebhart_has_h1 ) : ?>
		<section class="page-head">
			<div class="wrap">
				<h1><?php the_title(); ?></h1>
			</div>
		</section>
	<?php endif; ?>

	<div class="entry-content">
		<?php the_content(); ?>
	</div>

	<?php
endwhile;

// Die allgemeine Anfrage-Leiste entfällt, wenn die Seite eine eigene hat oder selbst das Formular zeigt.
$gebhart_skip_cta = is_front_page() || false !== strpos( $gebhart_content, 'band--dark' ) || false !== strpos( $gebhart_content, 'gebhart_kontaktformular' ) || false !== strpos( $gebhart_content, 'gebhart_danke' );
if ( ! $gebhart_skip_cta ) {
	get_template_part( 'template-parts/cta' );
}

get_footer();
