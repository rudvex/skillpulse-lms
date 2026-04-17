<?php
/**
 * REST API Authentication Controller
 *
 * Handles REST API endpoints for JWT-based authentication.
 * Provides endpoints for login, token validation, token refresh, and logout (token revocation).
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * POST   /splms/v1/auth - Authenticate user and get JWT token
 * GET    /splms/v1/auth/validate - Validate existing JWT token
 * POST   /splms/v1/auth/refresh - Refresh JWT token
 * POST   /splms/v1/auth/logout - Logout and revoke JWT token
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Authentication Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Rest_Auth_Controller extends WP_REST_Controller {

	/**
	 * Route base.
	 *
	 * @since 1.0.0
	 *
	 * @var string $rest_base
	 */
	protected $rest_base = 'auth';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
	}

	/**
	 * Register the routes for authentication.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Authentication endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'authenticate_user' ),
				'permission_callback' => '__return_true', // Public endpoint.
				'args'                => array(
					'username' => array(
						'description'       => __( 'Username or email address', 'skillpulse-lms' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_user',
					),
					'password' => array(
						'description' => __( 'Password', 'skillpulse-lms' ),
						'type'        => 'string',
						'required'    => true,
					),
				),
			)
		);

		// Token validation endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/validate',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'validate_token' ),
				'permission_callback' => array( $this, 'validate_token_permissions_check' ),
			)
		);

		// Token refresh endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/refresh',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'refresh_token' ),
				'permission_callback' => '__return_true', // Public endpoint.
			)
		);

		// Logout endpoint (revoke token).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/logout',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'logout_user' ),
				'permission_callback' => '__return_true', // Public endpoint.
			)
		);
	}

	/**
	 * Authenticate user and generate JWT token.
	 *
	 * Authenticates a user with username/email and password, then generates
	 * a JWT token for API access. Includes rate limiting and security checks.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/auth Authenticate User
	 * @apiName AuthenticateUser
	 * @apiGroup Authentication
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Authenticate a user and receive a JWT token for API access.
	 * This is a public endpoint but includes rate limiting and security checks.
	 *
	 * @apiParam {String} username Username or email address.
	 * @apiParam {String} password User password.
	 *
	 * @apiError (Error 401) invalid_credentials Invalid username or password.
	 * @apiError (Error 403) https_required HTTPS is required for authentication.
	 * @apiError (Error 403) user_inactive User account is inactive.
	 * @apiError (Error 429) rate_limit_exceeded Too many login attempts.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function authenticate_user( $request ) {
		// Security: Enforce HTTPS in production (always enforce, even in debug mode for security).
		if ( ! is_ssl() ) {
			// Allow non-HTTPS only if explicitly configured for development.
			$allow_http = apply_filters( 'splms_allow_http_auth', false );
			if ( ! $allow_http ) {
				return new WP_Error(
					'https_required',
					__( 'HTTPS is required for authentication.', 'skillpulse-lms' ),
					array( 'status' => 403 )
				);
			}
		}

		// Security: Rate limiting for login attempts.
		$ip_address = $this->get_client_ip();
		if ( $this->is_rate_limited( $ip_address, 'login' ) ) {
			return new WP_Error(
				'rate_limit_exceeded',
				__( 'Too many login attempts. Please try again later.', 'skillpulse-lms' ),
				array( 'status' => 429 )
			);
		}

		$username = $request->get_param( 'username' );
		$password = $request->get_param( 'password' );

		// Security: Validate input.
		if ( empty( $username ) || empty( $password ) ) {
			$this->record_failed_attempt( $ip_address, 'login' );
			return new WP_Error(
				'invalid_credentials',
				__( 'Invalid username or password.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Use WordPress's built-in authentication.
		$user = wp_authenticate( $username, $password );

		if ( is_wp_error( $user ) ) {
			$this->record_failed_attempt( $ip_address, 'login' );
			return new WP_Error(
				'invalid_credentials',
				__( 'Invalid username or password.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Check if user account is active.
		if ( ! $user || ! $user->exists() ) {
			$this->record_failed_attempt( $ip_address, 'login' );
			return new WP_Error(
				'user_not_found',
				__( 'User account not found.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		// Security: Check if user account is active and has read capability.
		if ( ! $user->has_cap( 'read' ) ) {
			$this->record_failed_attempt( $ip_address, 'login' );
			return new WP_Error(
				'user_inactive',
				__( 'User account is inactive.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		// Clear failed attempts on successful login.
		$this->clear_failed_attempts( $ip_address, 'login' );

		// Generate JWT token using the handler.
		$token_data = SkillPulse_LMS_JWT_Auth_Handler::generate_jwt_token( $user->ID );

		if ( is_wp_error( $token_data ) ) {
			return $token_data;
		}

		// Get user capabilities for frontend.
		$user_capabilities = array();
		foreach ( $user->get_role_caps() as $cap => $granted ) {
			if ( $granted ) {
				$user_capabilities[] = $cap;
			}
		}

		// Allow filtering of auth response.
		$auth_response = apply_filters(
			'splms_auth_response',
			array(
				'success' => true,
				'token'   => $token_data['token'],
				'expires' => $token_data['expires'],
				'user'    => array(
					'id'           => $user->ID,
					'username'     => $user->user_login,
					'email'        => $user->user_email,
					'display_name' => $user->display_name,
					'roles'        => $user->roles,
					'avatar_url'   => get_avatar_url( $user->ID ),
				),
				'message' => __( 'Authentication successful.', 'skillpulse-lms' ),
			),
			$user
		);

		return rest_ensure_response( $auth_response );
	}

	/**
	 * Validate an existing JWT token.
	 *
	 * Validates a JWT token and returns user information if the token is valid.
	 * Useful for checking token validity and getting user details.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/auth/validate Validate JWT Token
	 * @apiName ValidateToken
	 * @apiGroup Authentication
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Validate an existing JWT token and retrieve user information.
	 * Token should be provided in Authorization header as "Bearer {token}".
	 *
	 * @apiHeader {String} Authorization Bearer token (e.g., "Bearer YOUR_JWT_TOKEN").
	 *
	 * @apiError (Error 401) missing_token Authentication token is required.
	 * @apiError (Error 401) invalid_token Token is invalid or expired.
	 * @apiError (Error 401) invalid_user User associated with token not found.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function validate_token( $request ) {
		$token = $this->get_token_from_request( $request );

		if ( ! $token ) {
			return new WP_Error(
				'missing_token',
				__( 'Authentication token is required.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		$user_data = SkillPulse_LMS_JWT_Auth_Handler::validate_jwt_token( $token );

		if ( is_wp_error( $user_data ) ) {
			return $user_data;
		}

		$user = get_user_by( 'id', $user_data['user_id'] );

		if ( ! $user ) {
			return new WP_Error(
				'invalid_user',
				__( 'User associated with token not found.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		return rest_ensure_response(
			array(
				'valid'   => true,
				'user_id' => $user->ID,
				'expires' => $user_data['exp'],
				'user'    => array(
					'id'           => $user->ID,
					'username'     => $user->user_login,
					'display_name' => $user->display_name,
					'roles'        => $user->roles,
				),
			)
		);
	}

	/**
	 * Refresh an existing JWT token.
	 *
	 * Refreshes a JWT token that is close to expiry (within 1 hour).
	 * Returns a new token with extended expiration time.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/auth/refresh Refresh JWT Token
	 * @apiName RefreshToken
	 * @apiGroup Authentication
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Refresh a JWT token that is close to expiry.
	 * Token should be provided in Authorization header. Only tokens within 1 hour
	 * of expiry can be refreshed.
	 *
	 * @apiHeader {String} Authorization Bearer token (e.g., "Bearer YOUR_JWT_TOKEN").
	 *
	 * @apiError (Error 401) missing_token Authentication token is required.
	 * @apiError (Error 401) invalid_token Token is invalid or expired.
	 * @apiError (Error 400) token_not_ready_for_refresh Token is not close to expiry.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function refresh_token( $request ) {
		$token = $this->get_token_from_request( $request );

		if ( ! $token ) {
			return new WP_Error(
				'missing_token',
				__( 'Authentication token is required.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		$user_data = SkillPulse_LMS_JWT_Auth_Handler::validate_jwt_token( $token );

		if ( is_wp_error( $user_data ) ) {
			return $user_data;
		}

		// Check if token is close to expiry (within 1 hour).
		$time_until_expiry = $user_data['exp'] - time();
		if ( $time_until_expiry > 3600 ) {
			return new WP_Error(
				'token_not_ready_for_refresh',
				__( 'Token is not close to expiry. Refresh not needed.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		// Generate new token.
		$new_token_data = SkillPulse_LMS_JWT_Auth_Handler::generate_jwt_token( $user_data['user_id'] );

		if ( is_wp_error( $new_token_data ) ) {
			return $new_token_data;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'token'   => $new_token_data['token'],
				'expires' => $new_token_data['expires'],
				'message' => __( 'Token refreshed successfully.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Logout user (revoke token).
	 *
	 * Revokes a JWT token, effectively logging out the user from the API.
	 * The token will no longer be valid for authentication.
	 *
	 * @since 1.0.0
	 *
	 * @api {post} /splms/v1/auth/logout Logout User
	 * @apiName LogoutUser
	 * @apiGroup Authentication
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Revoke a JWT token to log out the user.
	 * Token should be provided in Authorization header.
	 *
	 * @apiHeader {String} Authorization Bearer token (e.g., "Bearer YOUR_JWT_TOKEN").
	 *
	 * @apiError (Error 401) missing_token Authentication token is required.
	 * @apiError (Error 400) logout_failed Failed to revoke token.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function logout_user( $request ) {
		$token = $this->get_token_from_request( $request );

		if ( ! $token ) {
			return new WP_Error(
				'missing_token',
				__( 'Authentication token is required.', 'skillpulse-lms' ),
				array( 'status' => 401 )
			);
		}

		$revoked = SkillPulse_LMS_JWT_Auth_Handler::revoke_token( $token );

		if ( ! $revoked ) {
			return new WP_Error(
				'logout_failed',
				__( 'Failed to revoke token.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Logout successful. Token has been revoked.', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Get token from request headers.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return string|null Token or null if not found.
	 */
	private function get_token_from_request( $request ) {
		// Check X-REST-Token header.
		$token = $request->get_header( 'X-REST-Token' );
		if ( $token ) {
			// Security: Validate token format.
			$token = trim( $token );
			if ( ! empty( $token ) && preg_match( '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token ) ) {
				return $token;
			}
		}

		// Check Authorization header.
		$auth_header = $request->get_header( 'Authorization' );
		if ( $auth_header && preg_match( '/Bearer\s+(.+)/i', $auth_header, $matches ) ) {
			// Security: Validate token format.
			$token = trim( $matches[1] );
			if ( ! empty( $token ) && preg_match( '/^[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+\.[A-Za-z0-9\-_]+$/', $token ) ) {
				return $token;
			}
		}

		return null;
	}

	/**
	 * Get client IP address.
	 *
	 * @since 1.0.0
	 *
	 * @return string Client IP address.
	 */
	private function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare.
			'HTTP_X_REAL_IP',        // Nginx proxy.
			'HTTP_X_FORWARDED_FOR',  // Proxy.
			'REMOTE_ADDR',           // Standard.
		);

		foreach ( $ip_keys as $key ) {
			if ( isset( $_SERVER[ $key ] ) && ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// Handle comma-separated IPs (from proxies).
				$ip = explode( ',', $ip );
				$ip = trim( $ip[0] );
				// Validate IP address.
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Check permissions for token validation endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if authorized, WP_Error otherwise.
	 */
	public function validate_token_permissions_check( $request ) {
		// Rate limit validation requests to prevent enumeration.
		$ip_address = $this->get_client_ip();
		if ( $this->is_rate_limited( $ip_address, 'token_validation' ) ) {
			return new WP_Error(
				'rate_limit_exceeded',
				__( 'Too many validation requests. Please try again later.', 'skillpulse-lms' ),
				array( 'status' => 429 )
			);
		}

		// Record validation attempt.
		$this->record_validation_attempt( $ip_address );

		// Allow public access but with rate limiting.
		return true;
	}

	/**
	 * Check if IP is rate limited.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip_address IP address.
	 * @param string $action     Action type (e.g., 'login', 'token_refresh', 'token_validation').
	 *
	 * @return bool True if rate limited, false otherwise.
	 */
	private function is_rate_limited( $ip_address, $action = 'login' ) {
		$transient_key = 'splms_rate_limit_' . md5( $ip_address . $action );
		$attempts      = get_transient( $transient_key );

		if ( false === $attempts ) {
			return false;
		}

		// Different limits for different actions.
		$max_attempts = apply_filters(
			'splms_jwt_max_attempts_' . $action,
			array(
				'login'            => 5,  // 5 login attempts per 15 minutes.
				'token_refresh'    => 10, // 10 refresh attempts per 15 minutes.
				'token_validation' => 20, // 20 validation attempts per 15 minutes.
			)[ $action ] ?? 5
		);

		return $attempts >= $max_attempts;
	}

	/**
	 * Record token validation attempt for rate limiting.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip_address IP address.
	 * @return void
	 */
	private function record_validation_attempt( $ip_address ) {
		$transient_key = 'splms_rate_limit_' . md5( $ip_address . 'token_validation' );
		$attempts      = get_transient( $transient_key );

		if ( false === $attempts ) {
			$attempts = 0;
		}

		++$attempts;
		// Store for 15 minutes.
		set_transient( $transient_key, $attempts, 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Record failed authentication attempt.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip_address IP address.
	 * @param string $action     Action type.
	 *
	 * @return void
	 */
	private function record_failed_attempt( $ip_address, $action = 'login' ) {
		$transient_key = 'splms_rate_limit_' . md5( $ip_address . $action );
		$attempts      = get_transient( $transient_key );

		if ( false === $attempts ) {
			$attempts = 0;
		}

		++$attempts;
		// Store for 15 minutes.
		set_transient( $transient_key, $attempts, 15 * MINUTE_IN_SECONDS );

		// Log suspicious activity if threshold exceeded.
		$max_attempts = apply_filters( 'splms_jwt_max_login_attempts', 5 );
		if ( $attempts >= $max_attempts ) {
			/**
			 * Fires when rate limit is exceeded for authentication attempts.
			 *
			 * @since 1.0.0
			 *
			 * @param string $ip_address IP address that exceeded rate limit.
			 * @param string $action     Action type.
			 * @param int    $attempts   Number of attempts.
			 */
			do_action( 'splms_auth_rate_limit_exceeded', $ip_address, $action, $attempts );
		}
	}

	/**
	 * Clear failed authentication attempts.
	 *
	 * @since 1.0.0
	 *
	 * @param string $ip_address IP address.
	 * @param string $action     Action type.
	 *
	 * @return void
	 */
	private function clear_failed_attempts( $ip_address, $action = 'login' ) {
		$transient_key = 'splms_rate_limit_' . md5( $ip_address . $action );
		delete_transient( $transient_key );
	}
}
