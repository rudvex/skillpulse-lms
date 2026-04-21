<?php
/**
 * Lesson Completion Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Lesson Completion Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Lesson_Completion_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'lesson_completion' );
		$this->set_template_name( __( 'Lesson Completion Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Great job! You completed {lesson_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they complete a lesson. Encourages continued learning and shows progress.', 'skillpulse-lms' ) );
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
		return __(
			'<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; margin-bottom: 30px;">
        <h2 style="color: #28a745; margin: 0;">🎉 Lesson Completed!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">You\'re making great progress</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Congratulations! You have successfully completed the lesson: <strong>{lesson_title}</strong> in the course <strong>{course_title}</strong>.
        </p>
        
        <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
            Your dedication to learning is paying off. Keep up the excellent work as you continue your learning journey!
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{course_url}" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Continue Learning
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">Your Progress</h3>
        <p style="margin: 0; color: #555; line-height: 1.6;">
            Course: <strong>{course_title}</strong><br>
            Lesson: <strong>{lesson_title}</strong><br>
            Course Progress: <strong>{course_progress}%</strong>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Keep learning and growing!<br>
            <strong>The {site_name} Team</strong>
        </p>
    </div>
</div>',
			'skillpulse-lms'
		);
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
			'user_name'       => __( 'User Name', 'skillpulse-lms' ),
			'site_name'       => __( 'Site Name', 'skillpulse-lms' ),
			'course_title'    => __( 'Course Title', 'skillpulse-lms' ),
			'lesson_title'    => __( 'Lesson Title', 'skillpulse-lms' ),
			'course_url'      => __( 'Course URL', 'skillpulse-lms' ),
			'course_progress' => __( 'Course Progress Percentage', 'skillpulse-lms' ),
		);
	}
}
