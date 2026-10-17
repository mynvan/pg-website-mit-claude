<?php
/**
 * Fallback für alles, was keine eigene Vorlage hat.
 */
get_header();
?>

<section class="page-head">
	<div class="wrap">
		<h1><?php echo is_search() ? 'Suche' : esc_html( get_bloginfo( 'name' ) ); ?></h1>
	</div>
</section>

<section class="wrap section" style="padding-top:8px">
	<?php if ( have_posts() ) : ?>
		<div class="post-list">
			<?php
			while ( have_posts() ) {
				the_post();
				get_template_part( 'template-parts/post-row' );
			}
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p>Dazu gibt es nichts zu zeigen.</p>
	<?php endif; ?>
</section>

<?php
get_footer();
