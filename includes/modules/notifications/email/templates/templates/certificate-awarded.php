<?php
/**
 * Certificate Awarded Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Certificate Awarded Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Certificate_Awarded_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'certificate_awarded' );
		$this->set_template_name( __( 'Certificate Awarded Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( '🎓 Congratulations! You earned a certificate for {course_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they are awarded a certificate. Celebrates their achievement and provides certificate download link.', 'skillpulse-lms' ) );
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
        <h2 style="color: #ffc107; margin: 0;">🎓 Certificate Awarded!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Congratulations on your achievement</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            We are thrilled to inform you that you have successfully completed <strong>{course_title}</strong> and have been awarded a certificate of completion!
        </p>
        
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; padding: 30px; margin: 20px 0; text-align: center; color: white;">
            <h3 style="margin: 0 0 10px 0; font-size: 24px; color: white;">Certificate of Completion</h3>
            <p style="margin: 5px 0; font-size: 16px; color: rgba(255,255,255,0.9);">
                This certifies that<br>
                <strong style="font-size: 20px;">{user_name}</strong><br>
                has successfully completed<br>
                <strong style="font-size: 18px;">{course_title}</strong>
            </p>
            <p style="margin: 15px 0 0 0; font-size: 14px; color: rgba(255,255,255,0.8);">
                Date: {certificate_date}
            </p>
        </div>
        
        <p style="margin: 15px 0; color: #555; line-height: 1.6;">
            Your hard work, dedication, and commitment to learning have paid off. This certificate is a testament to your achievement and the knowledge you\'ve gained.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{certificate_url}" style="background: #ffc107; color: #000; padding: 15px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;">
                Download Certificate
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">Certificate Details</h3>
        <p style="margin: 0; color: #555; line-height: 1.6;">
            Course: <strong>{course_title}</strong><br>
            Certificate ID: <strong>{certificate_id}</strong><br>
            Date Awarded: <strong>{certificate_date}</strong><br>
            Issued By: <strong>{site_name}</strong>
        </p>
    </div>
    
    <div style="background: #d4edda; border: 1px solid #28a745; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #155724; font-size: 16px;">🌟 What\'s Next?</h3>
        <ul style="margin: 0; padding-left: 20px; color: #155724;">
            <li>Share your achievement on social media</li>
            <li>Add it to your professional profile</li>
            <li>Continue learning with more courses</li>
            <li>Explore advanced topics in your field</li>
        </ul>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Congratulations on this amazing achievement!<br>
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
			'user_name'        => __( 'User Name', 'skillpulse-lms' ),
			'site_name'        => __( 'Site Name', 'skillpulse-lms' ),
			'course_title'     => __( 'Course Title', 'skillpulse-lms' ),
			'certificate_id'   => __( 'Certificate ID', 'skillpulse-lms' ),
			'certificate_url'  => __( 'Certificate Download URL', 'skillpulse-lms' ),
			'certificate_date' => __( 'Certificate Award Date', 'skillpulse-lms' ),
		);
	}
}
