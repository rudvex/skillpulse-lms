<?php
/**
 * Order Completed Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Order Completed Email Template Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Order_Completed_Email_Template extends SkillPulse_LMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'order_completed' );
		$this->set_template_name( __( 'Order Completed Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( 'Order Confirmation - Order #{order_id}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to customers when their order is completed. Includes order details and invoice download link.', 'skillpulse-lms' ) );
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
        <h2 style="color: #2c5aa0; margin: 0;">Thank You for Your Purchase!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">Your order has been confirmed</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Thank you for your purchase! Your order <strong>#{order_id}</strong> for the course <strong>{course_title}</strong> has been confirmed and your payment has been processed successfully.
        </p>
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">Order Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Order Number:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee; font-weight: bold;">#{order_id}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Order Date:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee;">{order_date}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Course:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee; font-weight: bold;">{course_title}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666; border-bottom: 1px solid #eee;">Payment Method:</td>
                    <td style="padding: 8px 0; text-align: right; border-bottom: 1px solid #eee;">{payment_method}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Total Amount:</td>
                    <td style="padding: 8px 0; text-align: right; font-weight: bold; color: #2c5aa0; font-size: 18px;">{order_amount}</td>
                </tr>
            </table>
        </div>
        
        <div style="background: #e7f3ff; border: 1px solid #2c5aa0; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center;">
            <p style="margin: 0 0 10px 0; color: #2c5aa0; font-weight: bold;">🎉 You now have access to your course!</p>
            <a href="{course_url}" style="display: inline-block; background-color: #2c5aa0; color: #fff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;">Access Your Course</a>
        </div>
        
        <div style="background: #fff; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">Invoice</h3>
            <p style="margin: 0 0 10px 0; color: #555; line-height: 1.6;">
                Your invoice is attached to this email. You can also download it anytime from your account dashboard.
            </p>
            <p style="margin: 10px 0 0 0; color: #555; line-height: 1.6;">
                <strong>Note:</strong> If you don\'t see the invoice attachment, please check your spam folder or contact us for assistance.
            </p>
        </div>
        
        <p style="margin: 20px 0 0 0; color: #555; line-height: 1.6;">
            If you have any questions about your order or need assistance, please don\'t hesitate to contact us.
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Thank you for choosing {site_name}!<br>
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
			'user_name'      => __( 'User Name', 'skillpulse-lms' ),
			'site_name'      => __( 'Site Name', 'skillpulse-lms' ),
			'order_id'       => __( 'Order ID', 'skillpulse-lms' ),
			'order_date'     => __( 'Order Date', 'skillpulse-lms' ),
			'course_title'   => __( 'Course Title', 'skillpulse-lms' ),
			'course_url'     => __( 'Course URL', 'skillpulse-lms' ),
			'payment_method' => __( 'Payment Method', 'skillpulse-lms' ),
			'order_amount'   => __( 'Order Amount', 'skillpulse-lms' ),
		);
	}
}
