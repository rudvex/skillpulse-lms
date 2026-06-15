<?php
/**
 * Quiz Fullscreen Header
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/quiz/header.php
 *
 * @package SPLMS
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
		'item_type'     => 'quiz',
		'user_id'       => get_current_user_id(),
	)
);
