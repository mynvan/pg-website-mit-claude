</main>

<footer class="site-footer">
	<div class="wrap site-footer__top">
		<div>
			<h2><?php bloginfo( 'name' ); ?></h2>
			<p>Websites für Unternehmen. Professionell umgesetzt. Komplett betreut.</p>
			<p>Friedberg bei Augsburg – für Unternehmen in der Region und darüber hinaus.</p>
		</div>
		<div>
			<h2>Leistungen</h2>
			<ul>
				<li><a href="<?php echo esc_url( home_url( '/wordpress-website/' ) ); ?>">WordPress-Websites</a></li>
				<li><a href="<?php echo esc_url( home_url( '/website-relaunch/' ) ); ?>">Website-Relaunch</a></li>
				<li><a href="<?php echo esc_url( home_url( '/wordpress-wartung/' ) ); ?>">Wartung &amp; Betreuung</a></li>
				<li><a href="<?php echo esc_url( home_url( '/suchmaschinenoptimierung-seo/' ) ); ?>">SEO</a></li>
			</ul>
		</div>
		<div>
			<h2>Seiten</h2>
			<?php
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 1,
					)
				);
			} else {
				?>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/ueber-mich/' ) ); ?>">Über mich</a></li>
					<li><a href="<?php echo esc_url( post_type_exists( 'referenz' ) ? get_post_type_archive_link( 'referenz' ) : home_url( '/referenzen/' ) ); ?>">Referenzen</a></li>
					<li><a href="<?php echo esc_url( get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : home_url( '/wissen/' ) ); ?>">Wissen</a></li>
					<li><a href="<?php echo esc_url( gebhart_contact_url() ); ?>">Projektanfrage</a></li>
				</ul>
			<?php } ?>
		</div>
		<div>
			<h2>Kontakt</h2>
			<ul class="footer-contact">
				<li><span class="footer-contact__label">Telefon</span><a href="tel:+491778362302">0177 8362302</a></li>
				<li><span class="footer-contact__label">E-Mail</span><a href="mailto:info@philippgebhart.de">info@philippgebhart.de</a></li>
				<li><span class="footer-contact__label">Erreichbar</span>Mo–Fr 9:00–18:00 Uhr</li>
			</ul>
		</div>
	</div>
	<div class="wrap site-footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Philipp Gebhart</span>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/impressum/' ) ); ?>">Impressum</a></li>
			<li><a href="<?php echo esc_url( get_privacy_policy_url() ? get_privacy_policy_url() : home_url( '/datenschutzerklaerung/' ) ); ?>">Datenschutz</a></li>
		</ul>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
