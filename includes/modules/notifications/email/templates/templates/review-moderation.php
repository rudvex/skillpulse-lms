<?php
/**
 * Review Moderation Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Review Moderation Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Review_Moderation_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'review_moderation' );
		$this->set_template_name( __( 'Review Moderation Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Review pending moderation: {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to administrators when a new review is pending moderation.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Review Pending Moderation</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Action Required</p>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #856404; line-height: 1.6;">
            A new review is pending moderation and requires your attention.
        </p>
        
        <div style="background: #fff; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; color: #333; font-weight: bold;">Course: {course_title}</p>
            <p style="margin: 0 0 10px 0; color: #555;">Reviewer: {reviewer_name}</p>
            <p style="margin: 0 0 10px 0; color: #555;">Rating: {rating} stars</p>
            <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                <p style="margin: 0 0 5px 0; color: #333; font-weight: bold;">Review Content:</p>
                <p style="margin: 0; color: #555; line-height: 1.6; white-space: pre-wrap;">{review_content}</p>
            </div>
        </div>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{admin_url}" style="background: #ffc107; color: #000; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Moderate Review
            </a>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            This is an automated notification from<br>
            <strong>{site_name}</strong>
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
			'course_title'   => __( 'Course Title', 'skillpulse-lms' ),
			'reviewer_name'  => __( 'Reviewer Name', 'skillpulse-lms' ),
			'rating'         => __( 'Rating (stars)', 'skillpulse-lms' ),
			'review_content' => __( 'Review Content', 'skillpulse-lms' ),
			'admin_url'      => __( 'Admin Dashboard URL', 'skillpulse-lms' ),
			'site_name'      => __( 'Site Name', 'skillpulse-lms' ),
		);
	}
}
