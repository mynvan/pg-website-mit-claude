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
					<figure class="shot"><?php the_post_thumbnail( 'full', array( 'class' => 'single-ref__image', 'alt' => '' ) ); ?></figure>
				<?php elseif ( current_user_can( 'edit_post', get_the_ID() ) ) : ?>
					<div class="placeholder-img single-ref__image">Titelbild fehlt noch – im Backend als Beitragsbild setzen<br>(nur für dich sichtbar)</div>
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
							<?php echo gebhart_ref_format( $text ); // phpcs:ignore WordPress.Security.EscapeOutput -- in gebhart_ref_format() escaped. ?>
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
		// Vorherige/nächste Referenz in derselben Reihenfolge wie die Übersicht.
		$ref_ids = get_posts(
			array(
				'post_type'      => 'referenz',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'orderby'        => array(
					'menu_order' => 'ASC',
					'date'       => 'DESC',
				),
				'no_found_rows'  => true,
			)
		);
		$pos  = array_search( get_the_ID(), $ref_ids, true );
		$prev = ( false !== $pos && $pos > 0 ) ? $ref_ids[ $pos - 1 ] : 0;
		$next = ( false !== $pos && $pos < count( $ref_ids ) - 1 ) ? $ref_ids[ $pos + 1 ] : 0;
		if ( $prev || $next ) :
			?>
			<nav class="ref-pager" aria-label="Weitere Referenzen">
				<?php if ( $prev ) : ?>
					<a class="ref-pager__link ref-pager__link--prev" href="<?php echo esc_url( get_permalink( $prev ) ); ?>" rel="prev">
						<span class="meta-label">← Vorherige Referenz</span>
						<strong><?php echo esc_html( get_the_title( $prev ) ); ?></strong>
					</a>
				<?php endif; ?>
				<?php if ( $next ) : ?>
					<a class="ref-pager__link ref-pager__link--next" href="<?php echo esc_url( get_permalink( $next ) ); ?>" rel="next">
						<span class="meta-label">Nächste Referenz →</span>
						<strong><?php echo esc_html( get_the_title( $next ) ); ?></strong>
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	</div>

	<?php
endwhile;

get_footer();
