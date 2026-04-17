<?php
/**
 * Header Template Functions
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Render site logo or site title
 */
function splms_render_site_logo() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		$logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
		?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="splms-logo-link" rel="home">
			<img src="<?php echo esc_url( $logo_url ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="splms-logo-img">
		</a>
	<?php } else { ?>
		<h1 class="splms-site-title">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<?php bloginfo( 'name' ); ?>
			</a>
		</h1>
		<?php
		$description = get_bloginfo( 'description', 'display' );
		if ( $description || is_customize_preview() ) {
			?>
			<p class="splms-site-description"><?php echo esc_html( $description ); ?></p>
		<?php } ?>
		<?php
	}
}

/**
 * Render primary navigation menu
 */
function splms_render_primary_menu() {
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_id'        => 'splms-primary-menu',
			'menu_class'     => 'splms-nav-menu',
			'container'      => false,
			'fallback_cb'    => 'splms_fallback_menu',
		)
	);
}

/**
 * Render notifications bell for logged-in users
 */
function splms_render_notifications_bell() {
	// Don't render if in-app notifications are disabled globally.
	if ( ! function_exists( 'splms_is_in_app_notifications_enabled' ) || ! splms_is_in_app_notifications_enabled() ) {
		return;
	}

	// Get unread count from database table (not user meta).
	$unread_count = 0;
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		// Use the notifications query class to get unread count from database table.
		if ( class_exists( 'SkillPulse_LMS_Notifications_Query' ) ) {
			$query                = SkillPulse_LMS_Notifications_Query::get_instance();
			$unread_notifications = $query->get_notifications(
				array(
					'user_id' => $user_id,
					'is_read' => false,
				)
			);
			$unread_count         = count( $unread_notifications );
		}
	}
	?>
	<div class="splms-notifications-trigger" id="splms-notifications-bell">
		<button class="splms-notification-btn" aria-label="<?php esc_attr_e( 'Notifications', 'skillpulse-lms' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				<path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="splms-notification-count" <?php echo $unread_count > 0 ? '' : 'style="display: none;"'; ?>><?php echo esc_html( $unread_count ); ?></span>
		</button>
		<div class="splms-notifications-dropdown" id="splms-notifications-dropdown">
			<!-- Notifications will be loaded via AJAX -->
		</div>
	</div>
	<?php
}

/**
 * Render user dropdown menu items
 */
function splms_render_user_dropdown_items() {
	$menu_items = array(
		'courses' => array(
			'url'   => home_url( '/courses/' ),
			'title' => __( 'My Courses', 'skillpulse-lms' ),
			'icon'  => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		),
	);

	foreach ( $menu_items as $item ) {
		?>
		<a href="<?php echo esc_url( $item['url'] ); ?>" class="splms-dropdown-item">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<?php echo wp_kses_post( $item['icon'] ); ?>
			</svg>
			<?php echo esc_html( $item['title'] ); ?>
		</a>
		<?php
	}

	// Admin panel link for administrators.
	if ( current_user_can( 'manage_options' ) ) {
		?>
		<div class="splms-dropdown-separator"></div>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=skillpulse-lms' ) ); ?>" class="splms-dropdown-item">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
				<path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="splms-dropdown-item-text"><?php esc_html_e( 'Admin Panel', 'skillpulse-lms' ); ?></span>
		</a>
	<?php } ?>

	<div class="splms-dropdown-separator"></div>
	<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="splms-dropdown-item splms-logout-item">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			<line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<span class="splms-dropdown-item-text"><?php esc_html_e( 'Logout', 'skillpulse-lms' ); ?></span>
	</a>
	<?php
}

/**
 * Render authentication buttons for non-logged-in users
 */
function splms_render_auth_buttons() {
	$login_url    = wp_login_url();
	$register_url = wp_registration_url();
	?>
	<div class="splms-auth-buttons">
		<a href="<?php echo esc_url( $login_url ); ?>" class="splms-btn splms-btn-outline">
			<?php esc_html_e( 'Login', 'skillpulse-lms' ); ?>
		</a>
		<a href="<?php echo esc_url( $register_url ); ?>" class="splms-btn splms-btn-primary">
			<?php esc_html_e( 'Register', 'skillpulse-lms' ); ?>
		</a>
	</div>
	<?php
}

/**
 * Render mobile menu toggle button
 */
function splms_render_mobile_toggle() {
	?>
	<button class="splms-mobile-menu-toggle" aria-expanded="false" aria-controls="splms-mobile-menu">
		<span class="splms-hamburger">
			<span class="splms-hamburger-line"></span>
			<span class="splms-hamburger-line"></span>
			<span class="splms-hamburger-line"></span>
		</span>
		<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'skillpulse-lms' ); ?></span>
	</button>
	<?php
}

/**
 * Render mobile menu content
 */
function splms_render_mobile_menu() {
	?>
	<div class="splms-mobile-menu-content">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'menu_id'        => 'splms-mobile-menu-list',
				'menu_class'     => 'splms-mobile-nav-menu',
				'container'      => false,
				'fallback_cb'    => 'splms_fallback_menu',
			)
		);
		?>

		<?php if ( is_user_logged_in() ) { ?>
			<div class="splms-mobile-user-actions">
				<a href="<?php echo esc_url( home_url( '/courses/' ) ); ?>" class="splms-mobile-menu-item">
					<?php esc_html_e( 'Profile', 'skillpulse-lms' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="splms-mobile-menu-item">
					<?php esc_html_e( 'Logout', 'skillpulse-lms' ); ?>
				</a>
			</div>
		<?php } else { ?>
			<div class="splms-mobile-auth-buttons">
				<a href="<?php echo esc_url( wp_login_url() ); ?>" class="splms-btn splms-btn-outline">
					<?php esc_html_e( 'Login', 'skillpulse-lms' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="splms-btn splms-btn-primary">
					<?php esc_html_e( 'Register', 'skillpulse-lms' ); ?>
				</a>
			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * Default menu function
 */
function splms_fallback_menu() {
	echo '<ul class="splms-nav-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'skillpulse-lms' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/courses/' ) ) . '">' . esc_html__( 'Courses', 'skillpulse-lms' ) . '</a></li>';
	echo '</ul>';
}

?>
