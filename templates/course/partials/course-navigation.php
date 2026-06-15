<?php
/**
 * Single course navigation template part - V2 Improved.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-navigation.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id   = get_the_ID();
$splms_access_info = splms_get_course_access_info( $splms_course_id );
$splms_is_enrolled = splms_is_user_enrolled( $splms_course_id );

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, reading URL parameter only.
$splms_current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';
?>

<div class="splms-nav-wrapper splms-nav-wrapper--v2" id="courseNavWrapper">
	<div class="splms-nav-container">
		<nav class="splms-course-navigation" aria-label="<?php esc_attr_e( 'Course Navigation', 'skillpulse-lms' ); ?>">
			<ul class="splms-nav-tabs">
				<li class="splms-nav-item">
					<a href="<?php echo esc_url( add_query_arg( 'tab', 'overview', get_permalink() ) ); ?>"
						class="splms-nav-link <?php echo 'overview' === $splms_current_tab ? 'splms-nav-link--active' : ''; ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3 7V5C3 3.89543 3.89543 3 5 3H19C20.1046 3 21 3.89543 21 5V7M3 7L12 13L21 7M3 7V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V7"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span class="splms-nav-link__text"><?php esc_html_e( 'Overview', 'skillpulse-lms' ); ?></span>
					</a>
				</li>

				<li class="splms-nav-item">
					<a href="<?php echo esc_url( add_query_arg( 'tab', 'curriculum', get_permalink() ) ); ?>"
						class="splms-nav-link <?php echo 'curriculum' === $splms_current_tab ? 'splms-nav-link--active' : ''; ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
									stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span class="splms-nav-link__text"><?php esc_html_e( 'Curriculum', 'skillpulse-lms' ); ?></span>
					</a>
				</li>

				<li class="splms-nav-item">
					<a href="<?php echo esc_url( add_query_arg( 'tab', 'instructor', get_permalink() ) ); ?>"
						class="splms-nav-link <?php echo 'instructor' === $splms_current_tab ? 'splms-nav-link--active' : ''; ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span class="splms-nav-link__text"><?php esc_html_e( 'Instructor', 'skillpulse-lms' ); ?></span>
					</a>
				</li>

			</ul>
		</nav>
	</div>
</div> 