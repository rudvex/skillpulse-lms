<?php
/**
 * The plugin constants.
 *
 * @package    SkillPulse_LMS
 * @subpackage Constants
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal constants, not to be overridden
 */

if ( ! defined( 'SPLMS_IS_FREE' ) ) {
	define( 'SPLMS_IS_FREE', true );
}
if ( ! defined( 'SKILLPULSE_LMS_VERSION' ) ) {
	define( 'SKILLPULSE_LMS_VERSION', '1.0.1' );
}

if ( ! defined( 'SKILLPULSE_LMS_DB_VERSION' ) ) {
	define( 'SKILLPULSE_LMS_DB_VERSION', 3 );
}

if ( ! defined( 'SKILLPULSE_LMS_DIR_PATH' ) ) {
	define( 'SKILLPULSE_LMS_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SKILLPULSE_LMS_URL_PATH' ) ) {
	define( 'SKILLPULSE_LMS_URL_PATH', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'SKILLPULSE_LMS_ASSETS_URL' ) ) {
	define( 'SKILLPULSE_LMS_ASSETS_URL', SKILLPULSE_LMS_URL_PATH . 'assets/' );
}

if ( ! defined( 'SPLMS_POST_TYPES' ) ) {
	$sp_post_types = array(
		'course'      => 'sp-course',
		'section'     => 'sp-section',
		'lesson'      => 'sp-lesson',
		'quiz'        => 'sp-quiz',
		'certificate' => 'sp-certificate',
	);
	define( 'SPLMS_POST_TYPES', $sp_post_types );
}

if ( ! defined( 'SPLMS_TAXONOMIES' ) ) {
	$sp_taxonomies = array(
		'course_category' => 'sp-course-category',
		'course_tag'      => 'sp-course-tag',
	);
	define( 'SPLMS_TAXONOMIES', $sp_taxonomies );
}

