<?php
/**
 * Cookie-Consent (CCM19). Das Skript muss möglichst früh im <head> stehen,
 * damit es Dienste von Drittanbietern (z. B. Google-Bewertungen) vor der Einwilligung blockieren kann.
 */
add_action(
	'wp_head',
	static function () {
		if ( is_admin() || is_customize_preview() ) {
			return;
		}
		echo '<script src="https://cloud.ccm19.de/app.js?apiKey=e5a1491d732cc9d1c224c925a5b1ec58eef51455a8c834a6&amp;domain=6ada64f5d6bfdfb74d08a812" referrerpolicy="origin"></script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	0
);
