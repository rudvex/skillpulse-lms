<?php
/**
 * Single course content template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-content.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id = get_the_ID();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, nonce verification handled at higher level.
$splms_current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'overview';

// Load the appropriate tab template.
splms_get_template_part( 'course/partials/tabs/' . $splms_current_tab );
