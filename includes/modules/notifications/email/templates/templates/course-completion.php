<?php
/**
 * Course Completion Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Course Completion Email Template Class
 *
 * @since 1.0.0
 */
class SPLMS_Course_Completion_Email_Template extends SPLMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'course_completion' );
		$this->set_template_name( __( 'Course Completion Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( '🎉 Congratulations! You completed {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they complete a course. Includes congratulations message and certificate download link.', 'skillpulse-lms' ) );
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
        <div style="font-size: 48px; margin-bottom: 15px;">🎉</div>
        <h2 style="color: #333; margin: 0;">Congratulations!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">You have successfully completed {course_title}</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            <strong>Fantastic achievement!</strong> You have successfully completed <strong>{course_title}</strong>. Your dedication and hard work have paid off!
        </p>
        
        <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
            You\'ve demonstrated excellent commitment to your learning journey. This accomplishment represents a significant step forward in your professional development.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{certificate_url}" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Download Your Certificate
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">What\'s Next?</h3>
        <ul style="margin: 0; padding-left: 20px; color: #555;">
            <li>Share your certificate on LinkedIn</li>
            <li>Add this achievement to your resume</li>
            <li>Explore more courses to continue learning</li>
            <li>Connect with other learners in our community</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">Certificate Details</h3>
        <p style="margin: 0; color: #856404; font-size: 14px;">
            Your certificate is now available for download. It includes your name, course title, completion date, and can be verified online. Keep it safe as proof of your achievement!
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Keep up the great work!<br>
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
			'certificate_url' => __( 'Certificate URL', 'skillpulse-lms' ),
		);
	}
}
