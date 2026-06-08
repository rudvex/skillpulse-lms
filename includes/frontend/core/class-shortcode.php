<?php
/**
 * Shortcode Handler
 *
 * Handles all shortcode registrations and rendering for SkillPulse LMS.
 *
 * @since   1.0.0
 * @package SPLMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Shortcode Handler Class
 *
 * Manages shortcode registration and rendering for the SkillPulse LMS plugin.
 *
 * @since   1.0.0
 * @package SPLMS
 */
class SPLMS_Shortcode {

	/**
	 * Class instance.
	 *
	 * @var SPLMS_Shortcode|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @return SPLMS_Shortcode
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Action hooks
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes.
	 *
	 * @since 1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'splms_notifications', array( $this, 'render_notifications_shortcode' ) );
		add_shortcode( 'splms_social_share', array( $this, 'render_social_share_shortcode' ) );
	}

	/**
	 * Render the notifications shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string Shortcode output.
	 */
	public static function render_notifications_shortcode( $atts ) {
		// Check if user is logged in.
		if ( ! is_user_logged_in() ) {
			return '<div class="splms-notifications-login-required">' .
					__( 'Please log in to view your notifications.', 'skillpulse-lms' ) .
					'</div>';
		}

		$atts = shortcode_atts(
			array(
				'per_page'      => 10,
				'show_filter'   => 'true',
				'show_mark_all' => 'true',
			),
			$atts
		);

		ob_start();
		?>
		<div class="splms-notifications-page" id="splms-notifications-frontend">
			<div class="splms-notifications-header">
				<h2><?php esc_html_e( 'Your Notifications', 'skillpulse-lms' ); ?></h2>
				<div class="splms-notifications-actions">
					<?php if ( 'true' === $atts['show_mark_all'] ) { ?>
						<button class="splms-btn splms-btn-secondary" id="splms-mark-all-notifications-read">
							<?php esc_html_e( 'Mark All Read', 'skillpulse-lms' ); ?>
						</button>
					<?php } ?>
					<button class="splms-btn splms-btn-primary" id="splms-refresh-notifications">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M4 12C4 16.41 7.59 20 12 20C16.41 20 20 16.41 20 12C20 7.59 16.41 4 12 4C9.53 4 7.33 5.12 5.86 6.93L7.5 8.5H2V3L3.24 4.24C5.32 1.85 8.5 0.5 12 0.5C18.08 0.5 23 5.42 23 11.5S18.08 22.5 12 22.5S1 17.58 1 11.5H4Z"
									fill="currentColor"/>
						</svg>
						<?php esc_html_e( 'Refresh', 'skillpulse-lms' ); ?>
					</button>
				</div>
			</div>

			<?php if ( 'true' === $atts['show_filter'] ) { ?>
				<div class="splms-notifications-filters">
					<div class="splms-filter-group">
						<label for="splms-notification-type-filter"><?php esc_html_e( 'Filter by type:', 'skillpulse-lms' ); ?></label>
						<select id="splms-notification-type-filter">
							<option value=""><?php esc_html_e( 'All notifications', 'skillpulse-lms' ); ?></option>
							<option value="course"><?php esc_html_e( 'Courses', 'skillpulse-lms' ); ?></option>
							<option value="enrollment"><?php esc_html_e( 'Enrollments', 'skillpulse-lms' ); ?></option>
							<option value="completion"><?php esc_html_e( 'Completions', 'skillpulse-lms' ); ?></option>
							<option value="certificate"><?php esc_html_e( 'Certificates', 'skillpulse-lms' ); ?></option>
							<option value="info"><?php esc_html_e( 'Information', 'skillpulse-lms' ); ?></option>
							<option value="warning"><?php esc_html_e( 'Warnings', 'skillpulse-lms' ); ?></option>
						</select>
					</div>
					<div class="splms-filter-group">
						<label for="splms-notification-status-filter"><?php esc_html_e( 'Filter by status:', 'skillpulse-lms' ); ?></label>
						<select id="splms-notification-status-filter">
							<option value=""><?php esc_html_e( 'All notifications', 'skillpulse-lms' ); ?></option>
							<option value="unread"><?php esc_html_e( 'Unread only', 'skillpulse-lms' ); ?></option>
							<option value="read"><?php esc_html_e( 'Read only', 'skillpulse-lms' ); ?></option>
						</select>
					</div>
				</div>
			<?php } ?>

			<div class="splms-notifications-content">
				<div class="splms-notifications-loading" id="splms-notifications-loading">
					<div class="splms-spinner"></div>
					<p><?php esc_html_e( 'Loading your notifications...', 'skillpulse-lms' ); ?></p>
				</div>

				<div class="splms-notifications-list" id="splms-notifications-list" style="display: none;">
					<!-- Notifications will be loaded here via JavaScript -->
				</div>

				<div class="splms-notifications-empty" id="splms-notifications-empty" style="display: none;">
					<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 2C13.1 2 14 2.9 14 4C14 4.74 13.6 5.39 13 5.73V7C13 10.76 15.15 14 18 16V17H6V16C8.85 14 11 10.76 11 7V5.73C10.4 5.39 10 4.74 10 4C10 2.9 10.9 2 12 2ZM10 21C10 22.1 10.9 23 12 23S14 22.1 14 21H10Z"
								fill="currentColor"/>
					</svg>
					<h3><?php esc_html_e( 'No notifications found', 'skillpulse-lms' ); ?></h3>
					<p><?php esc_html_e( 'You don\'t have any notifications matching the current filters.', 'skillpulse-lms' ); ?></p>
				</div>

				<div class="splms-notifications-pagination" id="splms-notifications-pagination" style="display: none;">
					<button class="splms-btn splms-btn-secondary" id="splms-load-more-notifications">
						<?php esc_html_e( 'Load More', 'skillpulse-lms' ); ?>
					</button>
				</div>
			</div>
		</div>

		<?php
		wp_add_inline_script(
			'jquery',
			'jQuery(document).ready(function(){if(typeof SPLMSNotifications!=="undefined"&&!window.splmsNotifications){window.splmsNotifications=new SPLMSNotifications()}});'
		);
		?>
		<?php

		return ob_get_clean();
	}

	/**
	 * Render the social share shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @since 1.0.0
	 * @return string Shortcode output.
	 */
	public function render_social_share_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'course_id' => get_the_ID(),
				'platforms' => 'facebook,twitter,linkedin',
				'style'     => 'buttons',
			),
			$atts
		);

		$course_id = intval( $atts['course_id'] );
		$platforms = explode( ',', $atts['platforms'] );
		$style     = $atts['style'];

		if ( ! $course_id ) {
			return '';
		}

		$output = '<div class="splms-social-share ' . esc_attr( $style ) . '">';

		foreach ( $platforms as $platform ) {
			$platform  = trim( $platform );
			$share_url = splms_generate_social_share_url( $course_id, $platform );

			if ( $share_url ) {
				$output .= sprintf(
					'<a href="%s" class="share-%s" target="_blank" data-course-id="%d" data-platform="%s">%s</a>',
					esc_url( $share_url ),
					esc_attr( $platform ),
					$course_id,
					esc_attr( $platform ),
					ucfirst( $platform )
				);
			}
		}

		$output .= '</div>';

		return $output;
	}
}
