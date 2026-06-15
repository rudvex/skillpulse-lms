<?php
/**
 * Wishlist Template
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

if ( function_exists( 'splms_get_setting' ) && ! splms_get_setting( 'enable_course_wishlist', true ) ) {
	echo '<div class="splms-dashboard-tab splms-wishlist-tab">';
	echo '<div class="splms-alert splms-alert-warning">' . esc_html__( 'Wishlist is disabled by the site admin.', 'skillpulse-lms' ) . '</div>';
	echo '</div>';
	return;
}

$splms_wishlist = get_user_meta( $splms_user_id, '_splms_course_wishlist', true );
$splms_wishlist = is_array( $splms_wishlist ) ? array_map( 'intval', $splms_wishlist ) : array();

echo '<div class="splms-dashboard-tab splms-wishlist-tab">';

// Page Header.
echo '<div class="splms-section-title" style="margin-bottom: 2rem;">';
echo '<i class="hgi-stroke hgi-favourite"></i>';
echo esc_html__( 'My Wishlist', 'skillpulse-lms' );
echo '</div>';

// Simple summary for wishlist.
if ( ! empty( $splms_wishlist ) ) {
	echo '<div class="splms-courses-summary">';
	echo '<p class="splms-summary-text">';
	printf(
		/* translators: %d: number of courses in wishlist */
		esc_html__( 'You have %d courses in your wishlist', 'skillpulse-lms' ),
		count( $splms_wishlist )
	);
	echo '</p>';
	echo '</div>';
}

if ( empty( $splms_wishlist ) ) {
	echo '<div class="splms-dashboard-empty-state">';
	echo '<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">';
	echo '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>';
	echo '</svg>';
	echo '<h3>' . esc_html__( 'Your Wishlist is Empty', 'skillpulse-lms' ) . '</h3>';
	echo '<p>' . esc_html__( 'Add courses to your wishlist to keep track of courses you want to take later.', 'skillpulse-lms' ) . '</p>';
	echo '</div>';
} else {
	echo '<div class="splms-course-list">';
	$splms_wishlist_posts = get_posts(
		array(
			'post_type'   => SPLMS_POST_TYPES['course'],
			'post__in'    => $splms_wishlist,
			'orderby'     => 'post__in',
			'numberposts' => -1,
		)
	);

	$splms_course_colors = array( '#4F46E5', '#EC4899', '#10B981', '#F59E0B', '#8B5CF6', '#06B6D4' );
	$splms_course_icons  = array( 'hgi-computer-check', 'hgi-paint-board', 'hgi-book-open-01', 'hgi-code-circle', 'hgi-database-01', 'hgi-graduation-hat-01' );

	foreach ( $splms_wishlist_posts as $splms_index => $splms_course ) {
		$splms_color = $splms_course_colors[ $splms_index % count( $splms_course_colors ) ];
		$splms_icon  = $splms_course_icons[ $splms_index % count( $splms_course_icons ) ];

		echo '<div class="splms-course-card">';
		echo '<div class="splms-course-icon" style="color: ' . esc_attr( $splms_color ) . '; background-color: ' . esc_attr( $splms_color ) . '20;">';
		echo '<i class="hgi-stroke ' . esc_attr( $splms_icon ) . '"></i>';
		echo '</div>';
		echo '<div class="splms-course-info">';
		echo '<div class="splms-course-header">';
		echo '<h3 class="splms-course-title">' . esc_html( get_the_title( $splms_course ) ) . '</h3>';
		echo '</div>';
		if ( $splms_course->post_excerpt ) {
			echo '<p class="splms-course-excerpt">' . esc_html( wp_trim_words( $splms_course->post_excerpt, 20 ) ) . '</p>';
		}
		echo '</div>';
		echo '<div class="splms-course-actions">';
		echo '<a class="splms-btn splms-btn-primary" href="' . esc_url( get_permalink( $splms_course ) ) . '">' . esc_html__( 'View Course', 'skillpulse-lms' ) . ' <i class="hgi-stroke hgi-arrow-right-01"></i></a>';
		echo '<button type="button" class="splms-btn splms-btn-secondary splms-wishlist-btn splms-remove-wishlist in-wishlist" data-course-id="' . esc_attr( $splms_course->ID ) . '">' . esc_html__( 'Remove', 'skillpulse-lms' ) . '</button>';
		echo '</div>';
		echo '</div>';
	}

	echo '</div>';
}

echo '</div>';
