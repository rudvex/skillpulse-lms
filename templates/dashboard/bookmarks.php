<?php
/**
 * Bookmarks Template
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Extract variables from args.
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience.
extract( $args );

if ( function_exists( 'splms_get_setting' ) && ! splms_get_setting( 'enable_bookmarks', true ) ) {
	echo '<div class="splms-dashboard-tab splms-bookmarks-tab">';
	echo '<div class="splms-alert splms-alert-warning">' . esc_html__( 'Bookmarks are disabled by the site admin.', 'skillpulse-lms' ) . '</div>';
	echo '</div>';
	return;
}

$splms_bookmarks = get_user_meta( $splms_user_id, '_splms_bookmarks', true );
$splms_bookmarks = is_array( $splms_bookmarks ) ? array_map( 'intval', $splms_bookmarks ) : array();

echo '<div class="splms-dashboard-tab splms-bookmarks-tab">';

// Page Header.
echo '<div class="splms-section-title" style="margin-bottom: 2rem;">';
echo '<i class="hgi-stroke hgi-bookmark-01"></i>';
echo esc_html__( 'My Bookmarks', 'skillpulse-lms' );
echo '</div>';

// Simple summary for bookmarks.
if ( ! empty( $splms_bookmarks ) ) {
	echo '<div class="splms-courses-summary">';
	echo '<p class="splms-summary-text">';
	printf(
		/* translators: %d: number of bookmarks */
		esc_html__( 'You have %d bookmarked lessons and quizzes', 'skillpulse-lms' ),
		count( $splms_bookmarks )
	);
	echo '</p>';
	echo '</div>';
}

if ( empty( $splms_bookmarks ) ) {
	echo '<div class="splms-dashboard-empty-state">';
	echo '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
	echo '<path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
	echo '</svg>';
	echo '<h3>' . esc_html__( 'No Bookmarks Yet', 'skillpulse-lms' ) . '</h3>';
	echo '<p>' . esc_html__( 'Bookmark lessons and quizzes to easily find them later.', 'skillpulse-lms' ) . '</p>';
	echo '</div>';
} else {
	echo '<div class="splms-course-list">';
	// Bookmarks are for lessons and quizzes (not courses).
	$splms_bookmark_posts = get_posts(
		array(
			'post_type'   => array( SPLMS_POST_TYPES['lesson'], SPLMS_POST_TYPES['quiz'] ),
			'post__in'    => $splms_bookmarks,
			'orderby'     => 'post__in',
			'numberposts' => -1,
			'post_status' => 'publish',
		)
	);

	$splms_lesson_color = '#10B981';  // Green for lessons.
	$splms_quiz_color   = '#F59E0B';  // Orange for quizzes.

	foreach ( $splms_bookmark_posts as $splms_bookmark_post ) {
		$splms_is_quiz    = ( SPLMS_POST_TYPES['quiz'] === $splms_bookmark_post->post_type );
		$splms_data_attr  = $splms_is_quiz ? 'data-quiz-id' : 'data-lesson-id';
		$splms_type_label = $splms_is_quiz ? __( 'Quiz', 'skillpulse-lms' ) : __( 'Lesson', 'skillpulse-lms' );
		$splms_color      = $splms_is_quiz ? $splms_quiz_color : $splms_lesson_color;
		$splms_icon       = $splms_is_quiz ? 'hgi-clipboard' : 'hgi-book-open-01';

		echo '<div class="splms-course-card">';
		echo '<div class="splms-course-icon" style="color: ' . esc_attr( $splms_color ) . '; background-color: ' . esc_attr( $splms_color ) . '20;">';
		echo '<i class="hgi-stroke ' . esc_attr( $splms_icon ) . '"></i>';
		echo '</div>';
		echo '<div class="splms-course-info">';
		echo '<div class="splms-course-header">';
		echo '<h3 class="splms-course-title">' . esc_html( get_the_title( $splms_bookmark_post ) ) . '</h3>';
		echo '<span class="splms-enrollment-status-badge" style="background: ' . esc_attr( $splms_color ) . '20; color: ' . esc_attr( $splms_color ) . ';">' . esc_html( $splms_type_label ) . '</span>';
		echo '</div>';
		echo '</div>';
		echo '<div class="splms-course-actions">';
		echo '<a class="splms-btn splms-btn-primary" href="' . esc_url( get_permalink( $splms_bookmark_post ) ) . '">' . esc_html__( 'Continue', 'skillpulse-lms' ) . ' <i class="hgi-stroke hgi-arrow-right-01"></i></a>';
		echo '<button type="button" class="splms-btn splms-btn-secondary splms-bookmark-btn splms-remove-bookmark bookmarked" ' . esc_attr( $splms_data_attr ) . '="' . esc_attr( $splms_bookmark_post->ID ) . '">' . esc_html__( 'Remove', 'skillpulse-lms' ) . '</button>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}

echo '</div>';
