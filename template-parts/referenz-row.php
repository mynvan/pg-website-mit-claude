<?php
/**
 * Referenz als Zeile (Bild und Text im Wechsel). Wird in der Schleife aufgerufen.
 */
$untertitel = gebhart_ref( 'ref_untertitel' );
$leistungen = gebhart_ref( 'ref_leistungen' );
?>
<article class="ref-row ref-row--archive">
	<a class="ref-row__media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="shot"><?php the_post_thumbnail( 'large', array( 'class' => 'ref-shot', 'alt' => '' ) ); ?></figure>
		<?php else : ?>
			<div class="placeholder-img">Titelbild fehlt noch</div>
		<?php endif; ?>
	</a>
	<div class="ref-row__text">
		<?php if ( $untertitel ) : ?>
			<p class="eyebrow"><?php echo esc_html( $untertitel ); ?></p>
		<?php endif; ?>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<?php if ( has_excerpt() ) : ?>
			<p><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
		<?php if ( $leistungen ) : ?>
			<p class="ref-row__tags"><?php echo esc_html( $leistungen ); ?></p>
		<?php endif; ?>
		<p><a href="<?php the_permalink(); ?>">Projekt ansehen →</a></p>
	</div>
</article>
