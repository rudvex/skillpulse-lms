<?php
/**
 * Certificate Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Certificate Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Certificate_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'certificate_email' );
		$this->set_template_name( __( 'Certificate Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( '📜 Your certificate for {course_title} is ready!', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when their certificate is ready. Includes certificate download link and usage tips.', 'skillpulse-lms' ) );
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
        <div style="font-size: 48px; margin-bottom: 15px;">📜</div>
        <h2 style="color: #333; margin: 0;">Your Certificate is Ready!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Download your certificate for {course_title}</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Great news! Your certificate for <strong>{course_title}</strong> is now ready for download. This certificate represents your successful completion of the course and the knowledge you\'ve gained.
        </p>
        
        <p style="margin: 0 0 20px 0; color: #555; line-height: 1.6;">
            Your certificate is professionally designed and includes all the necessary details to verify your achievement. You can download it now and keep it for your records.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{certificate_url}" style="background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Download Certificate
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">Certificate Features</h3>
        <ul style="margin: 0; padding-left: 20px; color: #555;">
            <li>Professional design with your name and course title</li>
            <li>Completion date and verification details</li>
            <li>High-quality PDF format for printing</li>
            <li>Verifiable online through our system</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">💡 How to Use Your Certificate</h3>
        <ul style="margin: 0; padding-left: 20px; color: #856404;">
            <li>Add it to your professional portfolio</li>
            <li>Share it on LinkedIn and other professional networks</li>
            <li>Include it in your resume or CV</li>
            <li>Print it for physical display</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Congratulations on your achievement!<br>
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
