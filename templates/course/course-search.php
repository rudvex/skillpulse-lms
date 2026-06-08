<?php
/**
 * Course search form template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/content-course-search.php.
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_search_query = get_search_query();
if ( ! $splms_search_query ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, reading URL parameter only.
	$splms_search_query = isset( $_GET['course_search'] ) ? sanitize_text_field( wp_unslash( $_GET['course_search'] ) ) : '';
}
?>

<div class="splms-search-toggle-wrapper">
	<!-- <button 
		type="button" 
		class="splms-search-toggle" 
		aria-label="<?php esc_attr_e( 'Search Courses', 'skillpulse-lms' ); ?>"
		aria-expanded="<?php echo ! empty( $splms_search_query ) ? 'true' : 'false'; ?>"
		title="<?php esc_attr_e( 'Search Courses', 'skillpulse-lms' ); ?>"
	>
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5S5.806 2 10.5 2S19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		</svg>
		<span class="screen-reader-text"><?php esc_html_e( 'Search Courses', 'skillpulse-lms' ); ?></span>
	</button> -->

	<div class="splms-search-panel" role="dialog" aria-hidden="<?php echo ! empty( $splms_search_query ) ? 'false' : 'true'; ?>" aria-label="<?php esc_attr_e( 'Search Courses', 'skillpulse-lms' ); ?>">
		<?php
		// Determine the form action based on context.
		$splms_form_action = get_post_type_archive_link( SPLMS_POST_TYPES['course'] );
		if ( is_tax( SPLMS_TAXONOMIES['course_category'] ) || is_tax( SPLMS_TAXONOMIES['course_tag'] ) ) {
			// Keep search within current taxonomy context.
			$splms_form_action = get_term_link( get_queried_object() );
		}
		?>
		<form class="splms-search-form" method="get" action="<?php echo esc_url( $splms_form_action ); ?>" data-expanded="<?php echo ! empty( $splms_search_query ) ? 'true' : 'false'; ?>">
			<div class="splms-search-input-wrapper">
				<input 
					type="text" 
					name="course_search" 
					id="splms-course-search-input"
					class="splms-search-input" 
					placeholder="<?php esc_attr_e( 'Search courses...', 'skillpulse-lms' ); ?>" 
					value="<?php echo esc_attr( $splms_search_query ); ?>"
					aria-label="<?php esc_attr_e( 'Search courses', 'skillpulse-lms' ); ?>"
					autocomplete="off"
				>
				<!-- <button type="button" class="splms-search-close" aria-label="<?php esc_attr_e( 'Close Search', 'skillpulse-lms' ); ?>" title="<?php esc_attr_e( 'Close Search', 'skillpulse-lms' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</button> -->
				<button type="submit" class="splms-search-submit" aria-label="<?php esc_attr_e( 'Submit Search', 'skillpulse-lms' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5S5.806 2 10.5 2S19 5.806 19 10.5Z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Search', 'skillpulse-lms' ); ?></span>
				</button>
			</div>
			<input type="hidden" name="post_type" value="<?php echo esc_attr( SPLMS_POST_TYPES['course'] ); ?>" />
			<?php
			// Preserve other query parameters.
			if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading query args for form state preservation only.
				// Sanitize the entire array to prevent static analysis false positives.
				$splms_sanitized_get = map_deep( wp_unslash( $_GET ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading query args for form state preservation only.

				foreach ( $splms_sanitized_get as $splms_key => $splms_value ) {
					if ( in_array( $splms_key, array( 'course_search', 'post_type' ), true ) ) {
						continue;
					}

					if ( is_array( $splms_value ) ) {
						foreach ( $splms_value as $splms_sub_value ) {
							echo '<input type="hidden" name="' . esc_attr( sanitize_key( $splms_key ) ) . '[]" value="' . esc_attr( $splms_sub_value ) . '" />';
						}
					} else {
						echo '<input type="hidden" name="' . esc_attr( sanitize_key( $splms_key ) ) . '" value="' . esc_attr( $splms_value ) . '" />';
					}
				}
			}
			?>
		</form>
	</div>
</div> 