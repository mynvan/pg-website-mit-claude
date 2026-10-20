<?php
/**
 * Wissen: Beitrag
 */
get_header();

while ( have_posts() ) :
	the_post();

	$cats     = get_the_category();
	$prepared = gebhart_prepare_content( apply_filters( 'the_content', get_the_content() ) );
	?>

	<div class="wrap wrap--narrow section">
		<article class="article">

			<p><a class="back-link" href="<?php echo esc_url( get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/' ) ); ?>">← Alle Beiträge</a></p>

			<header class="article__head">
				<p class="eyebrow">
					<?php echo ! empty( $cats ) ? esc_html( $cats[0]->name ) . ' · ' : ''; ?>
					<?php echo esc_html( gebhart_date() ); ?> · <?php echo esc_html( gebhart_reading_time() ); ?> Min. Lesezeit
				</p>
				<h1><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="article__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<p class="muted" style="font-size:14px">Von <?php echo esc_html( get_the_author() ); ?></p>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'gebhart-wide', array( 'class' => 'article__image', 'alt' => '' ) ); ?>
			<?php endif; ?>

			<?php if ( count( $prepared['toc'] ) >= 3 ) : ?>
				<nav class="toc" aria-label="Inhaltsverzeichnis">
					<p class="meta-label">In diesem Beitrag</p>
					<?php foreach ( $prepared['toc'] as $item ) : ?>
						<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<div class="article__body">
				<?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Ausgabe des Beitragsinhalts nach the_content-Filter ?>
			</div>

			<aside class="author-box" aria-label="Über den Autor">
				<img class="author-box__img" src="<?php echo esc_url( home_url( '/wp-content/uploads/2026/10/philipp-gebhart-portrait-01.webp' ) ); ?>" alt="" width="96" height="96" loading="lazy">
				<div class="author-box__text">
					<p class="meta-label">Über den Autor</p>
					<p class="author-box__name">Philipp Gebhart</p>
					<p>Webdesigner aus Friedberg bei Augsburg mit Hintergrund im Online-Marketing. Seit 2011 baue ich WordPress-Websites für Unternehmen und unterrichte seit über zehn Jahren an der VHS Augsburg.</p>
					<p><a href="<?php echo esc_url( home_url( '/ueber-mich/' ) ); ?>">Mehr über mich →</a></p>
				</div>
			</aside>

			<section class="cta-box">
				<div>
					<h2 style="font-size:26px">Dein Projekt</h2>
					<p>Erzähl mir kurz von deinem Vorhaben – ich melde mich persönlich bei dir.</p>
				</div>
				<a class="btn" href="<?php echo esc_url( gebhart_contact_url() ); ?>">Projekt anfragen ↗</a>
			</section>

			<?php
			$related = new WP_Query(
				array(
					'post_type'           => 'post',
					'posts_per_page'      => 2,
					'post__not_in'        => array( get_the_ID() ),
					'ignore_sticky_posts' => true,
					'no_found_rows'       => true,
				)
			);
			if ( $related->have_posts() ) :
				?>
				<section>
					<h2 style="font-size:26px;margin-bottom:8px">Weitere Beiträge</h2>
					<div class="post-list">
						<?php
						while ( $related->have_posts() ) {
							$related->the_post();
							get_template_part( 'template-parts/post-row' );
						}
						wp_reset_postdata();
						?>
					</div>
				</section>
			<?php endif; ?>

		</article>
	</div>

	<?php
endwhile;

get_footer();
