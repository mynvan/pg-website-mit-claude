<?php
/**
 * Wissen: Verzeichnis mit Themenfilter, Hervorhebung des neuesten Beitrags und Liste.
 * Erwartet $args: title, intro (HTML, optional)
 */
$title = isset( $args['title'] ) ? $args['title'] : 'Wissen';
$intro = isset( $args['intro'] ) ? $args['intro'] : '';

$posts_page = (int) get_option( 'page_for_posts' );
$all_url    = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
$current    = is_category() ? get_queried_object_id() : 0;
$cats       = get_categories( array( 'hide_empty' => true ) );
$paged      = max( 1, (int) get_query_var( 'paged' ) );
$feature    = ( 1 === $paged && ! is_category() && ! is_tag() && have_posts() );
?>

<section class="page-head">
	<div class="wrap">
		<p class="eyebrow">Wissen</p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?>
			<div class="page-head__lead"><?php echo wp_kses_post( $intro ); ?></div>
		<?php endif; ?>
	</div>
</section>

<?php if ( count( $cats ) > 1 || $current ) : ?>
	<section class="wrap" style="padding-bottom:32px">
		<ul class="post-filter" aria-label="Themen">
			<li class="<?php echo $current ? '' : 'is-active'; ?>"><a href="<?php echo esc_url( $all_url ); ?>">Alle</a></li>
			<?php foreach ( $cats as $cat ) : ?>
				<?php if ( 1 === (int) $cat->term_id && 'uncategorized' === $cat->slug ) { continue; } ?>
				<li class="<?php echo ( $current === (int) $cat->term_id ) ? 'is-active' : ''; ?>"><a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</section>
<?php endif; ?>

<section class="wrap section" style="padding-top:8px">
	<?php if ( have_posts() ) : ?>

		<?php
		if ( $feature ) :
			the_post();
			$fcats = get_the_category();
			?>
			<article class="post-feature" style="margin-bottom:56px">
				<?php if ( has_post_thumbnail() ) : ?>
					<a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1"><?php the_post_thumbnail( 'gebhart-card', array( 'alt' => '' ) ); ?></a>
				<?php endif; ?>
				<div class="post-feature__text">
					<p class="eyebrow">Neuester Beitrag<?php echo ! empty( $fcats ) ? ' · ' . esc_html( $fcats[0]->name ) : ''; ?> · <?php echo esc_html( gebhart_date() ); ?></p>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="muted"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<p><a href="<?php the_permalink(); ?>">Beitrag lesen →</a></p>
				</div>
			</article>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post-row' );
				}
				?>
			</div>
		<?php endif; ?>

		<?php
		the_posts_pagination(
			array(
				'prev_text' => '← Neuere Beiträge',
				'next_text' => 'Ältere Beiträge →',
			)
		);
		?>

	<?php else : ?>
		<p>Hier erscheint bald der erste Beitrag.</p>
	<?php endif; ?>
</section>

<?php get_template_part( 'template-parts/cta' ); ?>
