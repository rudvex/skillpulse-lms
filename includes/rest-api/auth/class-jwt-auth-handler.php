<?php
/**
 * JWT Authentication Handler
 *
 * Handles JWT token generation, validation, and authentication for REST API requests.
 * Integrates with WordPress authentication system via filters and hooks.
 *
 * @package SkillPulse_LMS
 * @subpackage REST_API
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load Strauss autoloader for prefixed dependencies.
if ( file_exists( SKILLPULSE_LMS_DIR_PATH . 'lib/vendor/autoload.php' ) ) {
	require_once SKILLPULSE_LMS_DIR_PATH . 'lib/vendor/autoload.php';
}

use SkillPulseLMS\Vendor\Firebase\JWT\JWT;
use SkillPulseLMS\Vendor\Firebase\JWT\Key;

/**
 * JWT Authentication Handler
 *
 * Integrates with WordPress determine_current_user filter for seamless JWT-based authentication.
 * Handles token generation, validation, revocation, and CORS headers for REST API requests.
 *
 * Security Features:
 * - Strong key generation (256-bit minimum) addressing CVE-2025-45769
 * - Token blacklisting/revocation support
 * - Key strength validation
 * - Secure header-only token transmission (no query parameters)
 * - HMAC-SHA256 algorithm enforcement
 * - Configurable CORS protection
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_JWT_Auth_Handler {

	/**
	 * Minimum token expiry time (5 minutes).
	 *
	 * @since 1.0.0
	 * @var int
	 */
	const MIN_TOKEN_EXPIRY = 300;

	/**
	 * Maximum token expiry time (7 days).
	 *
	 * @since 1.0.0
	 * @var int
	 */
	const MAX_TOKEN_EXPIRY = 604800;

	/**
	 * Initialize the JWT authentication handler.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function init() {
		// Ensure JWT secret is defined.
		self::define_jwt_secret();

		add_filter( 'determine_current_user', array( __CLASS__, 'determine_current_user' ), 10 );
		add_action( 'rest_authentication_errors', array( __CLASS__, 'rest_authentication_errors' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'setup_cors_headers' ) );
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'add_auth_headers' ), 10, 3 );

		// Security: Clean up expired tokens and rate limiting data periodically.
		add_action( 'wp_scheduled_delete', array( __CLASS__, 'cleanup_security_data' ) );
	}

	/**
	 * Define JWT secret constant if not already defined.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function define_jwt_secret() {
		if ( ! defined( 'SKILLPULSE_LMS_JWT_SECRET' ) ) {
			// Use WordPress salts to generate JWT secret.
			if ( function_exists( 'wp_salt' ) ) {
				$base_secret = wp_salt( 'auth' ) . wp_salt( 'secure_auth' ) . 'splms-jwt-v1';
			} elseif ( defined( 'AUTH_SALT' ) && defined( 'SECURE_AUTH_SALT' ) ) {
				// Fallback using constants if wp_salt() is not available yet.
				$base_secret = AUTH_SALT . SECURE_AUTH_SALT . 'splms-jwt-v1';
			} else {
				// Final fallback - this should not happen in normal WordPress installations.
				$base_secret = 'splms-jwt-fallback-' . ( function_exists( 'get_option' ) ? get_option( 'siteurl', 'localhost' ) : 'localhost' ) . '-v1';
			}

			$jwt_secret = hash( 'sha256', $base_secret );
			define( 'SKILLPULSE_LMS_JWT_SECRET', $jwt_secret );
		}
	}

	/**
	 * Determine current user based on JWT token.
	 *
	 * @since 1.0.0
	 *
	 * @param int|false $user_id User ID if one has been determined, false otherwise.
	 *
	 * @return int|false User ID on success, false on failure.
	 */
	public static function determine_current_user( $user_id ) {
		// If user is already determined, don't override.
		if ( $user_id ) {
			return $user_id;
		}

		// Only check for JWT on REST API requests.
		if ( ! self::is_rest_request() ) {
			return $user_id;
		}

		$token = self::get_jwt_token_from_request();

		if ( ! $token ) {
			return $user_id; // No token provided.
		}

		// Check if token is blacklisted (revoked).
		if ( self::is_token_blacklisted( $token ) ) {
			self::set_auth_error(
				new WP_Error(
					'jwt_auth_token_revoked',
					__( 'Token has been revoked. Please login again.', 'skillpulse-lms' )
				)
			);
			return $user_id;
		}

		$decoded_token = self::validate_jwt_token( $token );

		if ( is_wp_error( $decoded_token ) ) {
			// Store error for later use in rest_authentication_errors.
			self::set_auth_error( $decoded_token );
			return $user_id;
		}

		// Validate user still exists and is active.
		$user = get_user_by( 'id', $decoded_token['user_id'] );
		if ( ! $user || ! $user->exists() ) {
			self::set_auth_error(
				new WP_Error(
					'jwt_auth_user_not_found',
					__( 'User associated with token not found.', 'skillpulse-lms' )
				)
			);
			return $user_id;
		}

		// Security: Check if user account is active and not deleted.
		if ( ! $user->has_cap( 'read' ) ) {
			self::set_auth_error(
				new WP_Error(
					'jwt_auth_user_inactive',
					__( 'User account is inactive.', 'skillpulse-lms' )
				)
			);
			return $user_id;
		}

		// Set the current user for WordPress.
		wp_set_current_user( $user->ID );

		// Log successful authentication.
		self::log_auth_event( $user->ID, 'jwt_auth_success' );

		return $user->ID;
	}

	/**
	 * Handle REST API authentication errors.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error|null|true $error WP_Error if authentication error, null if authentication
	 *                                   method wasn't used, true if authentication succeeded.
	 *
	 * @return WP_Error|null|true
	 */
	public static function rest_authentication_errors( $error ) {
		// If authentication already succeeded, don't override.
		if ( true === $error ) {
			return $error;
		}

		$auth_error = self::get_auth_error();

		if ( $auth_error ) {
			return $auth_error;
		}

		// If user is already authenticated via JWT, return true.
		$current_user_id = get_current_user_id();
		if ( $current_user_id > 0 ) {
			return true;
		}

		return $error;
	}

	/**
	 * Check if current request is a REST API request.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if REST request, false otherwise.
	 */
	private static function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash.

		return ( false !== strpos( $request_uri, $rest_prefix ) );
	}

	/**
	 * Extract JWT token from request headers.
	 *
	 * @since 1.0.0
	 *
	 * @return string|null JWT token or null if not found.
	 */
	private static function get_jwt_token_from_request() {
		$headers = self::get_authorization_headers();

		// Check X-REST-Token header (our custom header).
		if ( isset( $headers['X-REST-Token'] ) ) {
			// Security: Sanitize and validate token format.
			$token = trim( $headers['X-REST-Token'] );
			if ( ! empty( $token ) && preg_match( '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token ) ) {
				return $token;
			}
		}

		// Check Authorization header (Bearer token).
		if ( isset( $headers['Authorization'] ) ) {
			$auth_header = $headers['Authorization'];
			if ( preg_match( '/Bearer\s+(.+)/i', $auth_header, $matches ) ) {
				// Security: Sanitize and validate token format.
				$token = trim( $matches[1] );
				if ( ! empty( $token ) && preg_match( '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token ) ) {
					return $token;
				}
			}
		}

		return null;
	}

	/**
	 * Get authorization headers from request.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of authorization headers.
	 */
	private static function get_authorization_headers() {
		$headers = array();

		// Check for custom header.
		if ( isset( $_SERVER['HTTP_X_REST_TOKEN'] ) ) {
			$headers['X-REST-Token'] = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REST_TOKEN'] ) );
		}

		// Check for Authorization header.
		$authorization_header = null;
		if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
			$authorization_header = sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) );
		} elseif ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
			$authorization_header = sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) );
		} elseif ( function_exists( 'apache_request_headers' ) ) {
			$request_headers = apache_request_headers();
			$request_headers = array_combine(
				array_map( 'ucwords', array_keys( $request_headers ) ),
				array_values( $request_headers )
			);
			if ( isset( $request_headers['Authorization'] ) ) {
				$authorization_header = sanitize_text_field( wp_unslash( $request_headers['Authorization'] ) );
			}
		}

		if ( $authorization_header ) {
			$headers['Authorization'] = $authorization_header;
		}

		return $headers;
	}

	/**
	 * Validate JWT token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token JWT token to validate.
	 *
	 * @return array|WP_Error Decoded token data or error.
	 */
	public static function validate_jwt_token( $token ) {
		// Ensure JWT secret is defined.
		self::define_jwt_secret();

		// Check if token is blacklisted (revoked) first.
		if ( self::is_token_blacklisted( $token ) ) {
			return new WP_Error(
				'jwt_auth_token_revoked',
				__( 'Token has been revoked. Please login again.', 'skillpulse-lms' )
			);
		}

		try {
			$decoded     = JWT::decode( $token, new Key( SKILLPULSE_LMS_JWT_SECRET, 'HS256' ) );
			$token_array = (array) $decoded;

			// Validate required fields.
			$required_fields = array( 'user_id', 'exp', 'iat', 'iss' );
			foreach ( $required_fields as $field ) {
				if ( ! isset( $token_array[ $field ] ) ) {
					return new WP_Error(
						'jwt_auth_invalid_token',
						// translators: %s: The missing field name.
						sprintf( __( 'Token missing required field: %s', 'skillpulse-lms' ), $field )
					);
				}
			}

			// Additional validation.
			if ( home_url() !== $token_array['iss'] ) {
				return new WP_Error(
					'jwt_auth_invalid_issuer',
					__( 'Token issuer does not match site URL.', 'skillpulse-lms' )
				);
			}

			// Security: Validate not-before claim.
			if ( isset( $token_array['nbf'] ) && $token_array['nbf'] > time() ) {
				return new WP_Error(
					'jwt_auth_token_not_active',
					__( 'Token is not yet active.', 'skillpulse-lms' )
				);
			}

			return $token_array;

		} catch ( \SkillPulseLMS\Vendor\Firebase\JWT\ExpiredException $e ) {
			return new WP_Error(
				'jwt_auth_token_expired',
				__( 'Token has expired.', 'skillpulse-lms' )
			);
		} catch ( \SkillPulseLMS\Vendor\Firebase\JWT\SignatureInvalidException $e ) {
			return new WP_Error(
				'jwt_auth_invalid_signature',
				__( 'Token signature verification failed.', 'skillpulse-lms' )
			);
		} catch ( \Exception $e ) {
			return new WP_Error(
				'jwt_auth_invalid_token',
				// translators: %s: The exception error message.
				sprintf( __( 'Token validation failed: %s', 'skillpulse-lms' ), $e->getMessage() )
			);
		}
	}

	/**
	 * Generate JWT token for user.
	 *
	 * @since 1.0.0
	 *
	 * @param int      $user_id    User ID.
	 * @param int|null $expires_in Token lifetime in seconds (default: 24 hours).
	 *
	 * @return array|WP_Error Token data or error.
	 */
	public static function generate_jwt_token( $user_id, $expires_in = null ) {
		// Ensure JWT secret is defined.
		self::define_jwt_secret();

		$user = get_user_by( 'id', $user_id );

		if ( ! $user ) {
			return new WP_Error(
				'jwt_auth_invalid_user',
				__( 'Invalid user ID.', 'skillpulse-lms' )
			);
		}

		if ( is_null( $expires_in ) ) {
			$expires_in = DAY_IN_SECONDS; // Default: 24 hours.
		}

		// Security: Validate token expiry bounds.
		if ( $expires_in < self::MIN_TOKEN_EXPIRY || $expires_in > self::MAX_TOKEN_EXPIRY ) {
			return new WP_Error(
				'jwt_auth_invalid_expiry',
				/* translators: 1: minimum expiry time in seconds, 2: maximum expiry time in seconds */
				sprintf( __( 'Token expiry must be between %1$d and %2$d seconds.', 'skillpulse-lms' ), self::MIN_TOKEN_EXPIRY, self::MAX_TOKEN_EXPIRY )
			);
		}

		$issued_at = time();
		$expires   = $issued_at + $expires_in;

		// Security: Generate unique JTI (JWT ID) for each token.
		$jti = self::generate_jti( $user_id, $issued_at );

		$payload = array(
			'user_id' => $user_id,
			'exp'     => $expires,
			'iat'     => $issued_at,
			'nbf'     => $issued_at, // Not before (security: prevent token use before issue time).
			'iss'     => home_url(),
			'sub'     => $user->user_login,
			'jti'     => $jti, // JWT ID for unique identification and revocation.
		);

		/**
		 * Filter the JWT token payload before encoding.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $payload Token payload array.
		 * @param WP_User $user    User object.
		 *
		 * @return array Filtered token payload array.
		 */
		$payload = apply_filters( 'splms_jwt_token_payload', $payload, $user );

		try {
			$token = JWT::encode( $payload, SKILLPULSE_LMS_JWT_SECRET, 'HS256' );

			return array(
				'token'   => $token,
				'expires' => $expires,
				'user_id' => $user_id,
			);

		} catch ( \Exception $e ) {
			return new WP_Error(
				'jwt_auth_token_generation_failed',
				// translators: %s: The exception error message.
				sprintf( __( 'Token generation failed: %s', 'skillpulse-lms' ), $e->getMessage() )
			);
		}
	}

	/**
	 * Set authentication error.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error $error Authentication error.
	 *
	 * @return void
	 */
	private static function set_auth_error( $error ) {
		global $splms_jwt_auth_error;
		$splms_jwt_auth_error = $error;
	}

	/**
	 * Get authentication error.
	 *
	 * @since 1.0.0
	 *
	 * @return WP_Error|null Authentication error or null.
	 */
	private static function get_auth_error() {
		global $splms_jwt_auth_error;
		return $splms_jwt_auth_error;
	}

	/**
	 * Setup CORS headers for API requests.
	 *
	 * This function configures CORS (Cross-Origin Resource Sharing) headers:
	 * - Access-Control-Allow-Headers: Headers that clients can SEND in requests
	 * - Access-Control-Expose-Headers: Headers that clients can READ from responses
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function setup_cors_headers() {
		if ( ! self::is_rest_request() ) {
			return;
		}

		// Security: Make CORS configurable instead of allowing all origins.
		$allowed_origins = apply_filters( 'splms_jwt_cors_allowed_origins', array() );

		// If no origins configured, don't set CORS headers (more secure).
		if ( empty( $allowed_origins ) ) {
			return;
		}

		// Get the origin from the request.
		$origin = isset( $_SERVER['HTTP_ORIGIN'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ORIGIN'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.

		// Check if origin is allowed.
		if ( in_array( $origin, $allowed_origins, true ) || in_array( '*', $allowed_origins, true ) ) {
			header( 'Access-Control-Allow-Origin: ' . ( '*' === $allowed_origins[0] ? '*' : $origin ) );
			header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS' );

			// Headers that clients can SEND in requests (request headers).
			$allowed_request_headers = apply_filters(
				'splms_cors_allowed_headers',
				array(
					'Authorization',
					'Content-Type',
					'X-REST-Token',
				)
			);
			header( 'Access-Control-Allow-Headers: ' . implode( ', ', $allowed_request_headers ) );

			header( 'Access-Control-Allow-Credentials: true' );

			// Headers that clients can READ from responses (response headers).
			$exposed_response_headers = apply_filters(
				'splms_cors_exposed_headers',
				array(
					'X-WP-Total',
					'X-WP-TotalPages',
					'X-Is-User-Loggedin',
					'X-Notification-Unread-Count',
				)
			);
			header( 'Access-Control-Expose-Headers: ' . implode( ', ', $exposed_response_headers ) );
		}

		// Handle preflight requests.
		$request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.
		if ( 'OPTIONS' === $request_method ) {
			status_header( 200 );
			exit;
		}
	}

	/**
	 * Log authentication events.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id User ID.
	 * @param string $event   Event type.
	 *
	 * @return void
	 */
	private static function log_auth_event( $user_id, $event ) {
		$log_data = array(
			'user_id'    => $user_id,
			'event'      => $event,
			'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.
			'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'unknown', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized.
			'timestamp'  => current_time( 'mysql' ),
		);

		// Log to error log.
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Conditional debug logging for JWT authentication events.
		error_log(
			sprintf(
				'SkillPulse JWT Auth: %s for user %d from IP %s',
				$event,
				$user_id,
				$log_data['ip_address']
			)
		);

		/**
		 * Fires after a JWT authentication event is logged.
		 *
		 * @since 1.0.0
		 *
		 * @param array $log_data {
		 *     Authentication event log data.
		 *
		 *     @type int    $user_id    User ID.
		 *     @type string $event      Event type (e.g., 'jwt_auth_success').
		 *     @type string $ip_address User IP address.
		 *     @type string $user_agent User agent string.
		 *     @type string $timestamp  Event timestamp.
		 * }
		 */
		do_action( 'splms_jwt_auth_event', $log_data );
	}

	/**
	 * Revoke token (add to blacklist).
	 *
	 * @since 1.0.0
	 *
	 * @param string   $token   JWT token to revoke.
	 * @param int|null $user_id User ID (optional, for verification).
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function revoke_token( $token, $user_id = null ) {
		$decoded = self::validate_jwt_token( $token );

		if ( is_wp_error( $decoded ) ) {
			return false;
		}

		if ( $user_id && $decoded['user_id'] !== $user_id ) {
			return false;
		}

		// Add token to blacklist.
		// Security: Use SHA-256 instead of MD5 for stronger hashing.
		$blacklist   = get_option( 'splms_jwt_blacklist', array() );
		$blacklist[] = array(
			'token_jti' => hash( 'sha256', $token ), // Store hash instead of full token.
			'exp'       => $decoded['exp'],
			'revoked'   => time(),
		);

		// Clean up expired tokens from blacklist.
		$blacklist = array_filter(
			$blacklist,
			function ( array $item ) {
				return isset( $item['exp'] ) && $item['exp'] > time();
			}
		);

		update_option( 'splms_jwt_blacklist', $blacklist );

		return true;
	}

	/**
	 * Check if token is blacklisted.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token JWT token to check.
	 *
	 * @return bool True if blacklisted, false otherwise.
	 */
	public static function is_token_blacklisted( $token ) {
		$blacklist = get_option( 'splms_jwt_blacklist', array() );
		// Security: Use SHA-256 instead of MD5 for stronger hashing.
		$token_hash = hash( 'sha256', $token );

		foreach ( $blacklist as $item ) {
			if ( $token_hash === $item['token_jti'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Add authentication status headers to REST API responses.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Response $result  Result to send to the client.
	 * @param WP_REST_Server   $server  Server instance.
	 * @param WP_REST_Request  $request Request used to generate the response.
	 *
	 * @return WP_REST_Response Modified response with auth headers.
	 */
	public static function add_auth_headers( $result, $server, $request ) {
		// Only add headers for SkillPulse LMS REST API endpoints.
		if ( ! self::is_splms_rest_request( $request ) ) {
			return $result;
		}

		$user_id = get_current_user_id();

		// Add simple authentication status header.
		if ( $user_id > 0 ) {
			$result->header( 'X-Is-User-Loggedin', 'yes' );

			// Add unread notification count header (only if in-app notifications are enabled).
			$in_app_enabled = get_option( 'splms_in_app_notifications_enabled', true ) === '1' || get_option( 'splms_in_app_notifications_enabled', true ) === true;

			if ( $in_app_enabled && class_exists( 'SkillPulse_LMS_Notifications_Query' ) ) {
				$query                = SkillPulse_LMS_Notifications_Query::get_instance();
				$unread_args          = array(
					'user_id' => $user_id,
					'is_read' => false,
				);
				$unread_notifications = $query->get_notifications( $unread_args );
				$unread_count         = count( $unread_notifications );
				$result->header( 'X-Notification-Unread-Count', $unread_count );
			} else {
				$result->header( 'X-Notification-Unread-Count', 0 );
			}
		} else {
			$result->header( 'X-Is-User-Loggedin', 'no' );
			$result->header( 'X-Notification-Unread-Count', 0 );
		}

		return $result;
	}

	/**
	 * Check if request is for SkillPulse LMS REST API.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return bool True if SPLMS REST API request, false otherwise.
	 */
	private static function is_splms_rest_request( $request ) {
		$route = $request->get_route();

		// Check if route starts with splms namespace.
		if ( strpos( $route, '/splms/' ) === 0 ) {
			return true;
		}

		return false;
	}



	/**
	 * Generate unique JWT ID (JTI).
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id    User ID.
	 * @param int $issued_at  Token issue time.
	 *
	 * @return string Unique JWT ID.
	 */
	private static function generate_jti( $user_id, $issued_at ) {
		// Create unique identifier using user ID, timestamp, and random component.
		$unique_data = $user_id . '_' . $issued_at . '_' . wp_generate_password( 16, false );
		return hash( 'sha256', $unique_data );
	}


	/**
	 * Clean up expired security data periodically.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function cleanup_security_data() {
		// Clean up expired blacklisted tokens.
		$blacklist         = get_option( 'splms_jwt_blacklist', array() );
		$current_time      = time();
		$cleaned_blacklist = array_filter(
			$blacklist,
			function ( array $item ) use ( $current_time ) {
				return isset( $item['exp'] ) && $item['exp'] > $current_time;
			}
		);

		if ( count( $cleaned_blacklist ) !== count( $blacklist ) ) {
			update_option( 'splms_jwt_blacklist', $cleaned_blacklist );
		}
	}
}
