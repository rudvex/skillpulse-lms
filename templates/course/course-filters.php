<?php
/**
 * Course filters template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/content-course-filters.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template file, nonce verification handled at higher level.
$splms_current_difficulty      = isset( $_GET['difficulty'] ) ? sanitize_text_field( wp_unslash( $_GET['difficulty'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$splms_current_learning_method = isset( $_GET['learning_method'] ) ? sanitize_text_field( wp_unslash( $_GET['learning_method'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$splms_current_sort            = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<div class="splms-course-filters">
	
	<form class="course-filters-form" method="get">
		<!-- Difficulty Filter -->
		<div class="filter-group filter-pill">
			<select name="difficulty" id="difficulty-filter" class="filter-select">
				<option value=""><?php esc_html_e( 'Filter by Level', 'skillpulse-lms' ); ?></option>
				<option value="beginner" <?php selected( $splms_current_difficulty, 'beginner' ); ?>><?php esc_html_e( 'Beginner', 'skillpulse-lms' ); ?></option>
				<option value="intermediate" <?php selected( $splms_current_difficulty, 'intermediate' ); ?>><?php esc_html_e( 'Intermediate', 'skillpulse-lms' ); ?></option>
				<option value="advanced" <?php selected( $splms_current_difficulty, 'advanced' ); ?>><?php esc_html_e( 'Advanced', 'skillpulse-lms' ); ?></option>
			</select>
		</div>

		<!-- Learning Method Filter -->
		<div class="filter-group filter-pill">
			<select name="learning_method" id="learning-method-filter" class="filter-select">
				<option value=""><?php esc_html_e( 'Learning Method', 'skillpulse-lms' ); ?></option>
				<option value="video" <?php selected( $splms_current_learning_method, 'video' ); ?>><?php esc_html_e( 'Video-Based Learning', 'skillpulse-lms' ); ?></option>
				<option value="text" <?php selected( $splms_current_learning_method, 'text' ); ?>><?php esc_html_e( 'Text & Reading Materials', 'skillpulse-lms' ); ?></option>
				<option value="interactive" <?php selected( $splms_current_learning_method, 'interactive' ); ?>><?php esc_html_e( 'Interactive Content', 'skillpulse-lms' ); ?></option>
				<option value="project" <?php selected( $splms_current_learning_method, 'project' ); ?>><?php esc_html_e( 'Project-Based Learning', 'skillpulse-lms' ); ?></option>
			</select>
		</div>

		<!-- Sort Filter -->
		<div class="filter-group filter-pill">
			<select name="orderby" id="sort-filter" class="filter-select">
				<option value=""><?php esc_html_e( 'Sort by', 'skillpulse-lms' ); ?></option>
				<option value="title" <?php selected( $splms_current_sort, 'title' ); ?>><?php esc_html_e( 'Title A-Z', 'skillpulse-lms' ); ?></option>
				<option value="date" <?php selected( $splms_current_sort, 'date' ); ?>><?php esc_html_e( 'Newest First', 'skillpulse-lms' ); ?></option>
				<option value="menu_order" <?php selected( $splms_current_sort, 'menu_order' ); ?>><?php esc_html_e( 'Featured', 'skillpulse-lms' ); ?></option>
			</select>
		</div>

		<!-- Hidden fields to preserve other parameters. -->
		<input type="hidden" name="post_type" value="<?php echo esc_attr( SPLMS_POST_TYPES['course'] ); ?>" />
		<?php
		if ( ! empty( $_GET ) ) {
			// Sanitize the entire array to prevent static analysis false positives.
			$sanitized_get = map_deep( wp_unslash( $_GET ), 'sanitize_text_field' );
			foreach ( $sanitized_get as $splms_key => $splms_value ) {
				if ( in_array( $splms_key, array( 'difficulty', 'learning_method', 'orderby', 'post_type' ), true ) ) {
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

		<!-- Auto-submit on change, Apply button hidden unless needed -->
		<!-- <?php if ( $splms_current_difficulty || $splms_current_learning_method || $splms_current_sort ) { ?>
			<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="filter-clear-btn">
				<?php esc_html_e( 'Clear', 'skillpulse-lms' ); ?>
			</a>
		<?php } ?> -->
	</form>
</div>
