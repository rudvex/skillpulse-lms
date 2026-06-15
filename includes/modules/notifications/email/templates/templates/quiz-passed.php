<?php
/**
 * Quiz Passed Email Template
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Quiz Passed Email Template Class
 *
 * @since 1.0.0
 */
class SPLMS_Quiz_Passed_Email_Template extends SPLMS_Abstract_Email_Template {

	/**
	 * Initialize template properties.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function init_template() {
		$this->set_template_key( 'quiz_passed' );
		$this->set_template_name( __( 'Quiz Passed Email', 'skillpulse-lms' ) );
		$this->set_template_subject( __( '🎉 Congratulations! You passed {quiz_title}', 'skillpulse-lms' ) );
		$this->set_template_content( $this->get_template_html() );
		$this->set_description( __( 'Email sent to users when they pass a quiz. Celebrates their achievement and encourages continued learning.', 'skillpulse-lms' ) );
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
        <h2 style="color: #28a745; margin: 0;">🎉 Congratulations!</h2>
        <p style="color: #666; margin: 10px 0 0 0;">You passed the quiz!</p>
    </div>
    
    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Hello <strong>{user_name}</strong>,
        </p>
        
        <p style="margin: 0 0 15px 0; color: #555; line-height: 1.6;">
            Excellent work! You have successfully passed the quiz: <strong>{quiz_title}</strong> for the course <strong>{course_title}</strong>.
        </p>
        
        <div style="background: #d4edda; border: 2px solid #28a745; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <h3 style="margin: 0 0 10px 0; color: #155724; font-size: 18px;">Your Score</h3>
            <div style="font-size: 36px; font-weight: bold; color: #28a745; margin: 10px 0;">
                {quiz_score}%
            </div>
            <p style="margin: 5px 0 0 0; color: #155724; font-size: 14px;">
                Passing Grade: {passing_grade}% | Your Score: {quiz_score}%
            </p>
        </div>
        
        <p style="margin: 15px 0; color: #555; line-height: 1.6;">
            Your dedication and hard work have paid off. You\'re making excellent progress in your learning journey!
        </p>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{course_url}" style="background: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Continue Learning
            </a>
        </div>
    </div>
    
    <div style="background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0; color: #0056b3; font-size: 16px;">Quiz Details</h3>
        <p style="margin: 0; color: #555; line-height: 1.6;">
            Course: <strong>{course_title}</strong><br>
            Quiz: <strong>{quiz_title}</strong><br>
            Score: <strong>{quiz_score}%</strong><br>
            Correct Answers: <strong>{correct_answers} out of {total_questions}</strong>
        </p>
    </div>
    
    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <p style="margin: 0; color: #666; font-size: 14px;">
            Keep up the excellent work!<br>
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
			'quiz_title'      => __( 'Quiz Title', 'skillpulse-lms' ),
			'quiz_score'      => __( 'Quiz Score Percentage', 'skillpulse-lms' ),
			'passing_grade'   => __( 'Passing Grade Percentage', 'skillpulse-lms' ),
			'correct_answers' => __( 'Number of Correct Answers', 'skillpulse-lms' ),
			'total_questions' => __( 'Total Questions', 'skillpulse-lms' ),
			'course_url'      => __( 'Course URL', 'skillpulse-lms' ),
		);
	}
}
