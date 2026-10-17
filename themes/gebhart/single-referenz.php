<?php
/**
 * Referenz: Einzelansicht
 */
get_header();

while ( have_posts() ) :
	the_post();

	$untertitel = gebhart_ref( 'ref_untertitel' );
	$kunde      = gebhart_ref( 'ref_kunde' );
	$branche    = gebhart_ref( 'ref_branche' );
	$jahr       = gebhart_ref( 'ref_jahr' );
	$leistungen = gebhart_ref( 'ref_leistungen' );
	$url        = gebhart_ref( 'ref_url' );
	$sections   = array(
		'Ausgangslage' => gebhart_ref( 'ref_ausgangslage' ),
		'Vorgehen'     => gebhart_ref( 'ref_vorgehen' ),
		'Ergebnis'     => gebhart_ref( 'ref_ergebnis' ),
	);
	$free       = trim( get_the_content() );
	?>

	<div class="wrap section">
		<p><a class="back-link" href="<?php echo esc_url( get_post_type_archive_link( 'referenz' ) ); ?>">← Alle Referenzen</a></p>

		<div class="single-ref" style="margin-top:32px">

			<div class="single-ref__head">
				<?php if ( $untertitel ) : ?>
					<p class="eyebrow"><?php echo esc_html( $untertitel ); ?></p>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
			</div>

			<div class="single-ref__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'gebhart-wide', array( 'class' => 'article__image', 'alt' => '' ) ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $kunde || $branche || $jahr || $url || $leistungen ) : ?>
				<dl class="single-ref__facts">
					<?php if ( $kunde ) : ?>
						<div><dt>Kunde</dt><dd><?php echo esc_html( $kunde ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $branche ) : ?>
						<div><dt>Branche</dt><dd><?php echo esc_html( $branche ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $jahr ) : ?>
						<div><dt>Jahr</dt><dd><?php echo esc_html( $jahr ); ?></dd></div>
					<?php endif; ?>
					<?php if ( $url ) : ?>
						<div><dt>Website</dt><dd><a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $url ) ) ); ?></a></dd></div>
					<?php endif; ?>
					<?php if ( $leistungen ) : ?>
						<div class="full"><dt>Leistung</dt><dd><?php echo esc_html( $leistungen ); ?></dd></div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>

			<div class="single-ref__body">
				<?php foreach ( $sections as $heading => $text ) : ?>
					<?php if ( '' !== $text ) : ?>
						<section>
							<h2><?php echo esc_html( $heading ); ?></h2>
							<p><?php echo esc_html( $text ); ?></p>
						</section>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ( '' !== $free ) : ?>
					<div class="article__body"><?php the_content(); ?></div>
				<?php endif; ?>

				<p><a class="btn" href="<?php echo esc_url( gebhart_contact_url() ); ?>">Ähnliches Projekt anfragen ↗</a></p>
			</div>

		</div>

		<?php
		$next = get_next_post();
		if ( ! $next ) {
			$first = get_posts(
				array(
					'post_type'      => 'referenz',
					'posts_per_page' => 1,
					'post__not_in'   => array( get_the_ID() ),
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			$next  = ! empty( $first ) ? $first[0] : null;
		}
		if ( $next ) :
			?>
			<a class="next-link" href="<?php echo esc_url( get_permalink( $next ) ); ?>" style="margin-top:56px">
				<span class="meta-label">Nächste Referenz</span>
				<strong><?php echo esc_html( get_the_title( $next ) ); ?> →</strong>
			</a>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
