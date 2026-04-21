<?php
/**
 * Order Cancelled Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Cancelled Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Cancelled_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'order_cancelled' );
		$this->set_template_name( __( 'Order Cancelled Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Order #{order_id} Cancelled', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to customers when their pending order is cancelled. Includes cancellation reason.', 'skillpulse-lms' ) );
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
        <h2 style="color: #333; margin: 0;">Order Cancelled</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Your order has been cancelled</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Your order <strong>#{order_id}</strong> for the course <strong>{course_title}</strong> has been cancelled.
        </p>
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">Order Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Order Number:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee; font-weight: bold;">#{order_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Order Amount:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold;">{order_amount}</td>
                </tr>
            </table>
        </div>
        
        <# if ( reason ) { #>
        <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 10px 0; color: #856404; font-size: 16px;">Cancellation Reason</h3>
            <p style="margin: 0; color: #856404; line-height: 1.6;">{reason}</p>
        </div>
        <# } #>
        
        <p style="margin: 20px 0 0 0; color: #555; line-height: 1.6;">
            Since this order was pending and no payment was processed, no refund is required.
        </p>
        
        <p style="margin: 15px 0 0 0; color: #555; line-height: 1.6;">
            If you would like to purchase this course in the future, you can visit our course catalog anytime.
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{site_url}" style="background: #007cba; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Browse Courses
            </a>
        </div>
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
			'user_name'    => __( 'User Name', 'skillpulse-lms' ),
			'site_name'    => __( 'Site Name', 'skillpulse-lms' ),
			'site_url'     => __( 'Site URL', 'skillpulse-lms' ),
			'order_id'     => __( 'Order ID', 'skillpulse-lms' ),
			'course_title' => __( 'Course Title', 'skillpulse-lms' ),
			'order_amount' => __( 'Order Amount', 'skillpulse-lms' ),
			'reason'       => __( 'Cancellation Reason', 'skillpulse-lms' ),
		);
	}
}
