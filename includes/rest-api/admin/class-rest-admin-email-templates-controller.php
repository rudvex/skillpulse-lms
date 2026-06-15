<?php
/**
 * REST API Email Templates Controller
 *
 * Handles REST API endpoints for email template management and testing.
 * Provides endpoints for getting/saving templates, testing templates, SMTP testing, and email queue management.
 *
 * @package SPLMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/email-templates - Get all email templates
 * POST   /splms/v1/email-templates - Save email template
 * POST   /splms/v1/email-templates/test - Test email template
 * POST   /splms/v1/email-templates/test-smtp - Test SMTP connection
 * GET    /splms/v1/email-templates/queue/stats - Get email queue statistics
 * POST   /splms/v1/email-templates/queue/retry - Retry failed emails
 * POST   /splms/v1/email-templates/queue/clear - Clear email queue
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Email Templates Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Admin_Email_Templates_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'email-templates';
	}

	/**
	 * Register the routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_template' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/test',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'test_template' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/test-smtp',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'test_smtp_connection' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/stats',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_queue_stats' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/retry',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'retry_failed_emails' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/clear',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'clear_email_queue' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/process',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'process_email_queue' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/queue/test',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'test_queue_system' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<template_key>[a-zA-Z0-9_-]+)/reset',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'reset_template' ),
					'permission_callback' => array( $this, 'check_admin_permissions' ),
				),
			)
		);
	}

	/**
	 * Check admin permissions.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if user has manage_options capability, false otherwise.
	 */
	public function check_admin_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get all email templates.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function get_templates( $request ) {
		$email_templates = SPLMS_Email_Templates::get_instance();
		$templates       = $email_templates->get_all_templates();

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $templates,
			)
		);
	}

	/**
	 * Save email template.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function save_template( $request ) {
		$template_data = $request->get_json_params();

		if ( ! $template_data ) {
			$template_data = $request->get_params();
		}

		$email_templates = SPLMS_Email_Templates::get_instance();
		$result          = $email_templates->save_template( $template_data );

		if ( false === $result ) {
			return new WP_Error( 'save_failed', __( 'Failed to save template', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get the template key.
		$template_key = sanitize_text_field( $template_data['template_key'] );

		// Get the original template data from the registered templates.
		$all_templates     = $email_templates->get_all_templates();
		$original_template = $all_templates[ $template_key ] ?? array();

		// Get the saved data from the database.
		$option_name = "splms_email_template_{$template_key}";
		$saved_data  = get_option( $option_name, array() );

		// Merge original template with saved data.
		$updated_template                 = array_merge( $original_template, $saved_data );
		$updated_template['template_key'] = $template_key;

		// Ensure is_active is properly set.
		if ( isset( $template_data['is_active'] ) ) {
			$updated_template['is_active'] = (bool) $template_data['is_active'];
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $updated_template,
			)
		);
	}

	/**
	 * Test email template.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function test_template( $request ) {
		$template_data = $request->get_params();

		$email_templates = SPLMS_Email_Templates::get_instance();
		$result          = $email_templates->test_template( $template_data );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'test_failed', $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Test email sent successfully', 'skillpulse-lms' ),
			)
		);
	}

	/**
	 * Reset email template.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function reset_template( $request ) {
		$template_key = $request->get_param( 'template_key' );

		$email_templates = SPLMS_Email_Templates::get_instance();
		$result          = $email_templates->reset_template( $template_key );

		if ( false === $result ) {
			return new WP_Error( 'reset_failed', __( 'Failed to reset template', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get the original template data from the registered templates.
		$all_templates                     = $email_templates->get_all_templates();
		$original_template                 = $all_templates[ $template_key ] ?? array();
		$original_template['template_key'] = $template_key;

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $original_template,
			)
		);
	}

	/**
	 * Test SMTP connection.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function test_smtp_connection( $request ) {
		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to test SMTP connections.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$data = $request->get_json_params();

		if ( ! isset( $data['use_smtp'] ) || ! $data['use_smtp'] ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'SMTP is not enabled.', 'skillpulse-lms' ),
				),
				400
			);
		}

		// Validate required SMTP fields.
		$required_fields = array( 'smtp_host', 'smtp_username', 'smtp_password' );
		foreach ( $required_fields as $field ) {
			if ( ! isset( $data[ $field ] ) || empty( $data[ $field ] ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						// translators: %s: The SMTP field name.
						'message' => sprintf( __( 'SMTP %s is required.', 'skillpulse-lms' ), str_replace( 'smtp_', '', $field ) ),
					),
					400
				);
			}
		}

		// Test SMTP connection.
		$email_sender = SPLMS_Email_Sender::get_instance();
		$result       = $email_sender->test_smtp_connection();

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'SMTP connection test successful!', 'skillpulse-lms' ),
			),
			200
		);
	}

	/**
	 * Get email queue statistics.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function get_queue_stats( $request ) {
		$email_sender = SPLMS_Email_Sender::get_instance();
		$stats        = $email_sender->get_queue_stats();

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $stats,
			),
			200
		);
	}

	/**
	 * Retry failed emails.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function retry_failed_emails( $request ) {
		$data        = $request->get_json_params();
		$max_retries = isset( $data['max_retries'] ) ? intval( $data['max_retries'] ) : 3;

		$email_sender = SPLMS_Email_Sender::get_instance();
		$result       = $email_sender->retry_failed_emails( $max_retries );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $result,
				// translators: %1$d: Number of emails retried, %2$d: Number of emails permanently failed.
				'message' => sprintf( __( 'Retried %1$d emails, %2$d permanently failed.', 'skillpulse-lms' ), $result['retried'], $result['permanently_failed'] ),
			),
			200
		);
	}

	/**
	 * Clear email queue.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function clear_email_queue( $request ) {
		$data   = $request->get_json_params();
		$status = isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'all';

		$email_sender = SPLMS_Email_Sender::get_instance();
		$result       = $email_sender->clear_email_queue( $status );

		if ( $result ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					// translators: %s: The queue status.
					'message' => sprintf( __( 'Email queue cleared (%s).', 'skillpulse-lms' ), $status ),
				),
				200
			);
		} else {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Failed to clear email queue.', 'skillpulse-lms' ),
				),
				400
			);
		}
	}

	/**
	 * Manually process email queue.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function process_email_queue( $request ) {
		$email_sender = SPLMS_Email_Sender::get_instance();
		$email_sender->process_email_queue();

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Email queue processing completed.', 'skillpulse-lms' ),
			),
			200
		);
	}

	/**
	 * Test the email queue system.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function test_queue_system( $request ) {
		$data       = $request->get_json_params();
		$test_email = isset( $data['test_email'] ) ? sanitize_email( $data['test_email'] ) : '';

		if ( empty( $test_email ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => __( 'Test email address is required.', 'skillpulse-lms' ),
				),
				400
			);
		}

		$email_sender = SPLMS_Email_Sender::get_instance();
		$result       = $email_sender->test_queue_system( $test_email );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Test email added to queue successfully.', 'skillpulse-lms' ),
			),
			200
		);
	}
}
