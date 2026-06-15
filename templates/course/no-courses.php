<?php
/**
 * No courses found template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/content-no-courses.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



// Sanitize the GET array to prevent static analysis false positives.
$splms_sanitized_get = map_deep( wp_unslash( $_GET ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file reading URL parameter only.

$splms_is_search   = is_search() || ! empty( $splms_sanitized_get['course_search'] );
$splms_has_filters = ! empty( array_filter( $splms_sanitized_get ) );
?>

<div class="splms-no-courses">
	<div class="no-courses-illustration">
		<svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<!-- Background circle -->
			<circle cx="60" cy="60" r="58" fill="#F3F4F6" stroke="#E5E7EB" stroke-width="2"/>
			<!-- Magnifying glass -->
			<circle cx="50" cy="50" r="20" stroke="#9CA3AF" stroke-width="2.5" fill="none" stroke-linecap="round"/>
			<line x1="67" y1="67" x2="80" y2="80" stroke="#9CA3AF" stroke-width="2.5" stroke-linecap="round"/>
			<!-- Document/book icon -->
			<rect x="35" y="30" width="25" height="30" rx="2" fill="#D1D5DB" opacity="0.6"/>
			<line x1="40" y1="35" x2="55" y2="35" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round"/>
			<line x1="40" y1="40" x2="52" y2="40" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round"/>
			<line x1="40" y1="45" x2="50" y2="45" stroke="#9CA3AF" stroke-width="1.5" stroke-linecap="round"/>
		</svg>
	</div>

	<div class="no-courses-content">
		<?php if ( $splms_is_search ) { ?>
			<h2 class="no-courses-title">
				<?php esc_html_e( 'Oops! We couldn\'t find any matching courses', 'skillpulse-lms' ); ?>
			</h2>
			<p class="no-courses-message">
				<?php
				if ( ! empty( $splms_sanitized_get['course_search'] ) ) {
					$splms_search_text = sprintf(
						/* translators: %s: Search query. */
						esc_html__( 'No courses match your search for "%s". Try different keywords, adjust your filters, or browse our course categories below.', 'skillpulse-lms' ),
						'<strong>' . esc_html( $splms_sanitized_get['course_search'] ) . '</strong>'
					);
					echo wp_kses_post( $splms_search_text );
				} else {
					esc_html_e( 'We couldn\'t find any courses matching your search. Try different keywords, adjust your filters, or browse our course categories below.', 'skillpulse-lms' );
				}
				?>
			</p>
		<?php } elseif ( $splms_has_filters ) { ?>
			<h2 class="no-courses-title">
				<?php esc_html_e( 'No matching courses found', 'skillpulse-lms' ); ?>
			</h2>
			<p class="no-courses-message">
				<?php esc_html_e( 'No courses match your current filter settings. Try adjusting your selections or clear all filters to browse all available courses.', 'skillpulse-lms' ); ?>
			</p>
		<?php } else { ?>
			<h2 class="no-courses-title">
				<?php esc_html_e( 'No courses available yet', 'skillpulse-lms' ); ?>
			</h2>
			<p class="no-courses-message">
				<?php esc_html_e( 'We\'re working hard to bring you amazing courses. Check back soon for new learning opportunities!', 'skillpulse-lms' ); ?>
			</p>
		<?php } ?>
	</div>

	<div class="no-courses-actions">
		<?php if ( $splms_is_search || $splms_has_filters ) { ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="btn btn-primary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span><?php esc_html_e( 'View All Courses', 'skillpulse-lms' ); ?></span>
			</a>
			
			<?php if ( $splms_has_filters ) { ?>
				<?php
				$splms_clear_url = remove_query_arg( array_map( 'sanitize_key', array_keys( $splms_sanitized_get ) ) );
				?>
				<a href="<?php echo esc_url( $splms_clear_url ); ?>" class="btn btn-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span><?php esc_html_e( 'Clear Filters', 'skillpulse-lms' ); ?></span>
				</a>
			<?php } ?>
		<?php } ?>
	</div>

	<?php
	// Show course categories if available.
	$splms_categories = get_terms(
		array(
			'taxonomy'   => SPLMS_TAXONOMIES['course_category'],
			'hide_empty' => true,
			'number'     => 8,
		)
	);

	if ( ! empty( $splms_categories ) && ! is_wp_error( $splms_categories ) ) {
		?>
		<div class="browse-categories">
			<h3 class="browse-categories-title">
				<?php esc_html_e( 'Or browse by category', 'skillpulse-lms' ); ?>
			</h3>
			<div class="category-chips">
				<?php foreach ( $splms_categories as $splms_category ) { ?>
					<a href="<?php echo esc_url( get_term_link( $splms_category ) ); ?>" class="category-chip">
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
						<span><?php echo esc_html( $splms_category->name ); ?></span>
						<?php if ( $splms_category->count > 0 ) { ?>
							<span class="category-count"><?php echo esc_html( $splms_category->count ); ?></span>
						<?php } ?>
					</a>
				<?php } ?>
			</div>
		</div>
	<?php } ?>

	<?php
	/**
	 * Hook: splms_no_courses_found
	 */
	do_action( 'splms_no_courses_found' );
	?>
</div> 