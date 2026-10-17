<?php
/**
 * Referenzen einer Leistung. Nutzt dieselbe Darstellung wie die Übersicht.
 */
get_header();
$term = get_queried_object();
?>

<section class="page-head">
	<div class="wrap">
		<p class="eyebrow">Referenzen · Leistung</p>
		<h1><?php echo esc_html( $term->name ); ?></h1>
		<?php if ( ! empty( $term->description ) ) : ?>
			<p class="page-head__lead"><?php echo esc_html( $term->description ); ?></p>
		<?php endif; ?>
		<p><a class="back-link" href="<?php echo esc_url( get_post_type_archive_link( 'referenz' ) ); ?>">← Alle Referenzen</a></p>
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
			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p>Zu dieser Leistung gibt es noch keine Referenz.</p>
		<?php endif; ?>
	</div>
</section>

<?php get_template_part( 'template-parts/cta' ); ?>

<?php
get_footer();
