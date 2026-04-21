<?php
/**
 * Purchase Token Manager
 *
 * Handles secure token generation and validation for purchase URLs.
 * Prevents enumeration attacks and ensures URL integrity.
 *
 * @package    SkillPulse_LMS
 * @subpackage Modules\Orders
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Purchase Token Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Purchase_Token {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var SkillPulse_LMS_Purchase_Token
	 */
	private static $instance = null;

	/**
	 * Token expiration time (24 hours).
	 *
	 * @since 1.0.0
	 * @var int
	 */
	const TOKEN_EXPIRY = 86400; // 24 hours in seconds.

	/**
	 * Get instance of this class.
	 *
	 * @since 1.0.0
	 * @return SkillPulse_LMS_Purchase_Token
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup_hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
	}

	/**
	 * Setup WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		// Scheduled cleanup of expired tokens.
		add_action( 'splms_cleanup_expired_tokens', array( $this, 'cleanup_expired_tokens' ) );

		// Schedule cleanup if not already scheduled.
		if ( ! wp_next_scheduled( 'splms_cleanup_expired_tokens' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'splms_cleanup_expired_tokens' );
		}
	}

	/**
	 * Generate secure purchase token.
	 *
	 * @since 1.0.0
	 * @param int    $course_id     Course ID.
	 * @param string $purchase_type Purchase type ('full_course' or 'sections').
	 * @param array  $section_ids   Optional. Array of section IDs for section purchases.
	 * @return string|WP_Error Token string or WP_Error on failure.
	 */
	public function generate_token( $course_id, $purchase_type = 'full_course', $section_ids = array() ) {
		$course_id = absint( $course_id );
		if ( ! $course_id ) {
			return new WP_Error( 'invalid_course', __( 'Invalid course ID.', 'skillpulse-lms' ) );
		}

		// Validate course exists.
		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ) );
		}

		$user_id   = get_current_user_id();
		$timestamp = time();

		// Build token payload.
		$payload = array(
			'course_id'     => $course_id,
			'purchase_type' => sanitize_key( $purchase_type ),
			'user_id'       => $user_id,
			'timestamp'     => $timestamp,
			'nonce'         => wp_create_nonce( 'purchase_' . $course_id . '_' . $user_id . '_' . $timestamp ),
		);

		// Add section IDs for section-based purchases.
		if ( 'sections' === $purchase_type && ! empty( $section_ids ) ) {
			$payload['section_ids'] = array_map( 'absint', $section_ids );
		}

		// Generate HMAC signature.
		$secret_key           = $this->get_secret_key();
		$payload_string       = wp_json_encode( $payload );
		$signature            = hash_hmac( 'sha256', $payload_string, $secret_key );
		$payload['signature'] = $signature;

		// Encode token using URL-safe Base64.
		// Standard Base64 uses + and / which are not URL-safe.
		// URL-safe Base64 replaces + with -, / with _, and removes = padding.
		$token_json = wp_json_encode( $payload );
		$token      = str_replace(
			array( '+', '/', '=' ),
			array( '-', '_', '' ),
			base64_encode( $token_json ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		);

		// Store token in transient for tracking (optional).
		$transient_key = 'splms_token_' . md5( $token );
		set_transient( $transient_key, $payload, self::TOKEN_EXPIRY );

		return $token;
	}

	/**
	 * Validate purchase token.
	 *
	 * @since 1.0.0
	 * @param string $token Token to validate.
	 * @return array|WP_Error Token data array or WP_Error on failure.
	 */
	public function validate_token( $token ) {
		if ( empty( $token ) ) {
			return new WP_Error( 'missing_token', __( 'Purchase token is missing.', 'skillpulse-lms' ) );
		}

		// Decode URL-safe Base64 token.
		// URL-safe Base64 uses - instead of +, _ instead of /, and no = padding.
		// Convert back to standard Base64 for decoding.
		$base64_token = str_replace( array( '-', '_' ), array( '+', '/' ), $token );

		// Add padding if needed (Base64 strings should be multiple of 4).
		$padding = strlen( $base64_token ) % 4;
		if ( $padding > 0 ) {
			$base64_token .= str_repeat( '=', 4 - $padding );
		}

		// Decode token.
		$token_json = base64_decode( $base64_token, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $token_json ) {
			return new WP_Error( 'invalid_token_format', __( 'Invalid token format.', 'skillpulse-lms' ) );
		}

		$payload = json_decode( $token_json, true );
		if ( ! is_array( $payload ) ) {
			return new WP_Error( 'invalid_token_data', __( 'Invalid token data.', 'skillpulse-lms' ) );
		}

		// Validate required fields.
		$required_fields = array( 'course_id', 'purchase_type', 'user_id', 'timestamp', 'nonce', 'signature' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $payload[ $field ] ) ) {
				return new WP_Error( 'incomplete_token', __( 'Token is missing required data.', 'skillpulse-lms' ) );
			}
		}

		// Verify signature.
		$signature = $payload['signature'];
		unset( $payload['signature'] );

		$secret_key         = $this->get_secret_key();
		$payload_string     = wp_json_encode( $payload );
		$expected_signature = hash_hmac( 'sha256', $payload_string, $secret_key );

		if ( ! hash_equals( $expected_signature, $signature ) ) {
			return new WP_Error( 'invalid_signature', __( 'Token signature verification failed.', 'skillpulse-lms' ) );
		}

		// Check expiration.
		$current_time = time();
		$time_diff    = $current_time - $payload['timestamp'];

		if ( $time_diff > self::TOKEN_EXPIRY ) {
			return new WP_Error( 'expired_token', __( 'This purchase link has expired. Please return to the course page to get a new link.', 'skillpulse-lms' ) );
		}

		if ( $time_diff < 0 ) {
			return new WP_Error( 'invalid_timestamp', __( 'Invalid token timestamp.', 'skillpulse-lms' ) );
		}

		// Verify nonce.
		$nonce_action = 'purchase_' . $payload['course_id'] . '_' . $payload['user_id'] . '_' . $payload['timestamp'];

		if ( ! wp_verify_nonce( $payload['nonce'], $nonce_action ) ) {
			return new WP_Error( 'invalid_nonce', __( 'Token nonce verification failed.', 'skillpulse-lms' ) );
		}

		// Verify user (if logged in).
		$current_user_id = get_current_user_id();

		if ( $current_user_id > 0 && (int) $payload['user_id'] !== $current_user_id ) {
			return new WP_Error( 'user_mismatch', __( 'This purchase link is not valid for your account.', 'skillpulse-lms' ) );
		}

		// Verify course still exists.
		$course = get_post( $payload['course_id'] );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ) );
		}

		// All validations passed - return payload.
		return $payload;
	}

	/**
	 * Get secret key for HMAC signing.
	 *
	 * @since 1.0.0
	 * @return string Secret key.
	 */
	private function get_secret_key() {
		// Use the constant defined in constants.php.
		if ( defined( 'SPLMS_PURCHASE_TOKEN_SECRET' ) && SPLMS_PURCHASE_TOKEN_SECRET ) {
			return SPLMS_PURCHASE_TOKEN_SECRET;
		}

		// Fallback (should not happen in normal circumstances).
		return wp_hash( 'splms_purchase_token_fallback_' . AUTH_KEY );
	}

	/**
	 * Cleanup expired token transients.
	 *
	 * @since 1.0.0
	 */
	public function cleanup_expired_tokens() {
		global $wpdb;

		// Delete expired transients (WordPress handles this automatically, but we can force cleanup).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_splms_token_' ) . '%',
				time()
			)
		);

		// Also delete the corresponding transient data.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table write operation.
		$wpdb->query(
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_splms_token_%'
			AND option_name NOT IN (
				SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
				FROM {$wpdb->options}
				WHERE option_name LIKE '_transient_timeout_splms_token_%'
			)"
		);
	}

	/**
	 * Refresh token (generate new token with same data but new timestamp).
	 *
	 * @since 1.0.0
	 * @param string $old_token Old expired token.
	 * @return string|WP_Error New token or WP_Error on failure.
	 */
	public function refresh_token( $old_token ) {
		// Decode old token without full validation (allow expired).
		// Convert URL-safe Base64 back to standard Base64.
		$base64_token = str_replace( array( '-', '_' ), array( '+', '/' ), $old_token );

		// Add padding if needed.
		$padding = strlen( $base64_token ) % 4;
		if ( $padding > 0 ) {
			$base64_token .= str_repeat( '=', 4 - $padding );
		}

		$token_json = base64_decode( $base64_token, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		if ( false === $token_json ) {
			return new WP_Error( 'invalid_token_format', __( 'Invalid token format.', 'skillpulse-lms' ) );
		}

		$payload = json_decode( $token_json, true );
		if ( ! is_array( $payload ) || ! isset( $payload['course_id'] ) ) {
			return new WP_Error( 'invalid_token_data', __( 'Invalid token data.', 'skillpulse-lms' ) );
		}

		// Generate new token with same data.
		$course_id     = $payload['course_id'];
		$purchase_type = isset( $payload['purchase_type'] ) ? $payload['purchase_type'] : 'full_course';
		$section_ids   = isset( $payload['section_ids'] ) ? $payload['section_ids'] : array();

		return $this->generate_token( $course_id, $purchase_type, $section_ids );
	}
}
