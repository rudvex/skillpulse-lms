<?php
/**
 * Email Module Class
 *
 * Centralized email management system.
 *
 * @package SkillPulse_LMS
 * @subpackage Email
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Email Module Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Email_Module {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Email_Module|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Email_Module The class instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_globals();
			self::$instance->load_classes();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup globals.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$files = array(
			'includes/modules/notifications/email/templates/class-email-templates',
			'includes/modules/notifications/email/senders/class-email-sender',
		);

		foreach ( $files as $file ) {
			if ( file_exists( SKILLPULSE_LMS_DIR_PATH . $file . '.php' ) ) {
				require SKILLPULSE_LMS_DIR_PATH . $file . '.php';
			}
		}
	}

	/**
	 * Load required classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function load_classes() {
		// Initialize email templates.
		SkillPulse_LMS_Email_Templates::get_instance();

		// Initialize email sender.
		SkillPulse_LMS_Email_Sender::get_instance();
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		// Hook into order completion to send email with invoice.
		add_action( 'splms_order_status_pending_to_completed', array( $this, 'send_order_completion_email' ), 10, 4 );

		// Hook into user signup.
		add_action( 'splms_signup_created', array( $this, 'send_activation_email' ), 10, 2 );
		add_action( 'splms_signup_activated', array( $this, 'send_welcome_email' ), 10, 2 );

		// Hook into course events (when implemented).
		add_action( 'splms_course_enrolled', array( $this, 'send_enrollment_email' ), 10, 2 );
		add_action( 'splms_course_completed', array( $this, 'send_completion_email' ), 10, 2 );
		add_action( 'splms_certificate_generated', array( $this, 'send_certificate_email' ), 10, 2 );

		// Hook into lesson and quiz events.
		add_action( 'splms_lesson_completed', array( $this, 'send_lesson_completion_email' ), 10, 2 );
		add_action( 'splms_quiz_completed', array( $this, 'send_quiz_completion_email' ), 10, 3 );
		add_action( 'splms_certificate_awarded', array( $this, 'send_certificate_awarded_email' ), 10, 3 );

		// Customize password reset email.
		add_filter( 'retrieve_password_notification_email', array( $this, 'customize_password_reset_email' ), 10, 4 );
	}

	/**
	 * Send activation email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $signup_id Signup ID.
	 * @return array|WP_Error Email send result.
	 */
	public function send_activation_email( $signup_id ) {
		$signup = SkillPulse_LMS_Signup::get_instance()->get_signup( $signup_id );

		if ( ! $signup ) {
			return new WP_Error( 'signup_not_found', __( 'Signup not found.', 'skillpulse-lms' ) );
		}

		$activation_link = $this->get_activation_link( $signup );
		$replacements    = array(
			'user_name'       => $signup->user_name,
			'user_login'      => $signup->user_login,
			'user_email'      => $signup->user_email,
			'activation_link' => $activation_link,
			'site_name'       => get_bloginfo( 'name' ),
			'site_url'        => home_url(),
		);

		$result = SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $signup->user_email, 'activation_email', $replacements );

		if ( is_wp_error( $result['email'] ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging for debugging.
				error_log( 'Error sending activation email: ' . $result['email']->get_error_message() );
			}
		}

		return $result;
	}

	/**
	 * Send welcome email.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $user_id User ID.
	 * @param object $signup  Signup object. // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by hook signature.
	 * @return void
	 */
	public function send_welcome_email( $user_id, $signup ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by hook signature.
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$replacements = array(
			'user_name'  => $user->display_name,
			'user_login' => $user->user_login,
			'user_email' => $user->user_email,
			'site_name'  => get_bloginfo( 'name' ),
			'site_url'   => home_url(),
			'login_url'  => wp_login_url(),
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'welcome_email', $replacements, $user_id );
	}

	/**
	 * Send course enrollment email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function send_enrollment_email( $user_id, $course_id ) {
		$user   = get_userdata( $user_id );
		$course = get_post( $course_id );

		if ( ! $user || ! $course ) {
			return;
		}

		$replacements = array(
			'user_name'    => $user->display_name,
			'course_title' => $course->post_title,
			'site_name'    => get_bloginfo( 'name' ),
			'login_url'    => wp_login_url(),
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'course_enrollment', $replacements, $user_id );
	}

	/**
	 * Send course completion email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function send_completion_email( $user_id, $course_id ) {
		$user   = get_userdata( $user_id );
		$course = get_post( $course_id );

		if ( ! $user || ! $course ) {
			return;
		}

		$replacements = array(
			'user_name'       => $user->display_name,
			'course_title'    => $course->post_title,
			'site_name'       => get_bloginfo( 'name' ),
			'certificate_url' => $this->get_certificate_url( $user_id, $course_id ),
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'course_completion', $replacements, $user_id );
	}

	/**
	 * Send certificate email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return void
	 */
	public function send_certificate_email( $user_id, $course_id ) {
		$user   = get_userdata( $user_id );
		$course = get_post( $course_id );

		if ( ! $user || ! $course ) {
			return;
		}

		$replacements = array(
			'user_name'       => $user->display_name,
			'course_title'    => $course->post_title,
			'certificate_url' => $this->get_certificate_url( $user_id, $course_id ),
			'site_name'       => get_bloginfo( 'name' ),
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'certificate_email', $replacements, $user_id );
	}

	/**
	 * Send lesson completion email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $lesson_id Lesson ID.
	 * @param int $user_id   User ID.
	 * @return void
	 */
	public function send_lesson_completion_email( $lesson_id, $user_id ) {
		$user   = get_userdata( $user_id );
		$lesson = get_post( $lesson_id );

		if ( ! $user || ! $lesson ) {
			return;
		}

		// Get course ID for this lesson.
		$course_id = splms_get_lesson_course( $lesson_id );
		if ( ! $course_id ) {
			return;
		}

		$course = get_post( $course_id );
		if ( ! $course ) {
			return;
		}

		// Calculate course progress.
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$progress_data    = $lessons_instance->calculate_course_progress( $user_id, $course_id );

		$replacements = array(
			'user_name'       => $user->display_name,
			'course_title'    => $course->post_title,
			'lesson_title'    => $lesson->post_title,
			'course_url'      => get_permalink( $course_id ),
			'course_progress' => round( $progress_data['percentage'], 0 ),
			'site_name'       => get_bloginfo( 'name' ),
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'lesson_completion', $replacements, $user_id );
	}

	/**
	 * Send quiz completion email.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id User ID.
	 * @param int   $quiz_id Quiz ID.
	 * @param float $score   Quiz score (percentage).
	 * @return void
	 */
	public function send_quiz_completion_email( $user_id, $quiz_id, $score ) {
		$user = get_userdata( $user_id );
		$quiz = get_post( $quiz_id );

		if ( ! $user || ! $quiz ) {
			return;
		}

		// Get course ID for this quiz.
		$course_id = splms_get_quiz_course( $quiz_id );
		$course    = $course_id ? get_post( $course_id ) : null;

		// Get quiz attempt data.
		$attempts_query = SkillPulse_LMS_Quiz_Attempts_Query::get_instance();
		$attempts       = $attempts_query->get_completed_attempts( $user_id, $quiz_id );
		$latest_attempt = ! empty( $attempts ) ? $attempts[0] : null;

		// Get quiz settings for passing grade.
		$quizzes_instance = SkillPulse_LMS_Quizzes::get_instance();
		$settings         = $quizzes_instance->get_quiz_settings( $quiz_id );
		$passing_grade    = isset( $settings['passing_grade'] ) ? floatval( $settings['passing_grade'] ) : 70.0;

		$score_percentage = round( floatval( $score ), 2 );
		$passed           = $score_percentage >= $passing_grade;

		// Determine correct answers and total questions.
		$correct_answers = 0;
		$total_questions = 0;
		if ( $latest_attempt ) {
			// Handle both JSON string and array formats.
			if ( is_string( $latest_attempt->answers ) ) {
				$answers = json_decode( $latest_attempt->answers, true );
			} else {
				$answers = $latest_attempt->answers;
			}

			if ( is_array( $answers ) ) {
				$total_questions = count( $answers );
				// Calculate correct answers from score.
				$correct_answers = round( ( $score_percentage / 100 ) * $total_questions );
			}
		}

		// Format time taken.
		$time_taken = '';
		if ( $latest_attempt && isset( $latest_attempt->time_taken ) ) {
			$seconds = intval( $latest_attempt->time_taken );
			$minutes = floor( $seconds / 60 );
			$hours   = floor( $minutes / 60 );
			if ( $hours > 0 ) {
				$time_taken = sprintf( '%d hour(s) %d minute(s)', $hours, $minutes % 60 );
			} elseif ( $minutes > 0 ) {
				$time_taken = sprintf( '%d minute(s) %d second(s)', $minutes, $seconds % 60 );
			} else {
				$time_taken = sprintf( '%d second(s)', $seconds );
			}
		}

		$replacements = array(
			'user_name'       => $user->display_name,
			'site_name'       => get_bloginfo( 'name' ),
			'course_title'    => $course ? $course->post_title : '',
			'course_id'       => $course_id, // For dispatcher to extract.
			'quiz_title'      => $quiz->post_title,
			'quiz_id'         => $quiz_id, // For dispatcher to extract.
			'quiz_score'      => $score_percentage,
			'score'           => $score_percentage, // Alias for in-app templates.
			'passing_grade'   => $passing_grade,
			'passing_score'   => $passing_grade, // Alias for in-app templates.
			'correct_answers' => $correct_answers,
			'total_questions' => $total_questions,
			'quiz_url'        => get_permalink( $quiz_id ),
			'time_taken'      => $time_taken,
			'course_url'      => $course ? get_permalink( $course_id ) : '',
		);

		// Determine which template to use based on pass/fail.
		$template_key = $passed ? 'quiz_passed' : 'quiz_failed';

		// Send only the specific notification (passed or failed) to avoid duplicate emails.
		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, $template_key, $replacements, $user_id );
	}

	/**
	 * Send certificate awarded email.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id       User ID.
	 * @param int $course_id     Course ID.
	 * @param int $certificate_id Certificate ID.
	 * @return void
	 */
	public function send_certificate_awarded_email( $user_id, $course_id, $certificate_id ) {
		$user   = get_userdata( $user_id );
		$course = get_post( $course_id );

		if ( ! $user || ! $course ) {
			return;
		}

		// Get certificate date - use current time as default.
		$certificate_date = current_time( 'mysql' );

		// Format certificate date.
		$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $certificate_date ) );

		$replacements = array(
			'user_name'        => $user->display_name,
			'site_name'        => get_bloginfo( 'name' ),
			'course_title'     => $course->post_title,
			'certificate_id'   => $certificate_id,
			'certificate_url'  => $this->get_certificate_url( $user_id, $course_id ),
			'certificate_date' => $formatted_date,
		);

		SkillPulse_LMS_Notification_Dispatcher::get_instance()->send_notification( $user->user_email, 'certificate_awarded', $replacements, $user_id );
	}

	/**
	 * Get activation link.
	 *
	 * @since 1.0.0
	 *
	 * @param object $signup Signup object.
	 * @return string Activation link.
	 */
	private function get_activation_link( $signup ) {
		// Use the new confirmation-based activation flow.
		$signup_screen_handler = SkillPulse_LMS_Signup_Screen_Handler::get_instance();
		$activation_link       = $signup_screen_handler->get_signup_url( 'activate/' . $signup->activation_key . '/' );

		return $activation_link;
	}

	/**
	 * Get certificate URL.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id   User ID.
	 * @param int $course_id Course ID.
	 * @return string Certificate URL.
	 */
	private function get_certificate_url( $user_id, $course_id ) {
		// This would be implemented based on your certificate system.
		return add_query_arg(
			array(
				'certificate' => 'download',
				'user_id'     => $user_id,
				'course_id'   => $course_id,
			),
			home_url()
		);
	}

	/**
	 * Customize password reset email.
	 *
	 * @since 1.0.0
	 *
	 * @param array   $email     Email data.
	 * @param string  $key       Reset key.
	 * @param string  $user_login User login.
	 * @param WP_User $user_data User data.
	 * @return array Modified email data.
	 */
	public function customize_password_reset_email( $email, $key, $user_login, $user_data ) {
		$email_templates = SkillPulse_LMS_Email_Templates::get_instance();
		$template        = $email_templates->get_template( 'password_reset' );

		if ( empty( $template ) || ! $template['is_active'] ) {
			return $email; // Fall back to default WordPress email.
		}

		// Get custom login URL if configured, otherwise fall back to default.
		$login_url = wp_login_url();

		$reset_link = add_query_arg(
			array(
				'action' => 'reset-password',
				'key'    => $key,
				'login'  => $user_login,
			),
			$login_url
		);

		$replacements = array(
			'user_name'  => $user_data->display_name,
			'site_name'  => get_bloginfo( 'name' ),
			'reset_link' => $reset_link,
		);

		$email_sender = SkillPulse_LMS_Email_Sender::get_instance();

		// Customize both subject and message.
		$custom_subject = $email_sender->replace_placeholders( $template['subject'], $replacements );
		$custom_message = $email_sender->replace_placeholders( $template['content'], $replacements );

		$email['subject'] = $custom_subject;
		$email['message'] = $custom_message;
		$email['headers'] = $email_sender->get_email_headers( $user_data->user_email );

		return $email;
	}

	/**
	 * Send order completion email with invoice attachment.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $order_id      Order ID.
	 * @param object $order         Order object.
	 * @param string $old_status    Old status.
	 * @param string $new_status    New status.
	 * @return void
	 */
	public function send_order_completion_email( $order_id, $order, $old_status, $new_status ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Parameters required by WordPress action signature.
		// Check if email notifications are enabled globally.
		if ( ! function_exists( 'splms_is_email_notifications_enabled' ) || ! splms_is_email_notifications_enabled() ) {
			return;
		}

		// Get user data.
		$user = get_userdata( $order->user_id );
		if ( ! $user || empty( $user->user_email ) ) {
			return;
		}

		// Get order details for email.
		$orders_query = SkillPulse_LMS_Orders_Query::get_instance();
		$order_meta   = $orders_query->get_order_meta( $order_id );

		// Get course data.
		$course = get_post( $order->course_id );
		if ( ! $course ) {
			return;
		}

		// Get payment method.
		$payment_method        = $order_meta['payment_method'] ?? 'unknown';
		$payment_method_labels = array(
			'paypal'   => __( 'PayPal', 'skillpulse-lms' ),
			'stripe'   => __( 'Stripe', 'skillpulse-lms' ),
			'razorpay' => __( 'Razorpay', 'skillpulse-lms' ),
		);
		$payment_method_label  = isset( $payment_method_labels[ $payment_method ] ) ? $payment_method_labels[ $payment_method ] : ucfirst( $payment_method );

		// Format order date.
		$order_date = $order->created_at ? date_i18n( get_option( 'date_format' ), strtotime( $order->created_at ) ) : '';

		// Format amount.
		$currency         = ! empty( $order->currency ) ? $order->currency : splms_get_setting( 'currency', 'USD' );
		$currency_symbol  = splms_get_currency_symbol( $currency );
		$formatted_amount = $currency_symbol . number_format( floatval( $order->amount ), 2 );

		// Prepare email replacements.
		$replacements = array(
			'user_name'      => $user->display_name,
			'site_name'      => get_bloginfo( 'name' ),
			'order_id'       => $order_id,
			'order_date'     => $order_date,
			'course_title'   => $course->post_title,
			'course_url'     => get_permalink( $course->ID ),
			'payment_method' => $payment_method_label,
			'order_amount'   => $formatted_amount,
		);

		// Generate invoice PDF for attachment.
		$attachments = array();
		if ( class_exists( 'SkillPulse_LMS_Order_Invoice' ) ) {
			$invoice_generator = SkillPulse_LMS_Order_Invoice::get_instance();
			$invoice_result    = $invoice_generator->generate_pdf( $order_id );

			if ( ! is_wp_error( $invoice_result ) ) {
				// Use filepath if available, otherwise derive from download_url.
				if ( isset( $invoice_result['filepath'] ) && file_exists( $invoice_result['filepath'] ) ) {
					$attachments[] = $invoice_result['filepath'];
				} elseif ( isset( $invoice_result['download_url'] ) ) {
					// Fallback: Get file path from file URL.
					$upload_dir = wp_upload_dir();
					$file_url   = $invoice_result['download_url'];
					$file_path  = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );

					// Verify file exists before attaching.
					if ( file_exists( $file_path ) ) {
						$attachments[] = $file_path;
					}
				}
			}
		}

		// Add attachments to replacements (will be extracted in dispatcher).
		$replacements['_attachments'] = $attachments;

		// Send email using notification dispatcher.
		$dispatcher = SkillPulse_LMS_Notification_Dispatcher::get_instance();
		$results    = $dispatcher->send_notification( $user->user_email, 'order_completed', $replacements, $user->ID );

		// Log result for debugging.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && is_wp_error( $results['email'] ) ) {
			error_log( 'Order completion email failed: ' . $results['email']->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}
