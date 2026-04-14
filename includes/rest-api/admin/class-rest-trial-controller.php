<?php
/**
 * Trial REST API Controller
 *
 * Handles REST API endpoints for trial system management and monitoring.
 * Provides endpoints for trial status, usage statistics, and conversion tracking.
 *
 * @package SkillPulse_LMS
 * @since [SPLMS_VERSION]
 *
 * @api
 * Available endpoints:
 * GET    /skillpulse-lms/v1/trial/status - Get trial status and usage statistics
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trial REST API Controller class.
 *
 * @since [SPLMS_VERSION]
 */
class SkillPulse_LMS_Rest_Trial_Controller extends WP_REST_Controller {

	/**
	 * Class instance.
	 *
	 * @since [SPLMS_VERSION]
	 * @var SkillPulse_LMS_Rest_Trial_Controller|null
	 */
	private static $instance = null;

	/**
	 * Constructor.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = 'skillpulse-lms/v1';
		$this->rest_base = 'trial';
	}

	/**
	 * Get class instance.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @return SkillPulse_LMS_Rest_Trial_Controller
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register the trial routes.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @return void
	 */
	public function register_routes() {
		// Trial status endpoint.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/status',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_trial_status' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
			)
		);
	}

	/**
	 * Check if a given request has access to trial data.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'splms_rest_forbidden',
				__( 'Sorry, you are not allowed to access trial information.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get trial status and usage statistics.
	 *
	 * Retrieves comprehensive trial information including status, days remaining,
	 * usage statistics, limits, and expiration details.
	 *
	 * @since [SPLMS_VERSION]
	 *
	 * @api {get} /skillpulse-lms/v1/trial/status Get Trial Status
	 * @apiName GetTrialStatus
	 * @apiGroup Trial
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve current trial status and comprehensive usage statistics.
	 * This includes trial active status, days remaining, feature usage, and conversion data.
	 * Requires 'manage_options' capability.
	 *
	 *     HTTP/1.1 200 OK
	 *     {
	 *         "success": true,
	 *         "trial_status": "active",
	 *         "days_remaining": 12,
	 *         "expires_at": "2024-01-15T10:30:00Z",
	 *         "usage_stats": {
	 *             "courses": {
	 *                 "used": 2,
	 *                 "limit": 3,
	 *                 "remaining": 1
	 *             },
	 *             "enrollments": {
	 *                 "used": 15,
	 *                 "limit": 25,
	 *                 "remaining": 10
	 *             },
	 *             "api_calls": {
	 *                 "used": 1250,
	 *                 "limit": 5000,
	 *                 "remaining": 3750
	 *             }
	 *         },
	 *         "feature_flags": {
	 *             "advanced_analytics": false,
	 *             "white_label": false,
	 *             "priority_support": false
	 *         },
	 *         "conversion": {
	 *             "upgrade_url": "/wp-admin/admin.php?page=splms-license",
	 *             "contact_url": "https://skillpulselms.com/contact/"
	 *         }
	 *     }
	 * @apiError (Error 403) Forbidden User does not have 'manage_options' capability.
	 * @apiError (Error 500) TrialSystemUnavailable Trial system is not available.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 403 Forbidden
	 *     {
	 *         "code": "rest_forbidden",
	 *         "message": "Sorry, you are not allowed to access trial information.",
	 *         "data": {
	 *             "status": 403
	 *         }
	 *     }
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_trial_status( $request ) {
		// Check if trial system is available.
		if ( ! class_exists( 'SkillPulse_LMS_Trial_System' ) ) {
			return new WP_Error(
				'trial_system_unavailable',
				__( 'Trial system is not available.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		$trial_system = SkillPulse_LMS_Trial_System::get_instance();
		$trial_manager = $trial_system->get_trial_manager();

		if ( ! $trial_manager ) {
			return new WP_Error(
				'trial_manager_unavailable',
				__( 'Trial manager is not available.', 'skillpulse-lms' ),
				array( 'status' => 500 )
			);
		}

		// Get trial status.
		$is_trial_active = $trial_manager->is_trial_active();
		$trial_data = $trial_manager->get_trial_data();

		// Calculate days remaining.
		$days_remaining = 0;
		$expires_at = null;

		if ( $is_trial_active && isset( $trial_data['expires_at'] ) ) {
			$expires_timestamp = is_numeric( $trial_data['expires_at'] ) ? (int) $trial_data['expires_at'] : strtotime( $trial_data['expires_at'] );
			$current_timestamp = time();
			$seconds_remaining = $expires_timestamp - $current_timestamp;
			$days_remaining = max( 0, ceil( $seconds_remaining / DAY_IN_SECONDS ) );
			$expires_at = gmdate( 'c', $expires_timestamp );
		}

		// Get usage statistics from trial system.
		$usage_stats = $trial_system->get_usage_stats();

		// Extract trial-specific usage data.
		$trial_usage_data = isset( $usage_stats['trial_data'] ) ? $usage_stats['trial_data'] : array();

		// Structure usage statistics for API response.
		$structured_usage_stats = array();

		if ( isset( $trial_usage_data['courses'] ) ) {
			$structured_usage_stats['courses'] = array(
				'used' => (int) $trial_usage_data['courses']['used'],
				'limit' => (int) $trial_usage_data['courses']['limit'],
				'remaining' => max( 0, (int) $trial_usage_data['courses']['limit'] - (int) $trial_usage_data['courses']['used'] ),
			);
		}

		if ( isset( $trial_usage_data['enrollments'] ) ) {
			$structured_usage_stats['enrollments'] = array(
				'used' => (int) $trial_usage_data['enrollments']['used'],
				'limit' => (int) $trial_usage_data['enrollments']['limit'],
				'remaining' => max( 0, (int) $trial_usage_data['enrollments']['limit'] - (int) $trial_usage_data['enrollments']['used'] ),
			);
		}

		if ( isset( $trial_usage_data['api_calls'] ) ) {
			$structured_usage_stats['api_calls'] = array(
				'used' => (int) $trial_usage_data['api_calls']['used'],
				'limit' => (int) $trial_usage_data['api_calls']['limit'],
				'remaining' => max( 0, (int) $trial_usage_data['api_calls']['limit'] - (int) $trial_usage_data['api_calls']['used'] ),
			);
		}

		if ( isset( $trial_usage_data['emails'] ) ) {
			$structured_usage_stats['emails'] = array(
				'used' => (int) $trial_usage_data['emails']['used'],
				'limit' => (int) $trial_usage_data['emails']['limit'],
				'remaining' => max( 0, (int) $trial_usage_data['emails']['limit'] - (int) $trial_usage_data['emails']['used'] ),
			);
		}

		if ( isset( $trial_usage_data['certificates'] ) ) {
			$structured_usage_stats['certificates'] = array(
				'used' => (int) $trial_usage_data['certificates']['used'],
				'limit' => (int) $trial_usage_data['certificates']['limit'],
				'remaining' => max( 0, (int) $trial_usage_data['certificates']['limit'] - (int) $trial_usage_data['certificates']['used'] ),
			);
		}

		// Get feature flags.
		$feature_flags_instance = $trial_system->get_feature_flags();
		$feature_flags = array(
			'advanced_analytics' => false,
			'white_label' => false,
			'priority_support' => false,
			'unlimited_courses' => false,
			'unlimited_enrollments' => false,
		);

		if ( $feature_flags_instance && method_exists( $feature_flags_instance, 'is_feature_enabled' ) ) {
			$feature_flags['advanced_analytics'] = $feature_flags_instance->is_feature_enabled( 'advanced_analytics' );
			$feature_flags['white_label'] = $feature_flags_instance->is_feature_enabled( 'white_label' );
			$feature_flags['priority_support'] = $feature_flags_instance->is_feature_enabled( 'priority_support' );
			$feature_flags['unlimited_courses'] = $feature_flags_instance->is_feature_enabled( 'unlimited_courses' );
			$feature_flags['unlimited_enrollments'] = $feature_flags_instance->is_feature_enabled( 'unlimited_enrollments' );
		}

		// Prepare response data.
		$response_data = array(
			'success' => true,
			'trial_status' => $is_trial_active ? 'active' : 'expired',
			'days_remaining' => $days_remaining,
			'expires_at' => $expires_at,
			'usage_stats' => $structured_usage_stats,
			'feature_flags' => $feature_flags,
			'conversion' => array(
				'upgrade_url' => admin_url( 'admin.php?page=splms-license' ),
				'contact_url' => 'https://skillpulselms.com/contact/',
			),
			'system_status' => isset( $usage_stats['system_status'] ) ? $usage_stats['system_status'] : array(),
		);

		return rest_ensure_response( $response_data );
	}
}