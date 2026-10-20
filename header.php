<?php
/**
 * Kopfbereich
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#F9F7F3">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content">Zum Inhalt springen</a>

<header class="site-header">
	<div class="wrap site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'alt' => get_bloginfo( 'name' ) ) ); ?>
			<?php else : ?>
				<?php echo gebhart_logo_mark(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- festes SVG ?>
				<span class="site-logo__text"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation" aria-label="Menü">
			<span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
		</button>

		<div class="site-header__nav" id="site-navigation">
			<nav class="site-nav" aria-label="Hauptmenü">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'fallback_cb'    => 'gebhart_fallback_menu',
						'depth'          => 2,
					)
				);
				?>
			</nav>
			<a class="btn btn--small" href="<?php echo esc_url( gebhart_contact_url() ); ?>">Projekt anfragen</a>
		</div>
	</div>
</header>

<main id="content" class="site-main">
