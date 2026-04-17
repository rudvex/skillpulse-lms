<?php
/**
 * Course sidebar filters template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/content-course-sidebar-filters.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



// Get settings to check if features are enabled.
$splms_categories_enabled = splms_get_setting( 'course_categories_enabled', true );
$splms_tags_enabled       = splms_get_setting( 'course_tags_enabled', true );

// Only fetch categories if enabled.
$splms_categories = array();
if ( $splms_categories_enabled ) {
	$splms_categories = get_terms(
		array(
			'taxonomy'   => SPLMS_TAXONOMIES['course_category'],
			'hide_empty' => true,
		)
	);
}

// Only fetch tags if enabled.
$splms_tags = array();
if ( $splms_tags_enabled ) {
	$splms_tags = get_terms(
		array(
			'taxonomy'   => SPLMS_TAXONOMIES['course_tag'],
			'hide_empty' => true,
		)
	);
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Template file, nonce verification handled at higher level. Input is sanitized and unslashed via array_map.
$splms_selected_categories = isset( $_GET['course_category'] ) ? array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) $_GET['course_category'] ) ) : array();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized,WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- Template file, nonce verification handled at higher level. Input is sanitized and unslashed via array_map.
$splms_selected_tags = isset( $_GET['course_tag'] ) ? array_map( 'sanitize_text_field', array_map( 'wp_unslash', (array) $_GET['course_tag'] ) ) : array();
?>

<?php
// Determine sidebar state for CSS classes.
$splms_total_filter_sections = 0;
if ( $splms_categories_enabled && ! empty( $splms_categories ) && ! is_wp_error( $splms_categories ) ) {
	++$splms_total_filter_sections;
}
if ( $splms_tags_enabled && ! empty( $splms_tags ) && ! is_wp_error( $splms_tags ) ) {
	++$splms_total_filter_sections;
}

$splms_sidebar_classes = array( 'splms-sidebar-filters' );
if ( $splms_total_filter_sections <= 2 ) {
	$splms_sidebar_classes[] = 'minimal-filters';
}
if ( 1 === $splms_total_filter_sections ) {
	$splms_sidebar_classes[] = 'single-filter';
}
?>

<div class="<?php echo esc_attr( implode( ' ', $splms_sidebar_classes ) ); ?>">
	<button class="splms-filter-close-btns">
		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
			<g clip-path="url(#clip0_199_30)">
				<path d="M18 6L6 18" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				<path d="M6 6L18 18" stroke="#1A1A1A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
			</g>
			<defs>
				<clipPath id="clip0_199_30">
					<rect width="24" height="24" fill="white" />
				</clipPath>
			</defs>
		</svg>
	</button>
	<div class="sidebar-filter-header">
		<h3><?php esc_html_e( 'Filter Courses', 'skillpulse-lms' ); ?></h3>
		<?php if ( ! empty( array_filter( array_merge( $splms_selected_categories, $splms_selected_tags ) ) ) ) { ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="clear-all-filters">
				<?php esc_html_e( 'Clear', 'skillpulse-lms' ); ?>
			</a>
		<?php } ?>
	</div>
	<div class="archive-controls-right">
		<?php splms_get_template_part( 'course/course-filters' ); ?>
	</div>
	<?php
	// Check if we have any filters to show.
	$splms_has_filters = ( $splms_categories_enabled && ! empty( $splms_categories ) && ! is_wp_error( $splms_categories ) ) ||
		( $splms_tags_enabled && ! empty( $splms_tags ) && ! is_wp_error( $splms_tags ) );

	if ( ! $splms_has_filters ) {
		?>
		<div class="empty-filters-message">
			<div class="empty-icon">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M3 4C3 3.44772 3.44772 3 4 3H20C20.5523 3 21 3.44772 21 4C21 4.55228 20.5523 5 20 5H4C3.44772 5 3 4.55228 3 4Z" fill="currentColor" />
					<path d="M3 12C3 11.4477 3.44772 11 4 11H20C20.5523 11 21 11.4477 21 12C21 12.5523 20.5523 13 20 13H4C3.44772 13 3 12.5523 3 12Z" fill="currentColor" />
					<path d="M3 20C3 19.4477 3.44772 19 4 19H20C20.5523 19 21 19.4477 21 20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20Z" fill="currentColor" />
				</svg>
			</div>
			<p><?php esc_html_e( 'No additional filters available', 'skillpulse-lms' ); ?></p>
		</div>
	<?php } else { ?>

		<form class="sidebar-filters-form" method="get">
			<?php if ( $splms_categories_enabled && ! empty( $splms_categories ) && ! is_wp_error( $splms_categories ) ) { ?>
				<div class="filter-section filter-accordion">
					<div class="filter-section-header" role="button" tabindex="0" aria-expanded="true">
						<h4 class="filter-section-title">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="filter-icon">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
									stroke-linejoin="round" />
							</svg>
							<?php esc_html_e( 'Categories', 'skillpulse-lms' ); ?>
						</h4>
						<div class="filter-header-right">
							<span class="filter-count-badge"><?php echo count( $splms_categories ); ?></span>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="accordion-arrow">
								<path fill="none" stroke="#343a40" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2 5 6 6 6-6" />
							</svg>
						</div>
					</div>

					<div class="filter-section-content">
						<div class="filter-scrollable-container" id="categories-list" data-items-per-load="8" data-total-items="<?php echo count( $splms_categories ); ?>">
							<div class="filter-options">
								<?php foreach ( $splms_categories as $splms_index => $splms_category ) { ?>
									<label class="filter-checkbox-label" data-item-index="<?php echo esc_attr( $splms_index ); ?>">
										<input
											type="checkbox"
											name="course_category[]"
											value="<?php echo esc_attr( $splms_category->slug ); ?>"
											<?php checked( in_array( $splms_category->slug, $splms_selected_categories, true ) ); ?>
											class="filter-checkbox">
										<span class="checkmark"></span>
										<span class="filter-option-text">
											<?php echo esc_html( $splms_category->name ); ?>
											<span class="filter-count">(<?php echo esc_html( $splms_category->count ); ?>)</span>
										</span>
									</label>
								<?php } ?>
							</div>

							<?php if ( count( $splms_categories ) > 8 ) { ?>
								<div class="load-more-container">
									<button type="button" class="load-more-btn" data-target="categories-list">
										<span class="load-more-text"><?php esc_html_e( 'Load More', 'skillpulse-lms' ); ?></span>
									</button>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>

			<?php if ( $splms_tags_enabled && ! empty( $splms_tags ) && ! is_wp_error( $splms_tags ) ) { ?>
				<div class="filter-section filter-accordion">
					<div class="filter-section-header" role="button" tabindex="0" aria-expanded="true">
						<h4 class="filter-section-title">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="filter-icon">
								<path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 0 1 0 2.828l-7 7a2 2 0 0 1-2.828 0l-7-7A1.994 1.994 0 0 1 3 12V7a4 4 0 0 1 4-4z"
									stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<?php esc_html_e( 'Tags', 'skillpulse-lms' ); ?>
						</h4>
						<div class="filter-header-right">
							<span class="filter-count-badge"><?php echo count( $splms_tags ); ?></span>
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" class="accordion-arrow">
								<path fill="none" stroke="#343a40" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m2 5 6 6 6-6" />
							</svg>
						</div>
					</div>
					<div class="filter-section-content">


						<div class="filter-scrollable-container filter-tags-container" id="tags-list" data-items-per-load="12" data-total-items="<?php echo count( $splms_tags ); ?>">
							<div class="filter-options filter-tags">
								<?php foreach ( $splms_tags as $splms_tag_index => $splms_tag_item ) { ?>
									<label class="filter-tag-label" data-item-index="<?php echo esc_attr( $splms_tag_index ); ?>">
										<input
											type="checkbox"
											name="course_tag[]"
											value="<?php echo esc_attr( $splms_tag_item->slug ); ?>"
											<?php checked( in_array( $splms_tag_item->slug, $splms_selected_tags, true ) ); ?>
											class="filter-tag-checkbox">
										<span class="tag-text"><?php echo esc_html( $splms_tag_item->name ); ?></span>
									</label>
								<?php } ?>
							</div>

							<?php if ( count( $splms_tags ) > 12 ) { ?>
								<div class="load-more-container">
									<button type="button" class="load-more-btn" data-target="tags-list">
										<span class="load-more-text"><?php esc_html_e( 'Load More', 'skillpulse-lms' ); ?></span>
									</button>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>

			<!-- Hidden fields to preserve other parameters -->
			<?php
			// Only add post_type parameter if NOT already on course archive page.
			if ( ! is_post_type_archive( SPLMS_POST_TYPES['course'] ) ) {
				?>
				<input type="hidden" name="post_type" value="<?php echo esc_attr( SPLMS_POST_TYPES['course'] ); ?>" />
				<?php
			}
			?>
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, nonce verification handled at higher level.
			if ( ! empty( $_GET ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, nonce verification handled at higher level.
				foreach ( $_GET as $splms_key => $splms_value ) {
					if ( in_array( $splms_key, array( 'course_category', 'course_tag', 'post_type' ), true ) ) {
						continue;
					}
					if ( is_array( $splms_value ) ) {
						foreach ( $splms_value as $splms_sub_value ) {
							echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( wp_unslash( $splms_key ) ) ) . '[]" value="' . esc_attr( sanitize_text_field( wp_unslash( $splms_sub_value ) ) ) . '" />';
						}
					} else {
						echo '<input type="hidden" name="' . esc_attr( sanitize_text_field( wp_unslash( $splms_key ) ) ) . '" value="' . esc_attr( sanitize_text_field( wp_unslash( $splms_value ) ) ) . '" />';
					}
				}
			}
			?>

			<div class="filter-actions">
				<button type="submit" class="filter-apply-btn btn btn-primary">
					<?php esc_html_e( 'Apply Filters', 'skillpulse-lms' ); ?>
				</button>
			</div>
		</form>

		<?php
	} // End $has_filters check
	?>
</div>