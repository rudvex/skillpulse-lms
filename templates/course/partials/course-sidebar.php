<?php
/**
 * Single course sidebar template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-sidebar.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id      = get_the_ID();
$splms_access_info    = splms_get_course_access_info( $splms_course_id );
$splms_is_enrolled    = splms_is_user_enrolled( $splms_course_id );
$splms_duration       = splms_get_course_duration( $splms_course_id );
$splms_students_count = splms_get_course_enrollment_count( $splms_course_id );
$splms_difficulty     = splms_get_course_difficulty( $splms_course_id );
$splms_rating_summary = splms_get_course_rating( $splms_course_id );

// Ensure we have the proper structure.
if ( ! is_array( $splms_rating_summary ) ) {
	$splms_rating_summary = array();
}

// Set default values if keys are missing.
$splms_rating_summary = array_merge(
	array(
		'average_rating'   => 0,
		'total_reviews'    => 0,
		'rating_breakdown' => array(
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		),
	),
	$splms_rating_summary
);
?>

<div class="course-sidebar-content">
	<!-- Course Categories -->
	<?php
	$splms_categories = get_the_terms( $splms_course_id, SPLMS_TAXONOMIES['course_category'] );
	if ( $splms_categories && ! is_wp_error( $splms_categories ) ) {
		?>
		<div class="sidebar-widget course-categories-widget">
			<h3 class="widget-title">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="currentColor" stroke-width="2"
							stroke-linecap="round" stroke-linejoin="round"/>
					<polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Categories', 'skillpulse-lms' ); ?>
			</h3>
			<div class="course-categories-list">
				<?php foreach ( $splms_categories as $splms_category ) { ?>
					<a href="<?php echo esc_url( get_term_link( $splms_category ) ); ?>" class="category-link">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<?php echo esc_html( $splms_category->name ); ?>
					</a>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<!-- Course Tags -->
	<?php
	$splms_tags = get_the_terms( $splms_course_id, SPLMS_TAXONOMIES['course_tag'] );
	if ( $splms_tags && ! is_wp_error( $splms_tags ) ) {
		?>
		<div class="sidebar-widget course-tags-widget">
			<h3 class="widget-title">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20.59 13.41L13.42 20.58C13.2343 20.766 13.0137 20.9135 12.7709 21.0141C12.5281 21.1148 12.2678 21.1666 12.005 21.1666C11.7422 21.1666 11.4819 21.1148 11.2391 21.0141C10.9963 20.9135 10.7757 20.766 10.59 20.58L2 12V2H12L20.59 10.59C20.9625 10.9647 21.1716 11.4716 21.1716 12C21.1716 12.5284 20.9625 13.0353 20.59 13.41Z"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M7 7H7.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Tags', 'skillpulse-lms' ); ?>
			</h3>
			<div class="course-tags-list">
				<?php foreach ( $splms_tags as $splms_course_tag ) { ?>
					<a href="<?php echo esc_url( get_term_link( $splms_course_tag ) ); ?>" class="tag-link">
						#<?php echo esc_html( $splms_course_tag->name ); ?>
					</a>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php
	// Allow modules to render sidebar widgets (e.g., certificate section).
	do_action( 'splms_course_sidebar_widgets', $splms_course_id );
	?>

	<!-- Enhanced Share Course -->
	<div class="sidebar-widget share-widget">
		<h3 class="widget-title">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
				<circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
				<circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
				<path d="M8.59 13.51L15.42 17.49" stroke="currentColor" stroke-width="2"/>
				<path d="M15.41 6.51L8.59 10.49" stroke="currentColor" stroke-width="2"/>
			</svg>
			<?php esc_html_e( 'Share Course', 'skillpulse-lms' ); ?>
		</h3>
		<div class="share-buttons">
			<div class="share-btn-group">
				<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_permalink() ); ?>"
					target="_blank"
					class="share-btn facebook"
					title="<?php esc_attr_e( 'Share on Facebook', 'skillpulse-lms' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
						<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
					</svg>
					<span>Facebook</span>
				</a>
			</div>
			<div class="share-btn-group">

				<a href="https://twitter.com/intent/tweet?url=<?php echo rawurlencode( get_permalink() ); ?>&text=<?php echo rawurlencode( get_the_title() ); ?>"
					target="_blank"
					class="share-btn twitter"
					title="<?php esc_attr_e( 'Share on Twitter', 'skillpulse-lms' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
						<path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
					</svg>
					<span>Twitter</span>
				</a>
			</div>
			<div class="share-btn-group">
				<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( get_permalink() ); ?>"
					target="_blank"
					class="share-btn linkedin"
					title="<?php esc_attr_e( 'Share on LinkedIn', 'skillpulse-lms' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
						<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
					</svg>
					<span>LinkedIn</span>
				</a>
			</div>
			<div class="share-btn-group">
				<button type="button" class="share-btn copy-link"
						data-copy-url="<?php echo esc_attr( get_permalink() ); ?>"
						title="<?php esc_attr_e( 'Copy Link', 'skillpulse-lms' ); ?>">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10 13C10.4295 13.5741 10.9774 14.0491 11.6066 14.3929C12.2357 14.7367 12.9315 14.9411 13.6467 14.9923C14.3618 15.0435 15.0796 14.9403 15.7513 14.6897C16.4231 14.4392 17.0331 14.047 17.54 13.54L20.54 10.54C21.4508 9.59695 21.9548 8.33394 21.9434 7.02296C21.932 5.71198 21.4061 4.45791 20.4791 3.53087C19.5521 2.60383 18.298 2.07799 16.987 2.0666C15.676 2.0552 14.413 2.55918 13.47 3.47L11.75 5.18"
								stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						<path d="M14 11C13.5705 10.4259 13.0226 9.95086 12.3934 9.60712C11.7643 9.26339 11.0685 9.05895 10.3533 9.00775C9.63819 8.95655 8.92037 9.05973 8.24864 9.31026C7.5769 9.5608 6.96687 9.95303 6.46 10.46L3.46 13.46C2.54917 14.403 2.04519 15.6661 2.0566 16.9771C2.06801 18.288 2.59385 19.5421 3.52089 20.4691C4.44793 21.3962 5.70201 21.922 7.01299 21.9334C8.32397 21.9448 9.58699 21.4408 10.53 20.53L12.24 18.82"
								stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span><?php esc_html_e( 'Copy Link', 'skillpulse-lms' ); ?></span>
				</button>
			</div>
		</div>
	</div>

</div> 
