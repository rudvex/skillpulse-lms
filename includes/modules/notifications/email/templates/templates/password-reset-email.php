<?php
/**
 * Password Reset Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Password Reset Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Password_Reset_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'password_reset' );
		$this->set_template_name( __( 'Password Reset Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Reset your password - {site_name}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they request a password reset. Includes professional styling and security notice.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Password Reset Request</h2>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            We received a request to reset your password for your account on {site_name}. If you made this request, please click the button below to reset your password:
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{reset_link}" style="background: #dc3545; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Reset My Password
            </a>
        </div>
        
        <p style="margin: 15px 0 0 0; color: #666; font-size: 14px; line-height: 1.5;">
            If the button doesn\'t work, you can copy and paste this link into your browser:<br>
            <a href="{reset_link}" style="color: #dc3545;">{reset_link}</a>
        </p>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
            <strong>Security Notice:</strong> This password reset link will expire in 24 hours. If you did not request a password reset, please ignore this email and your password will remain unchanged.
        </p>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0; color: #004085; font-size: 14px;">
            <strong>Need Help?</strong> If you\'re having trouble resetting your password, please contact our support team.
        </p>
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
			'user_name'  => __( 'User Name', 'skillpulse-lms' ),
			'site_name'  => __( 'Site Name', 'skillpulse-lms' ),
			'reset_link' => __( 'Password Reset Link', 'skillpulse-lms' ),
		);
	}
}
