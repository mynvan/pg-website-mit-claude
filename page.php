<?php
/**
 * Seiten: Startseite, Leistungen, Über mich, Kontakt und andere.
 * Auf der Startseite erscheint der Titel nicht, dort steht die Überschrift im Inhalt.
 */
get_header();

while ( have_posts() ) :
	the_post();
	?>

	<?php if ( ! is_front_page() ) : ?>
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

if ( ! is_front_page() ) {
	get_template_part( 'template-parts/cta' );
}

get_footer();
