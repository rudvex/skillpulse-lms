<?php
/**
 * Order Refunded Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Refunded Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Refunded_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'order_refunded' );
		$this->set_template_name( __( 'Order Refunded Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Order #{order_id} Refunded', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to customers when their order is refunded. Includes refund amount and reason.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Order Refunded</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Your refund has been processed</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            We have processed a refund for your order <strong>#{order_id}</strong> for the course <strong>{course_title}</strong>.
        </p>
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">Refund Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Order Number:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee; font-weight: bold;">#{order_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Refund Amount:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee; font-weight: bold; color: #28a745;">{refund_amount}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Original Amount:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold;">{order_amount}</td>
                </tr>
            </table>
        </div>
        
        <# if ( reason ) { #>
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">Refund Reason</h3>
            <p style="margin: 0; color: #856404; line-height: 1.6;">{reason}</p>
        </div>
        <# } #>
        
        <p style="margin: 20px 0 0 0; color: #555; line-height: 1.6;">
            The refund will be processed to your original payment method. Please allow 5-10 business days for the refund to appear in your account.
        </p>
        
        <p style="margin: 15px 0 0 0; color: #555; line-height: 1.6;">
            <strong>Note:</strong> Your access to the course has been revoked as part of this refund.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            If you have any questions, please contact us.<br>
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
			'user_name'     => __( 'User Name', 'skillpulse-lms' ),
			'site_name'     => __( 'Site Name', 'skillpulse-lms' ),
			'order_id'      => __( 'Order ID', 'skillpulse-lms' ),
			'course_title'  => __( 'Course Title', 'skillpulse-lms' ),
			'refund_amount' => __( 'Refund Amount', 'skillpulse-lms' ),
			'order_amount'  => __( 'Original Order Amount', 'skillpulse-lms' ),
			'reason'        => __( 'Refund Reason', 'skillpulse-lms' ),
		);
	}
}
