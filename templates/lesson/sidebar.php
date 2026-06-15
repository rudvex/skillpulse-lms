<?php
/**
 * Lesson Fullscreen Sidebar
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/lesson/sidebar.php
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get lesson and course data.
$splms_lesson_id = get_the_ID();
$splms_user_id   = get_current_user_id();
$splms_course_id = splms_get_lesson_course( $splms_lesson_id );

if ( ! $splms_course_id ) {
	return;
}

// Get course info.
$splms_course_title = get_the_title( $splms_course_id );

// Get unified progress data.
$splms_progress_data = array(
	'percentage' => 0,
	'completed'  => 0,
	'total'      => 0,
);
if ( $splms_user_id ) {
	// Get unified progress data.
	$splms_progress_data = splms_get_course_progress_data( $splms_user_id, $splms_course_id );
}

// Get course curriculum using centralized function.
$splms_curriculum_result = splms_get_course_curriculum( $splms_course_id, $splms_user_id );

$splms_course_sections = isset( $splms_curriculum_result['sections'] ) ? $splms_curriculum_result['sections'] : array();
?>

<aside class="splms-fullscreen-sidebar" role="navigation" aria-label="<?php esc_attr_e( 'Course curriculum', 'skillpulse-lms' ); ?>">
	<div class="splms-sidebar-inner">
		<!-- Progress Summary Card -->
		<div class="splms-progress-summary-card">
			<div class="splms-progress-stats">
				<div class="splms-progress-stat">
					<div class="splms-progress-icon">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
							<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
						</svg>
					</div>
					<div class="splms-progress-text">
						<div class="splms-progress-value"><?php echo esc_html( round( $splms_progress_data['percentage'], 1 ) ); ?>%</div>
						<div class="splms-progress-label"><?php esc_html_e( 'Complete', 'skillpulse-lms' ); ?></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Course Content -->
		<div class="splms-curriculum-container">
			<h3 class="splms-curriculum-heading"><?php esc_html_e( 'COURSE CONTENT', 'skillpulse-lms' ); ?></h3>

			<div class="splms-curriculum-list">
				<?php
				if ( ! empty( $splms_course_sections ) ) :
					foreach ( $splms_course_sections as $splms_section ) :
						$splms_section_id    = $splms_section['id'];
						$splms_section_title = $splms_section['title'];
						$splms_children      = isset( $splms_section['children'] ) ? $splms_section['children'] : array();

						// Count items and completed.
						$splms_total_items     = count( $splms_children );
						$splms_completed_count = 0;
						foreach ( $splms_children as $splms_child ) {
							if ( isset( $splms_child['completed'] ) && $splms_child['completed'] ) {
								++$splms_completed_count;
							}
						}

						// Section expanded by default if it contains current lesson.
						$splms_has_current_lesson = false;
						foreach ( $splms_children as $splms_child ) {
							if ( intval( $splms_child['id'] ) === intval( $splms_lesson_id ) ) {
								$splms_has_current_lesson = true;
								break;
							}
						}
						$splms_is_expanded = $splms_has_current_lesson ? 'true' : 'false';
						?>

						<div class="splms-curriculum-section <?php echo esc_attr( $splms_has_current_lesson ? 'is-expanded' : '' ); ?>" data-section-id="<?php echo esc_attr( $splms_section_id ); ?>">
							<!-- Section Header -->
							<button type="button" class="splms-section-header" aria-expanded="<?php echo esc_attr( $splms_is_expanded ); ?>">
								<div class="splms-section-toggle">
									<svg class="splms-section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</div>
								<div class="splms-section-info">
									<span class="splms-section-icon-type">
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
									<span class="splms-section-title"><?php echo esc_html( $splms_section_title ); ?></span>
									<span class="splms-section-count">(<?php echo esc_html( $splms_total_items ); ?>)</span>
								</div>
							</button>

							<!-- Section Items -->
							<div class="splms-section-items">
								<?php
								foreach ( $splms_children as $splms_child ) :
									$splms_item_id    = $splms_child['id'];
									$splms_item_type  = $splms_child['type'];
									$splms_item_title = $splms_child['title'];
									$splms_item_url   = $splms_child['permalink'];

									// Check if item is completed.
									$splms_is_item_completed = isset( $splms_child['completed'] ) ? $splms_child['completed'] : false;

									// Check if item is current.
									$splms_is_current = ( intval( $splms_item_id ) === intval( $splms_lesson_id ) );

									// Check if user can access (for locked state).
									$splms_can_access = isset( $splms_child['has_access'] ) ? $splms_child['has_access'] : true;

									// Determine icon.
									$splms_item_icon = '';
									if ( SPLMS_POST_TYPES['lesson'] === $splms_item_type ) {
										$splms_item_icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
									} else {
										$splms_item_icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 11L12 14L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
									}

									$splms_item_classes = 'splms-curriculum-item';
									if ( $splms_is_current ) {
										$splms_item_classes .= ' is-current';
									}
									if ( $splms_is_item_completed ) {
										$splms_item_classes .= ' is-completed';
									}
									if ( ! $splms_can_access ) {
										$splms_item_classes .= ' is-locked';
									}
									?>

									<a href="<?php echo esc_url( $splms_item_url ); ?>" class="<?php echo esc_attr( $splms_item_classes ); ?>" <?php echo ! $splms_can_access ? 'aria-disabled="true"' : ''; ?>>
										<span class="splms-item-icon">
											<?php echo wp_kses_post( $splms_item_icon ); ?>
										</span>
										<span class="splms-item-title"><?php echo esc_html( $splms_item_title ); ?></span>
										<span class="splms-item-status">
											<?php if ( $splms_is_item_completed ) : ?>
												<svg class="splms-check-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
											<?php elseif ( ! $splms_can_access ) : ?>
												<svg class="splms-lock-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/>
													<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
											<?php endif; ?>
										</span>
									</a>

								<?php endforeach; ?>
							</div>
						</div>

					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</div>

		<!-- Collapse Toggle -->
		<button type="button" class="splms-sidebar-collapse-toggle" aria-label="<?php esc_attr_e( 'Collapse sidebar', 'skillpulse-lms' ); ?>">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none"
				xmlns="http://www.w3.org/2000/svg">
				<path d="M9 18L15 12L9 6"
						stroke="currentColor"
						stroke-width="2"
						stroke-linecap="round"
						stroke-linejoin="round"/>
			</svg>
			<span><?php esc_html_e( 'Collapse', 'skillpulse-lms' ); ?></span>
		</button>

	</div>
</aside>
