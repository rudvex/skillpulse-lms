<?php
/**
 * Enrollment Reminder Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Enrollment Reminder Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Enrollment_Reminder_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'enrollment_reminder' );
		$this->set_template_name( __( 'Enrollment Reminder Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Continue Learning: {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to remind enrolled students to continue their learning journey. Includes course progress and direct link to course.', 'skillpulse-lms' ) );
		$this->set_placeholders( $this->get_template_placeholders() );
	}

	/**
	 * Get template HTML content.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template HTML content.
	 */
	private function get_template_html() {
		$title         = esc_html__( 'Continue Your Learning Journey', 'skillpulse-lms' );
		$subtitle      = esc_html__( "Don't let your progress slip away", 'skillpulse-lms' );
		$button_text   = esc_html__( 'Continue Learning', 'skillpulse-lms' );
		$section_title = esc_html__( 'Keep Your Momentum Going', 'skillpulse-lms' );
		$list_item_1   = esc_html__( 'Resume where you left off', 'skillpulse-lms' );
		$list_item_2   = esc_html__( 'Complete lessons and quizzes', 'skillpulse-lms' );
		$list_item_3   = esc_html__( 'Track your progress', 'skillpulse-lms' );
		$list_item_4   = esc_html__( 'Earn your certificate', 'skillpulse-lms' );
		$footer_text   = esc_html__( 'This is an automated reminder email. You can manage your email preferences in your account settings.', 'skillpulse-lms' );

		return '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #333; margin: 0;">' . $title . '</h2>
        <p style="color: #666; margin: 10px 0 0 0;">' . $subtitle . '</p>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #333; margin: 0 0 15px 0;">
            Hi <strong>{user_name}</strong>,
        </p>
        <p style="color: #666; margin: 0 0 15px 0;">
            You\'re enrolled in <strong>{course_title}</strong> and we wanted to remind you to continue your learning!
        </p>
        <p style="color: #666; margin: 0;">
            Your current progress: <strong>{progress}</strong>
        </p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{course_url}" style="display: inline-block; padding: 12px 30px; background-color: #0073aa; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
            ' . $button_text . '
        </a>
    </div>

    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">' . $section_title . '</h3>
        <ul style="margin: 0; padding-left: 20px; color: #555;">
            <li>' . $list_item_1 . '</li>
            <li>' . $list_item_2 . '</li>
            <li>' . $list_item_3 . '</li>
            <li>' . $list_item_4 . '</li>
        </ul>
    </div>

    <div style="text-align: center; color: #999; font-size: 12px; margin-top: 30px;">
        <p style="margin: 0;">' . $footer_text . '</p>
    </div>
</div>';
	}

	/**
	 * Get template placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return array Template placeholders.
	 */
	private function get_template_placeholders() {
		return array(
			'user_name'    => __( 'User Name', 'skillpulse-lms' ),
			'course_title' => __( 'Course Title', 'skillpulse-lms' ),
			'course_url'   => __( 'Course URL', 'skillpulse-lms' ),
			'progress'     => __( 'Progress Percentage', 'skillpulse-lms' ),
		);
	}
}
