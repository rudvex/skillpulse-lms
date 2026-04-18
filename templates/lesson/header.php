<?php
/**
 * Lesson Fullscreen Header
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/header.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use shared fullscreen header component.
splms_get_template_part(
	'shared/fullscreen-header',
	'',
	array(
		'splms_item_id' => get_the_ID(),
		'item_type'     => 'lesson',
		'user_id'       => get_current_user_id(),
	)
);
