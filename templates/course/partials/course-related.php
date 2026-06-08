<?php
/**
 * Single course related courses template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/single-course-related.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



// Check if related courses are enabled.
if ( ! splms_get_setting( 'enable_related_courses', true ) ) {
	return;
}

$splms_course_id = get_the_ID();

// Cache key for related courses post IDs.
$splms_cache_key  = 'splms_related_courses_' . $splms_course_id;
$splms_cache_time = HOUR_IN_SECONDS; // Cache for 1 hour.

// Try to get cached post IDs.
$splms_cached_post_ids = get_transient( $splms_cache_key );
$splms_related_query   = null;

// If cached, use cached post IDs to build query.
if ( false !== $splms_cached_post_ids && is_array( $splms_cached_post_ids ) && ! empty( $splms_cached_post_ids ) ) {
	$splms_query_args = array(
		'post_type'      => SPLMS_POST_TYPES['course'],
		'post_status'    => 'publish',
		'post__in'       => $splms_cached_post_ids,
		'posts_per_page' => 3,
		'orderby'        => 'post__in', // Maintain cached order.
		'no_found_rows'  => true,
	);

	$splms_related_query = new WP_Query( $splms_query_args );
} else {
	// Fetch taxonomy terms once (reused across strategies).
	$splms_categories = wp_get_object_terms( $splms_course_id, SPLMS_TAXONOMIES['course_category'], array( 'fields' => 'ids' ) );
	$splms_tags       = isset( SPLMS_TAXONOMIES['course_tag'] ) ? wp_get_object_terms( $splms_course_id, SPLMS_TAXONOMIES['course_tag'], array( 'fields' => 'ids' ) ) : array();

	// Base query arguments (optimized for performance).
	$splms_base_args = array(
		'post_type'              => SPLMS_POST_TYPES['course'],
		'post_status'            => 'publish',
		'posts_per_page'         => 3,
		// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Single post exclusion; negligible performance impact.
		'post__not_in'           => array( $splms_course_id ),
		'no_found_rows'          => true, // Skip pagination count for better performance.
		'update_post_meta_cache' => false, // Skip meta cache if not needed.
		'update_post_term_cache' => false, // Skip term cache if not needed.
	);

	// Priority 1: Get related courses by category.
	if ( ! empty( $splms_categories ) && ! is_wp_error( $splms_categories ) ) {
		$splms_query_args = array_merge(
			$splms_base_args,
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Related courses query, acceptable performance trade-off.
				'tax_query' => array(
					array(
						'taxonomy' => SPLMS_TAXONOMIES['course_category'],
						'field'    => 'term_id',
						'terms'    => $splms_categories,
					),
				),
			)
		);

		$splms_related_query = new WP_Query( $splms_query_args );
	}

	// Priority 2: If no results from categories, try tags.
	if ( ( ! $splms_related_query || ! $splms_related_query->have_posts() ) && ! empty( $splms_tags ) && ! is_wp_error( $splms_tags ) ) {
		wp_reset_postdata();

		$splms_query_args = array_merge(
			$splms_base_args,
			array(
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query, PluginCheck.Security.DirectDB.UnescapedDBParameter -- Related courses query, acceptable performance trade-off.
				'tax_query' => array(
					array(
						'taxonomy' => SPLMS_TAXONOMIES['course_tag'],
						'field'    => 'term_id',
						'terms'    => $splms_tags,
					),
				),
			)
		);

		$splms_related_query = new WP_Query( $splms_query_args );
	}

	// Priority 3: If still no results, get recent courses (fallback).
	if ( ! $splms_related_query || ! $splms_related_query->have_posts() ) {
		wp_reset_postdata();

		$splms_query_args = array_merge(
			$splms_base_args,
			array(
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		$splms_related_query = new WP_Query( $splms_query_args );
	}

	// Cache the post IDs if we have posts.
	if ( $splms_related_query && $splms_related_query->have_posts() ) {
		$splms_post_ids = wp_list_pluck( $splms_related_query->posts, 'ID' );
		set_transient( $splms_cache_key, $splms_post_ids, $splms_cache_time );
	}
}

// Final check: If still no courses, don't display the section.
if ( ! $splms_related_query || ! $splms_related_query->have_posts() ) {
	if ( $splms_related_query ) {
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
			while ( $splms_related_query->have_posts() ) {
				$splms_related_query->the_post();
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
