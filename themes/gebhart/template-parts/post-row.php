<?php
/**
 * Beitrag als Zeile im Verzeichnis (Wissen). Wird in der Schleife aufgerufen.
 */
$cats = get_the_category();
?>
<a class="post-row" href="<?php the_permalink(); ?>">
	<span class="post-row__date"><?php echo esc_html( gebhart_date() ); ?></span>
	<span class="post-row__main">
		<?php if ( ! empty( $cats ) ) : ?>
			<span class="eyebrow"><?php echo esc_html( $cats[0]->name ); ?></span>
		<?php endif; ?>
		<span class="post-row__title"><?php the_title(); ?></span>
		<span class="muted"><?php echo esc_html( get_the_excerpt() ); ?></span>
	</span>
	<span class="post-row__more">Lesen →</span>
</a>
