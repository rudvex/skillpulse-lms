<?php
/**
 * Single course related courses template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-related.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Check if related courses are enabled.
if ( ! splms_get_setting( 'enable_related_courses', true ) ) {
	return;
}

$course_id = get_the_ID();

// Cache key for related courses post IDs.
$cache_key  = 'splms_related_courses_' . $course_id;
$cache_time = HOUR_IN_SECONDS; // Cache for 1 hour.

// Try to get cached post IDs.
$cached_post_ids = get_transient( $cache_key );
$related_query   = null;

// If cached, use cached post IDs to build query.
if ( false !== $cached_post_ids && is_array( $cached_post_ids ) && ! empty( $cached_post_ids ) ) {
	$query_args = array(
		'post_type'      => SPLMS_POST_TYPES['course'],
		'post_status'    => 'publish',
		'post__in'       => $cached_post_ids,
		'posts_per_page' => 3,
		'orderby'        => 'post__in', // Maintain cached order.
		'no_found_rows'  => true,
	);

	$related_query = new WP_Query( $query_args );
} else {
	// Fetch taxonomy terms once (reused across strategies).
	$categories = wp_get_object_terms( $course_id, SPLMS_TAXONOMIES['course_category'], array( 'fields' => 'ids' ) );
	$tags       = isset( SPLMS_TAXONOMIES['course_tag'] ) ? wp_get_object_terms( $course_id, SPLMS_TAXONOMIES['course_tag'], array( 'fields' => 'ids' ) ) : array();

	// Base query arguments (optimized for performance).
	$base_args = array(
		'post_type'              => SPLMS_POST_TYPES['course'],
		'post_status'            => 'publish',
		'posts_per_page'         => 3,
		'post__not_in'           => array( $course_id ),
		'no_found_rows'          => true, // Skip pagination count for better performance.
		'update_post_meta_cache' => false, // Skip meta cache if not needed.
		'update_post_term_cache' => false, // Skip term cache if not needed.
	);

	// Priority 1: Get related courses by category.
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		$query_args = array_merge(
			$base_args,
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Related courses query, acceptable performance trade-off.
				'tax_query' => array(
					array(
						'taxonomy' => SPLMS_TAXONOMIES['course_category'],
						'field'    => 'term_id',
						'terms'    => $categories,
					),
				),
			)
		);

		$related_query = new WP_Query( $query_args );
	}

	// Priority 2: If no results from categories, try tags.
	if ( ( ! $related_query || ! $related_query->have_posts() ) && ! empty( $tags ) && ! is_wp_error( $tags ) ) {
		wp_reset_postdata();

		$query_args = array_merge(
			$base_args,
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Related courses query, acceptable performance trade-off.
				'tax_query' => array(
					array(
						'taxonomy' => SPLMS_TAXONOMIES['course_tag'],
						'field'    => 'term_id',
						'terms'    => $tags,
					),
				),
			)
		);

		$related_query = new WP_Query( $query_args );
	}

	// Priority 3: If still no results, get recent courses (fallback).
	if ( ! $related_query || ! $related_query->have_posts() ) {
		wp_reset_postdata();

		$query_args = array_merge(
			$base_args,
			array(
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		$related_query = new WP_Query( $query_args );
	}

	// Cache the post IDs if we have posts.
	if ( $related_query && $related_query->have_posts() ) {
		$post_ids = wp_list_pluck( $related_query->posts, 'ID' );
		set_transient( $cache_key, $post_ids, $cache_time );
	}
}

// Final check: If still no courses, don't display the section.
if ( ! $related_query || ! $related_query->have_posts() ) {
	if ( $related_query ) {
		wp_reset_postdata();
	}
	return;
}
?>

<section class="splms-container course-related">
	<div class="course-related-header">
		<h2 class="course-related-title">
			<span class="title-text"><?php esc_html_e( 'Related Courses', 'skillpulse-lms' ); ?></span>
		</h2>
	</div>

	<div class="course-related-content">
		<div class="related-courses-grid">
			<?php
			while ( $related_query->have_posts() ) {
				$related_query->the_post();
				?>
				<div class="related-course-item">
					<?php splms_get_template_part( 'course/course' ); ?>
				</div>
			<?php } ?>
		</div>

		<div class="course-related-footer">
			<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="splms-btn splms-btn-outline btn-view-all-courses">
				<?php esc_html_e( 'View All Courses', 'skillpulse-lms' ); ?>
			</a>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
?>
