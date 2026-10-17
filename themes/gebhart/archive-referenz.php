<?php
/**
 * Referenzen: Übersicht
 */
get_header();
?>

<section class="page-head">
	<div class="wrap">
		<p class="eyebrow">Referenzen</p>
		<h1>Einblicke in meine Arbeit.</h1>
		<p class="page-head__lead">Projekte, an denen sichtbar wird, was ich konkret beigetragen habe – von der Gestaltung über die Technik bis zur Weiterentwicklung.</p>
	</div>
</section>

<section class="section" style="padding-top:24px">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<div class="ref-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/referenz-row' );
				}
				?>
			</div>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => '← Vorherige',
					'next_text' => 'Weitere →',
				)
			);
			?>
		<?php else : ?>
			<p>Referenzen folgen in Kürze.</p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
