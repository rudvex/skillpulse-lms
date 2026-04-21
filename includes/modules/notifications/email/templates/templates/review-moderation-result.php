<?php
/**
 * Review Moderation Result Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Review Moderation Result Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Review_Moderation_Result_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'review_moderation_result' );
		$this->set_template_name( __( 'Review Moderation Result Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Review update for: {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to review authors when their review moderation status changes (published, rejected, or flagged).', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Review Status Update</h2>
        <p style="color: #666; margin: 10px 0 0 0;">{course_title}</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <div style="background: {status_background}; border-left: 4px solid {status_color}; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; color: {status_text_color}; line-height: 1.6; font-weight: bold; font-size: 16px;">
                {status_message}
            </p>
        </div>
        
        <p style="margin: 15px 0; color: #555; line-height: 1.6;">
            {additional_message}
        </p>
        
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
			'user_name'          => __( 'User Name', 'skillpulse-lms' ),
			'course_title'       => __( 'Course Title', 'skillpulse-lms' ),
			'status'             => __( 'Status (published/rejected/flagged)', 'skillpulse-lms' ),
			'status_message'     => __( 'Status Message', 'skillpulse-lms' ),
			'status_background'  => __( 'Status Background Color', 'skillpulse-lms' ),
			'status_color'       => __( 'Status Border Color', 'skillpulse-lms' ),
			'status_text_color'  => __( 'Status Text Color', 'skillpulse-lms' ),
			'additional_message' => __( 'Additional Message', 'skillpulse-lms' ),
			'course_url'         => __( 'Course URL', 'skillpulse-lms' ),
			'site_name'          => __( 'Site Name', 'skillpulse-lms' ),
		);
	}
}
