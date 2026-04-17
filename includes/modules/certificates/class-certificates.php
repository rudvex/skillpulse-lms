<?php
/**
 * Certificate System
 *
 * WordPress post type-based certificate system similar to LearnDash
 *
 * @since      1.0.0
 * @subpackage Certificates
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Certificate Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Certificates {

	/**
	 * Single instance of the class
	 *
	 * @var SkillPulse_LMS_Certificates
	 */
	private static $instance = null;

	/**
	 * Get single instance
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Certificates
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->setup_hooks();
		$this->schedule_cleanup();
	}

	/**
	 * Setup WordPress hooks
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_hooks() {
		// Auto-generate certificate on course completion (handle both parameter orders).
		add_action( 'splms_course_completed', array( $this, 'maybe_award_certificate' ), 10, 3 );

		// Register certificate rewrite rules and query vars for action key approach.
		add_action( 'init', array( $this, 'add_certificate_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_certificate_query_vars' ) );

		// Add certificate link to course completion.
		add_filter( 'splms_course_completion_message', array( $this, 'add_certificate_link' ), 10, 3 );

		// Certificate lifecycle management.
		add_action( 'delete_post', array( $this, 'handle_certificate_deletion' ), 10, 2 );
		add_action( 'wp_trash_post', array( $this, 'handle_certificate_status_change' ) );
		add_action( 'untrash_post', array( $this, 'handle_certificate_status_change' ) );
		add_action( 'transition_post_status', array( $this, 'handle_certificate_status_transition' ), 10, 3 );
	}

	/**
	 * Maybe award certificate on course completion
	 * Handles both parameter orders: (user_id, course_id) and (course_id, user_id)
	 *
	 * @since 1.0.0
	 *
	 * @param int   $param1 First parameter (could be user_id or course_id).
	 * @param int   $param2 Second parameter (could be course_id or user_id).
	 * @param mixed $param3 Third parameter (enrollment object or null).
	 * @return void
	 */
	public function maybe_award_certificate( $param1, $param2, $param3 = null ) {
		// Determine parameter order by checking which one is a valid course.
		$course_check1 = get_post( $param1 );
		$course_check2 = get_post( $param2 );

		if ( $course_check1 && SPLMS_POST_TYPES['course'] === $course_check1->post_type ) {
			// First parameter is course_id.
			$course_id = $param1;
			$user_id   = $param2;
		} elseif ( $course_check2 && SPLMS_POST_TYPES['course'] === $course_check2->post_type ) {
			// Second parameter is course_id.
			$course_id = $param2;
			$user_id   = $param1;
		} else {
			// Default: first is user_id, second is course_id.
			$user_id   = $param1;
			$course_id = $param2;
		}

		// Get enrollment object if provided, otherwise fetch it.
		$enrollment = $param3;
		if ( ! $enrollment || ! is_object( $enrollment ) ) {
			$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
			$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );
		}

		// Get completion date from enrollment.
		$completion_date = $enrollment && ! empty( $enrollment->completed_at ) ? $enrollment->completed_at : current_time( 'mysql' );

		// Check if certificates are enabled globally.
		if ( ! splms_get_setting( 'enable_certificates', false ) ) {
			return;
		}

		// Check if auto-generation is enabled.
		if ( ! splms_get_setting( 'certificate_auto_generate', true ) ) {
			return;
		}

		// Check if certificate is enabled for this course.
		if ( ! splms_is_certificate_enabled( $course_id ) ) {
			return;
		}

		// Check if user already has a certificate for this course.
		if ( $this->user_has_certificate( $user_id, $course_id ) ) {
			return;
		}

		// Get course certificate template ID.
		$content_info   = splms_get_course_content_info( $course_id );
		$certificate_id = isset( $content_info['certificate_template_id'] ) ? $content_info['certificate_template_id'] : '';

		// If no specific certificate set, try to find a default certificate.
		if ( ! $certificate_id ) {
			$certificate_id = $this->get_default_certificate();
		}

		// Final validation before awarding.
		if ( ! $certificate_id || ! $this->validate_certificate_for_award( $certificate_id, $course_id, $user_id ) ) {
			return;
		}

		// Award the certificate with source tracking.
		$this->award_certificate( $user_id, $course_id, $certificate_id, $completion_date );
	}

	/**
	 * Check if user has certificate for course
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return bool
	 */
	public function user_has_certificate( $user_id, $course_id ) {
		$user_certificates = get_user_meta( $user_id, '_splms_certificates', true );
		if ( ! is_array( $user_certificates ) ) {
			return false;
		}

		foreach ( $user_certificates as $cert ) {
			if ( isset( $cert['course_id'] ) && (int) $cert['course_id'] === (int) $course_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Award certificate to user
	 * Creates certificate post with all meta fields and updates user meta for consistency.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id        User ID.
	 * @param int    $course_id      Course ID.
	 * @param int    $certificate_id Certificate template ID.
	 * @param string $completion_date Completion date (optional, defaults to current time).
	 *
	 * @return int|false Certificate post ID on success, false on failure.
	 */
	public function award_certificate( $user_id, $course_id, $certificate_id, $completion_date = null ) {
		// Get completion date if not provided.
		if ( ! $completion_date ) {
			$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
			$enrollment        = $enrollments_query->get_enrollment( $user_id, $course_id );
			$completion_date   = $enrollment && ! empty( $enrollment->completed_at ) ? $enrollment->completed_at : current_time( 'mysql' );
		}

		// Get template and course data.
		$template = get_post( $certificate_id );
		$course   = get_post( $course_id );
		$user     = get_user_by( 'id', $user_id );

		if ( ! $template || ! $course || ! $user ) {
			return false;
		}

		// Check if certificate post already exists for this user/course combination.
		$existing_certificate = $this->get_certificate_post_for_user_course( $user_id, $course_id );
		if ( $existing_certificate ) {
			// Certificate post already exists, just update user meta.
			$user_certificates = get_user_meta( $user_id, '_splms_certificates', true );
			if ( ! is_array( $user_certificates ) ) {
				$user_certificates = array();
			}

			$certificate_data = array(
				'course_id'      => $course_id,
				'certificate_id' => $certificate_id,
				'awarded_on'     => time(),
			);

			$user_certificates[] = $certificate_data;
			update_user_meta( $user_id, '_splms_certificates', $user_certificates );

			// Trigger certificate awarded action.
			do_action( 'splms_certificate_awarded', $user_id, $course_id, $certificate_id );

			return $existing_certificate->ID;
		}

		// Create certificate post.
		$certificate_post = array(
			'post_title'   => sprintf( '%s - %s Certificate', $course->post_title, $user->display_name ),
			'post_content' => $template->post_content,
			'post_status'  => 'publish',
			'post_type'    => SPLMS_POST_TYPES['certificate'],
			'post_author'  => $user_id,
		);

		$certificate_post_id = wp_insert_post( $certificate_post );

		if ( is_wp_error( $certificate_post_id ) || ! $certificate_post_id ) {
			return false;
		}

		// Store certificate metadata (same as manual generation).
		update_post_meta( $certificate_post_id, '_splms_certificate_user_id', $user_id );
		update_post_meta( $certificate_post_id, '_splms_certificate_course_id', $course_id );
		update_post_meta( $certificate_post_id, '_splms_certificate_template_id', $certificate_id );
		update_post_meta( $certificate_post_id, '_splms_certificate_completion_date', $completion_date );
		update_post_meta( $certificate_post_id, '_splms_certificate_generated_date', current_time( 'mysql' ) );
		update_post_meta( $certificate_post_id, '_splms_certificate_status', 'issued' );

		// Generate secure verification data.
		$verification_system = SkillPulse_LMS_Certificate_Verification::get_instance();
		$verification_system->generate_verification_data( $certificate_post_id, $user_id, $course_id );

		// Clear certificate cache for this user/course combination.
		delete_transient( "splms_cert_{$user_id}_{$course_id}" );

		// Update user meta for consistency.
		$user_certificates = get_user_meta( $user_id, '_splms_certificates', true );
		if ( ! is_array( $user_certificates ) ) {
			$user_certificates = array();
		}

		$certificate_data = array(
			'course_id'      => $course_id,
			'certificate_id' => $certificate_id,
			'awarded_on'     => time(),
		);

		$user_certificates[] = $certificate_data;
		update_user_meta( $user_id, '_splms_certificates', $user_certificates );

		// Trigger certificate awarded action.
		do_action( 'splms_certificate_awarded', $user_id, $course_id, $certificate_id );

		return $certificate_post_id;
	}


	/**
	 * Get certificate post for user and course
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return WP_Post|null Certificate post or null if not found.
	 */
	private function get_certificate_post_for_user_course( $user_id, $course_id ) {
		// Use transient caching for frequently accessed certificates.
		$cache_key = "splms_cert_{$user_id}_{$course_id}";
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached ? get_post( $cached ) : null;
		}

		$args = array(
			'post_type'      => SPLMS_POST_TYPES['certificate'],
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Necessary for finding certificate.
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_splms_certificate_user_id',
					'value'   => $user_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_splms_certificate_course_id',
					'value'   => $course_id,
					'compare' => '=',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_splms_certificate_status',
					'value'   => 'issued',
					'compare' => '=',
				),
			),
		);

		$query          = new WP_Query( $args );
		$certificate_id = $query->have_posts() ? $query->posts[0] : false;

		// Cache result for 1 hour.
		set_transient( $cache_key, $certificate_id, HOUR_IN_SECONDS );

		wp_reset_postdata();

		return $certificate_id ? get_post( $certificate_id ) : null;
	}

	/**
	 * Get certificate link for user and course
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 *
	 * @return string|false Certificate link or false
	 */
	public function get_certificate_link( $user_id, $course_id ) {
		if ( ! $this->user_has_certificate( $user_id, $course_id ) ) {
			return false;
		}

		$data = array(
			'user_id'   => $user_id,
			'course_id' => $course_id,
			'timestamp' => time(),
		);

		$encoded_certificate_key = $this->encode_certificate_data( $data );
		return home_url( '/certificate/' . $encoded_certificate_key );
	}

	/**
	 * Encode certificate data into a secure hash
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Certificate data to encode.
	 *
	 * @return string Encoded hash
	 */
	public function encode_certificate_data( $data ) {
		$json       = wp_json_encode( $data );
		$secret_key = $this->get_certificate_secret_key();
		$hash       = hash_hmac( 'sha256', $json, $secret_key );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Used for certificate data encoding, not code obfuscation.
		$encrypted = base64_encode( $json . '|' . $hash );

		// Make URL-safe.
		return str_replace( array( '=', '+', '/' ), array( '', '-', '_' ), $encrypted );
	}

	/**
	 * Decode certificate data from hash
	 *
	 * @since 1.0.0
	 *
	 * @param string $hash Encoded hash.
	 *
	 * @return array|false Decoded data or false on failure
	 */
	public function decode_certificate_data( $hash ) {
		// Make base64-safe.
		$hash = str_replace( array( '-', '_' ), array( '+', '/' ), $hash );

		// Decode.
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode -- Used for certificate data decoding, not code obfuscation.
		$decoded = base64_decode( $hash );
		if ( false === $decoded ) {
			return false;
		}

		// Split data and verification hash.
		$parts = explode( '|', $decoded, 2 );
		if ( 2 !== count( $parts ) ) {
			return false;
		}

		list( $json, $verification_hash ) = $parts;

		// Verify hash.
		$secret_key    = $this->get_certificate_secret_key();
		$expected_hash = hash_hmac( 'sha256', $json, $secret_key );

		if ( ! hash_equals( $expected_hash, $verification_hash ) ) {
			return false;
		}

		// Decode JSON.
		$data = json_decode( $json, true );
		if ( null === $data ) {
			return false;
		}

		return $data;
	}

	/**
	 * Get or create certificate secret key
	 *
	 * @since 1.0.0
	 *
	 * @return string Secret key
	 */
	private function get_certificate_secret_key() {
		$key = get_option( 'splms_certificate_secret_key' );

		if ( ! $key ) {
			$key = wp_generate_password( 64, true, true );
			update_option( 'splms_certificate_secret_key', $key );
		}

		return $key;
	}

	/**
	 * Add certificate URL rewrite rules
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_certificate_rewrite_rules() {
		add_rewrite_rule( '^certificate/([a-zA-Z0-9\-_]+)/?$', 'index.php?splms_component=certificate&certificate_key=$matches[1]', 'top' );
	}

	/**
	 * Add certificate query vars to WordPress
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars Query variables array.
	 * @return array Modified query variables array.
	 */
	public function add_certificate_query_vars( $vars ) {
		$vars[] = 'splms_component';
		$vars[] = 'certificate_key';
		return $vars;
	}

	/**
	 * Add certificate link to course completion message
	 *
	 * @since 1.0.0
	 *
	 * @param string $message   Completion message.
	 * @param int    $user_id   User ID.
	 * @param int    $course_id Course ID.
	 *
	 * @return string Modified message
	 */
	public function add_certificate_link( $message, $user_id, $course_id ) {
		$certificate_link = $this->get_certificate_link( $user_id, $course_id );

		if ( $certificate_link ) {
			$certificate_buttons = sprintf(
				'<div class="splms-certificate-links">
					<a href="%s" class="button button-secondary" target="_blank">%s</a>
					<button type="button" class="button button-primary" onclick="window.print()">%s</button>
				</div>',
				esc_url( $certificate_link ),
				__( 'View Certificate', 'skillpulse-lms' ),
				__( 'Print Certificate', 'skillpulse-lms' )
			);

			$message .= $certificate_buttons;
		}

		return $message;
	}

	/**
	 * Get user certificates
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 *
	 * @return array User certificates
	 */
	public function get_user_certificates( $user_id ) {
		$user_certificates = get_user_meta( $user_id, '_splms_certificates', true );
		if ( ! is_array( $user_certificates ) ) {
			return array();
		}

		return $user_certificates;
	}


	/**
	 * Get default certificate template
	 *
	 * @since 1.0.0
	 *
	 * @return int|false Certificate ID or false
	 */
	public function get_default_certificate() {
		$default_id = 0; // @todo: we will get default certificate id.

		return $default_id;
	}


	/**
	 * Validate certificate for awarding to user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $certificate_id Certificate template ID.
	 * @param int $course_id      Course ID.
	 * @param int $user_id        User ID.
	 * @return bool True if certificate can be awarded.
	 */
	private function validate_certificate_for_award( $certificate_id, $course_id, $user_id ) {

		// Check if course is valid.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return false;
		}

		// Check if user is valid.
		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) {
			return false;
		}

		// Check if user already has certificate.
		if ( $this->user_has_certificate( $user_id, $course_id ) ) {
			return false;
		}

		// Additional validation can be added here (e.g., course completion requirements).

		return true;
	}

	/**
	 * Handle certificate deletion.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID being deleted.
	 * @param WP_Post $post    Post object being deleted.
	 * @return void
	 */
	public function handle_certificate_deletion( $post_id, $post ) {
		// Only handle certificate post types.
		if ( SPLMS_POST_TYPES['certificate'] !== $post->post_type ) {
			return;
		}

		$was_default = get_post_meta( $post_id, '_splms_certificate_is_default', true );

		// Clear certificate caches.
		delete_transient( 'splms_default_certificate' );
		$this->clear_certificate_user_caches( $post_id );

		// If this was the default certificate, promote another one.
		if ( '1' === $was_default ) {
			$this->promote_new_default_certificate( $post_id );
		}
	}

	/**
	 * Handle certificate status changes (trash/untrash).
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function handle_certificate_status_change( $post_id ) {
		$post = get_post( $post_id );

		// Only handle certificate post types.
		if ( ! $post || SPLMS_POST_TYPES['certificate'] !== $post->post_type ) {
			return;
		}

		// Clear default certificate cache on any status change.
		delete_transient( 'splms_default_certificate' );
	}

	/**
	 * Handle certificate status transitions.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 * @return void
	 */
	public function handle_certificate_status_transition( $new_status, $old_status, $post ) {
		// Only handle certificate post types.
		if ( SPLMS_POST_TYPES['certificate'] !== $post->post_type ) {
			return;
		}

		// Clear cache on any status transition.
		delete_transient( 'splms_default_certificate' );

		// Check if default certificate became unpublished.
		$is_default = get_post_meta( $post->ID, '_splms_certificate_is_default', true );
		if ( '1' === $is_default && 'publish' === $old_status && 'publish' !== $new_status ) {
			// Default certificate became unpublished, promote another one.
			$this->promote_new_default_certificate( $post->ID );
		}
	}

	/**
	 * Promote a new default certificate when current default is removed.
	 *
	 * @since 1.0.0
	 *
	 * @param int $excluded_id Certificate ID to exclude from selection.
	 * @return bool True if new default was set, false otherwise.
	 */
	private function promote_new_default_certificate( $excluded_id ) {
		// Find the next available published certificate to make default.
		$certificates = get_posts(
			array(
				'post_type'      => SPLMS_POST_TYPES['certificate'],
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'exclude'        => array( $excluded_id ),
				'orderby'        => 'date',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $certificates ) ) {
			$new_default_id = $certificates[0];
			update_post_meta( $new_default_id, '_splms_certificate_is_default', '1' );

			// Clear cache to force refresh.
			delete_transient( 'splms_default_certificate' );

			return true;
		}

		return false;
	}

	/**
	 * Clear certificate user caches for a specific template.
	 *
	 * @since 1.0.0
	 *
	 * @param int $certificate_id Certificate template ID.
	 * @return void
	 */
	private function clear_certificate_user_caches( $certificate_id ) {
		global $wpdb;

		// Clear caches for certificates that used this template.
		// Use certificate_id in cache key pattern for more specific clearing.
		$cache_pattern = 'splms_cert_' . $certificate_id . '_%';
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				OR option_name LIKE %s
				OR option_name LIKE %s
				OR option_name LIKE %s",
				'_transient_splms_cert_%',
				'_transient_timeout_splms_cert_%',
				'_transient_' . $cache_pattern,
				'_transient_timeout_' . $cache_pattern
			)
		);
	}

	/**
	 * Schedule periodic certificate file cleanup.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function schedule_cleanup() {
		// Schedule daily cleanup if not already scheduled.
		if ( ! wp_next_scheduled( 'splms_certificate_cleanup' ) ) {
			wp_schedule_event( time(), 'daily', 'splms_certificate_cleanup' );
		}

		// Hook the cleanup function.
		add_action( 'splms_certificate_cleanup', array( $this, 'run_scheduled_cleanup' ) );
	}

	/**
	 * Run scheduled cleanup of certificate files.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run_scheduled_cleanup() {
		// Note: Certificate PDF cleanup no longer needed since we removed PDF generation
		// This method is kept for backward compatibility but does nothing.

		// Certificate cleanup completed - no files to clean since PDF generation was removed.
	}
}
