<?php
/**
 * Certificate Verification System
 *
 * Provides cryptographically secure certificate verification using digital signatures
 * and anti-tampering mechanisms. Replaces simple token-based verification with
 * robust security measures.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Certificate Verification Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Certificate_Verification {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Certificate_Verification|null $instance
	 */
	private static $instance;

	/**
	 * Verification algorithm.
	 *
	 * @var string
	 */
	private const ALGORITHM = 'sha256';

	/**
	 * Signature expiration time (1 year in seconds).
	 *
	 * @var int
	 */
	private const SIGNATURE_EXPIRY = 365 * 24 * 60 * 60;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Certificate_Verification
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Delay hook initialization until WordPress is ready.
		add_action( 'init', array( $this, 'init_hooks' ), 10 );
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Add verification route.
		$this->add_verification_endpoint();
		add_action( 'wp', array( $this, 'handle_verification_request' ) );
	}

	/**
	 * Generate secure verification data for a certificate.
	 *
	 * @since 1.0.0
	 *
	 * @param int $certificate_id Certificate post ID.
	 * @param int $user_id        User ID.
	 * @param int $course_id      Course ID.
	 *
	 * @return array Verification data.
	 */
	public function generate_verification_data( $certificate_id, $user_id, $course_id ) {
		$timestamp = time();
		$site_url  = get_site_url();

		// Create verification payload.
		$payload = array(
			'certificate_id' => $certificate_id,
			'user_id'        => $user_id,
			'course_id'      => $course_id,
			'issued_at'      => $timestamp,
			'site_url'       => $site_url,
			'version'        => '1.0',
		);

		// Generate unique token.
		$token = $this->generate_secure_token( $payload );

		// Create digital signature.
		$signature = $this->create_digital_signature( $payload, $token );

		// Store verification data.
		$verification_data = array(
			'token'     => $token,
			'signature' => $signature,
			'payload'   => $payload,
			'expires'   => $timestamp + self::SIGNATURE_EXPIRY,
		);

		// Save verification data to certificate.
		update_post_meta( $certificate_id, '_splms_certificate_verification', $verification_data );

		return $verification_data;
	}

	/**
	 * Verify certificate authenticity.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token Certificate verification token.
	 *
	 * @return array|WP_Error Verification result or error.
	 */
	public function verify_certificate( $token ) {
		if ( empty( $token ) ) {
			return new WP_Error( 'invalid_token', 'Invalid verification token.' );
		}

		// Find certificate by token.
		$certificate_id = $this->find_certificate_by_token( $token );
		if ( ! $certificate_id ) {
			return new WP_Error( 'certificate_not_found', 'Certificate not found.' );
		}

		// Get verification data.
		$verification_data = get_post_meta( $certificate_id, '_splms_certificate_verification', true );
		if ( empty( $verification_data ) ) {
			return new WP_Error( 'verification_data_missing', 'Verification data not found.' );
		}

		// Check token match.
		if ( $verification_data['token'] !== $token ) {
			return new WP_Error( 'token_mismatch', 'Token verification failed.' );
		}

		// Check expiration.
		if ( time() > $verification_data['expires'] ) {
			return new WP_Error( 'certificate_expired', 'Certificate verification has expired.' );
		}

		// Verify digital signature.
		if ( ! $this->verify_digital_signature( $verification_data['payload'], $token, $verification_data['signature'] ) ) {
			return new WP_Error( 'signature_invalid', 'Certificate signature verification failed.' );
		}

		// Get certificate details.
		$certificate_details = $this->get_certificate_details( $certificate_id, $verification_data['payload'] );

		return array(
			'verified'    => true,
			'certificate' => $certificate_details,
			'issued_at'   => $verification_data['payload']['issued_at'],
			'expires_at'  => $verification_data['expires'],
		);
	}

	/**
	 * Generate secure token for certificate.
	 *
	 * @since 1.0.0
	 *
	 * @param array $payload Certificate payload.
	 *
	 * @return string Secure token.
	 */
	private function generate_secure_token( $payload ) {
		$data = wp_json_encode( $payload ) . wp_salt( 'secure_auth' ) . time();
		return hash( self::ALGORITHM, $data );
	}

	/**
	 * Create digital signature for certificate.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $payload Certificate payload.
	 * @param string $token   Certificate token.
	 *
	 * @return string Digital signature.
	 */
	private function create_digital_signature( $payload, $token ) {
		$signing_data = wp_json_encode( $payload ) . $token . wp_salt( 'logged_in' );
		return hash_hmac( self::ALGORITHM, $signing_data, wp_salt( 'secure_auth' ) );
	}

	/**
	 * Verify digital signature.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $payload   Certificate payload.
	 * @param string $token     Certificate token.
	 * @param string $signature Expected signature.
	 *
	 * @return bool True if signature is valid.
	 */
	private function verify_digital_signature( $payload, $token, $signature ) {
		$expected_signature = $this->create_digital_signature( $payload, $token );
		return hash_equals( $expected_signature, $signature );
	}

	/**
	 * Find certificate by verification token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token Verification token.
	 *
	 * @return int|false Certificate ID or false if not found.
	 */
	private function find_certificate_by_token( $token ) {
		global $wpdb;

		$certificate_id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta}
				WHERE meta_key = '_splms_certificate_verification'
				AND meta_value LIKE %s
				LIMIT 1",
				'%"token":"' . $wpdb->esc_like( $token ) . '"%'
			)
		);

		return $certificate_id ? (int) $certificate_id : false;
	}

	/**
	 * Get certificate details for verification result.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $certificate_id Certificate ID.
	 * @param array $payload        Certificate payload.
	 *
	 * @return array Certificate details.
	 */
	private function get_certificate_details( $certificate_id, $payload ) {
		$user   = get_user_by( 'id', $payload['user_id'] );
		$course = get_post( $payload['course_id'] );

		return array(
			'id'              => $certificate_id,
			'recipient_name'  => $user ? $user->display_name : '',
			'recipient_email' => $user ? $user->user_email : '',
			'course_title'    => $course ? $course->post_title : '',
			'course_id'       => $payload['course_id'],
			'issued_date'     => wp_date( 'F j, Y', $payload['issued_at'] ),
			'issuer'          => get_bloginfo( 'name' ),
			'site_url'        => $payload['site_url'],
		);
	}

	/**
	 * Add verification endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_verification_endpoint() {
		add_rewrite_rule( 'certificate/verify/([^/]+)/?$', 'index.php?splms_verify_certificate=$matches[1]', 'top' );

		// Only add query var if function exists (WordPress may not be fully loaded during early init).
		if ( function_exists( 'add_query_var' ) ) {
			add_query_var( 'splms_verify_certificate' );
		}
	}

	/**
	 * Handle certificate verification request.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function handle_verification_request() {
		$token = get_query_var( 'splms_verify_certificate' );
		if ( ! $token ) {
			return;
		}

		// Verify certificate.
		$result = $this->verify_certificate( $token );

		if ( is_wp_error( $result ) ) {
			$this->display_verification_error( $result );
		} else {
			$this->display_verification_success( $result );
		}

		exit;
	}

	/**
	 * Display verification error page.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $error Verification error.
	 *
	 * @return void
	 */
	private function display_verification_error( $error ) {
		status_header( 404 );
		echo '<!DOCTYPE html>';
		echo '<html><head><title>Certificate Verification Failed</title></head>';
		echo '<body><h1>Certificate Verification Failed</h1>';
		echo '<p>' . esc_html( $error->get_error_message() ) . '</p>';
		echo '</body></html>';
	}

	/**
	 * Display verification success page.
	 *
	 * @since 1.0.0
	 *
	 * @param array $result Verification result.
	 *
	 * @return void
	 */
	private function display_verification_success( $result ) {
		$certificate = $result['certificate'];
		echo '<!DOCTYPE html>';
		echo '<html><head><title>Certificate Verified</title></head>';
		echo '<body>';
		echo '<h1>Certificate Successfully Verified</h1>';
		echo '<div>';
		echo '<p><strong>Recipient:</strong> ' . esc_html( $certificate['recipient_name'] ) . '</p>';
		echo '<p><strong>Course:</strong> ' . esc_html( $certificate['course_title'] ) . '</p>';
		echo '<p><strong>Issued:</strong> ' . esc_html( $certificate['issued_date'] ) . '</p>';
		echo '<p><strong>Issuer:</strong> ' . esc_html( $certificate['issuer'] ) . '</p>';
		echo '<p><strong>Verified:</strong> ' . esc_html( wp_date( 'F j, Y g:i A' ) ) . '</p>';
		echo '</div>';
		echo '</body></html>';
	}
}
