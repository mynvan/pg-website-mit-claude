<?php
/**
 * Beitrag als Zeile im Verzeichnis (Wissen). Wird in der Schleife aufgerufen.
 */
$cats = get_the_category();
?>
<a class="post-row<?php echo has_post_thumbnail() ? ' post-row--thumb' : ''; ?>" href="<?php the_permalink(); ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<span class="post-row__thumb"><?php the_post_thumbnail( 'gebhart-card', array( 'alt' => '', 'sizes' => '(min-width: 900px) 300px, 30vw' ) ); ?></span>
	<?php endif; ?>
	<span class="post-row__main">
		<span class="eyebrow"><?php echo ! empty( $cats ) ? esc_html( $cats[0]->name ) . ' · ' : ''; ?><?php echo esc_html( gebhart_date() ); ?></span>
		<span class="post-row__title"><?php the_title(); ?></span>
		<span class="muted post-row__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></span>
	</span>
	<span class="post-row__more">Lesen →</span>
</a>
