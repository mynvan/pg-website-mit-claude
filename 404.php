<?php
/**
 * Seite nicht gefunden
 */
get_header();
?>

<div class="wrap not-found">
	<p class="eyebrow">Fehler 404</p>
	<h1>Diese Seite gibt es nicht.</h1>
	<p>Die Adresse stimmt nicht oder die Seite wurde verschoben. Von der Startseite aus findest du alles.</p>
	<p><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">Zur Startseite</a></p>
</div>

<?php
get_footer();
