<?php
/**
 * Course Enrollment Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Course Enrollment Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Course_Enrollment_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'course_enrollment' );
		$this->set_template_name( __( 'Course Enrollment Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Welcome to {course_title} - You\'re enrolled!', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they enroll in a course. Includes course access details and learning tips.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Welcome to {course_title}!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">You\'re successfully enrolled and ready to start learning</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Great news! You have successfully enrolled in <strong>{course_title}</strong>. Your learning journey is about to begin!
        </p>
        
        <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
            You now have full access to all course materials, lessons, and resources. Take your time to explore the course content at your own pace.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{login_url}" style="background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Start Learning Now
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">Course Access Details</h3>
        <ul style="margin: 0; padding-left: 20px; color: #555;">
            <li>Access your course anytime, anywhere</li>
            <li>Track your progress through lessons</li>
            <li>Complete assignments and quizzes</li>
            <li>Earn your certificate upon completion</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">💡 Learning Tips</h3>
        <ul style="margin: 0; padding-left: 20px; color: #856404;">
            <li>Set aside dedicated time for learning</li>
            <li>Take notes during lessons</li>
            <li>Complete all assignments for best results</li>
            <li>Don\'t hesitate to ask questions</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Happy learning!<br>
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
			'user_name'    => __( 'User Name', 'skillpulse-lms' ),
			'site_name'    => __( 'Site Name', 'skillpulse-lms' ),
			'course_title' => __( 'Course Title', 'skillpulse-lms' ),
			'login_url'    => __( 'Login URL', 'skillpulse-lms' ),
		);
	}
}
