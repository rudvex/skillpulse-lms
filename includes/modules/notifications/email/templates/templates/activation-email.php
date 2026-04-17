<?php
/**
 * Activation Email Template
 *
 * @package SkillPulse_LMS
 * @subpackage Email
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Activation Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Activation_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'activation_email' );
		$this->set_template_name( __( 'Account Activation Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Activate your account - {site_name}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they sign up to activate their account. Includes professional styling and security notice.', 'skillpulse-lms' ) );
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
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Thank you for signing up with {site_name}. To complete your account setup and start learning, please click the button below to activate your account:
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{activation_link}" style="background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Activate My Account
            </a>
        </div>
        
        <p style="margin: 15px 0 0 0; color: #666; font-size: 14px; line-height: 1.5;">
            If the button doesn\'t work, you can copy and paste this link into your browser:<br>
            <a href="{activation_link}" style="color: #007cba;">{activation_link}</a>
        </p>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0; color: #856404; font-size: 14px;">
            <strong>Security Notice:</strong> If you did not create this account, please ignore this email. Your account will not be activated without clicking the link above.
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
			'user_name'       => __( 'User Name', 'skillpulse-lms' ),
			'site_name'       => __( 'Site Name', 'skillpulse-lms' ),
			'activation_link' => __( 'Activation Link', 'skillpulse-lms' ),
		);
	}
}
