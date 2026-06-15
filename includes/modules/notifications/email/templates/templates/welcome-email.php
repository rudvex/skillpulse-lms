<?php
/**
 * Welcome Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Welcome Email Template Class
 *
 * @since 1.0.0
 */
class SPLMS_Welcome_Email_Template extends SPLMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'welcome_email' );
		$this->set_template_name( __( 'Welcome Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Welcome to {site_name} - Your account is ready!', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users after their account is activated. Includes welcome message and next steps guidance.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Welcome to {site_name}!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Your account has been successfully activated</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Congratulations! Your account has been successfully activated and you\'re now ready to start your learning journey with {site_name}.
        </p>
        
        <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
            You can now access all our courses, track your progress, and earn certificates as you complete your learning goals.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{login_url}" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Access Your Account
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">What\'s Next?</h3>
        <ul style="margin: 0; padding-left: 20px; color: #555;">
            <li>Browse our course catalog</li>
            <li>Enroll in your first course</li>
            <li>Track your learning progress</li>
            <li>Connect with other learners</li>
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
			'user_name' => __( 'User Name', 'skillpulse-lms' ),
			'site_name' => __( 'Site Name', 'skillpulse-lms' ),
			'login_url' => __( 'Login URL', 'skillpulse-lms' ),
		);
	}
}
