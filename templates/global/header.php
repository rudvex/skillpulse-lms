<?php
/**
 * SkillPulse LMS Global Header Template
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/global/header.php.
 * This won't conflict with WordPress theme hierarchy or block themes.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hook: splms_before_get_header
 */
do_action( 'splms_before_get_header' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks splms-site-wrapper">

	<header class="splms-header" role="banner">
		<div class="splms-header-container">
			<!-- Site Branding -->
			<div class="splms-site-branding">
				<?php splms_render_site_logo(); ?>
			</div>

			<!-- Main Navigation -->
			<nav class="splms-main-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'skillpulse-lms' ); ?>">
				<?php splms_render_primary_menu(); ?>
			</nav>
		</div>

		<!-- Mobile Menu -->
		<div class="splms-mobile-menu" id="splms-mobile-menu">
			<?php splms_render_mobile_menu(); ?>
		</div>
	</header>

	<main class="splms-main-content" role="main">
		<?php
		/**
		 * Hook: splms_after_get_header
		 */
		do_action( 'splms_after_get_header' );
		?>
