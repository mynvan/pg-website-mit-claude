<?php
/**
 * CTA-Band "Dein Projekt"
 */
$title = isset( $args['title'] ) ? $args['title'] : 'Dein Projekt';
?>
<section class="cta-band">
	<div class="wrap cta-band__inner">
		<div class="cta-band__text">
			<h2><?php echo esc_html( $title ); ?></h2>
			<p>Beschreibe kurz dein Vorhaben – gemeinsam klären wir, was du brauchst.</p>
		</div>
		<a class="btn" href="<?php echo esc_url( gebhart_contact_url() ); ?>">Projekt anfragen ↗</a>
	</div>
</section>
