<?php
/**
 * Review Reply Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Review Reply Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Review_Reply_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'review_reply' );
		$this->set_template_name( __( 'Review Reply Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'New reply to your review for {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to students when an instructor replies to their course review.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">New Reply to Your Review</h2>
        <p style="color: #666; margin: 10px 0 0 0;">{course_title}</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{student_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            You have received a new reply to your review for <strong>{course_title}</strong>.
        </p>
        
        <div style="background: #fff; border-left: 4px solid #007cba; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; color: #333; font-weight: bold;">Reply from {author_name}:</p>
            <p style="margin: 0; color: #555; line-height: 1.6; white-space: pre-wrap;">{reply_content}</p>
        </div>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{course_url}" style="background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                View Course
            </a>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Best regards,<br>
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
			'student_name'  => __( 'Student Name', 'skillpulse-lms' ),
			'course_title'  => __( 'Course Title', 'skillpulse-lms' ),
			'author_name'   => __( 'Reply Author Name', 'skillpulse-lms' ),
			'reply_content' => __( 'Reply Content', 'skillpulse-lms' ),
			'course_url'    => __( 'Course URL', 'skillpulse-lms' ),
			'site_name'     => __( 'Site Name', 'skillpulse-lms' ),
		);
	}
}
