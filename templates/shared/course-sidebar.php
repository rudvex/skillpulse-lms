<?php
/**
 * Course Curriculum Sidebar (Shared Template)
 * Used for both Lesson and Quiz pages
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get current post ID and determine type.
$splms_current_post_id   = get_the_ID();
$splms_current_post_type = get_post_type( $splms_current_post_id );
$splms_is_lesson         = ( SPLMS_POST_TYPES['lesson'] === $splms_current_post_type );
$splms_is_quiz           = ( SPLMS_POST_TYPES['quiz'] === $splms_current_post_type );

// Get course information.
$splms_course_id            = null;
$splms_relationships_query  = SPLMS_Relationships_Query::get_instance();
$splms_parent_relationships = $splms_relationships_query->get_parents( $splms_current_post_id );

if ( ! empty( $splms_parent_relationships ) ) {
	$splms_section_id = $splms_parent_relationships[0]->parent_id;
	$splms_course_id  = SPLMS_Course_Items_Query::get_instance()->get_item_course_id( $splms_section_id );
}

$splms_course_title = '';
if ( $splms_course_id ) {
	$splms_course_title = get_the_title( $splms_course_id );
}

// Get enrollment and progress data.
$splms_current_user_id = get_current_user_id();
$splms_is_enrolled     = false;
if ( $splms_current_user_id && $splms_course_id ) {
	$splms_is_enrolled = splms_is_user_enrolled( $splms_course_id, $splms_current_user_id );
}

// Get course curriculum and progress using unified method.
$splms_curriculum    = array();
$splms_progress_data = array(
	'percentage' => 0,
	'completed'  => 0,
	'total'      => 0,
);

if ( $splms_course_id && $splms_is_enrolled ) {
	$splms_curriculum_result = splms_get_course_curriculum( $splms_course_id, $splms_current_user_id, array( 'include_stats' => true ) );

	// Transform to sidebar format.
	foreach ( $splms_curriculum_result['sections'] as $splms_section ) {
		$splms_section_items = array();

		foreach ( $splms_section['children'] as $splms_child ) {
			$splms_section_items[] = array(
				'id'        => $splms_child['id'],
				'title'     => $splms_child['title'],
				'type'      => SPLMS_POST_TYPES['lesson'] === $splms_child['type'] ? 'lesson' : 'quiz',
				'url'       => $splms_child['permalink'],
				'completed' => isset( $splms_child['completed'] ) ? $splms_child['completed'] : false,
				'locked'    => false, // TODO: Add sequential completion logic here if needed.
			);
		}

		if ( ! empty( $splms_section_items ) ) {
			$splms_curriculum[] = array(
				'title' => $splms_section['title'],
				'items' => $splms_section_items,
			);
		}
	}

	// Get unified progress data.
	$splms_progress_data = splms_get_course_progress_data( $splms_current_user_id, $splms_course_id );
}
?>

<!-- Course Curriculum Sidebar -->
<aside class="splms-course-sidebar">
	<div class="splms-course-sidebar__header">
		<h3 class="splms-course-sidebar__title">
			<svg class="splms-course-sidebar__title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M4 19.5C4 18.6716 4.67157 18 5.5 18H20.5C20.7761 18 21 18.2239 21 18.5C21 18.7761 20.7761 19 20.5 19H5.5C5.22386 19 5 19.2239 5 19.5C5 19.7761 5.22386 20 5.5 20H20.5C20.7761 20 21 20.2239 21 20.5C21 20.7761 20.7761 21 20.5 21H5.5C4.67157 21 4 20.3284 4 19.5Z" fill="currentColor"/>
				<path d="M4 15.5C4 14.6716 4.67157 14 5.5 14H20.5C20.7761 14 21 14.2239 21 14.5C21 14.7761 20.7761 15 20.5 15H5.5C5.22386 15 5 15.2239 5 15.5C5 15.7761 5.22386 16 5.5 16H20.5C20.7761 16 21 16.2239 21 16.5C21 16.7761 20.7761 17 20.5 17H5.5C4.67157 17 4 16.3284 4 15.5Z" fill="currentColor"/>
				<path d="M4 3.5C4 2.67157 4.67157 2 5.5 2H20.5C20.7761 2 21 2.22386 21 2.5C21 2.77614 20.7761 3 20.5 3H5.5C5.22386 3 5 3.22386 5 3.5C5 3.77614 5.22386 4 5.5 4H20.5C20.7761 4 21 4.22386 21 4.5C21 4.77614 20.7761 5 20.5 5H5.5C4.67157 5 4 4.32843 4 3.5Z" fill="currentColor"/>
				<path d="M2 6C2 5.44772 2.44772 5 3 5C3.55228 5 4 5.44772 4 6V18C4 18.5523 3.55228 19 3 19C2.44772 19 2 18.5523 2 18V6Z" fill="currentColor"/>
			</svg>
			<span><?php echo esc_html( $splms_course_title ? $splms_course_title : __( 'Course Content', 'skillpulse-lms' ) ); ?></span>
		</h3>
		
		<?php
		if ( $splms_is_enrolled && $splms_progress_data['total'] > 0 ) {
			splms_get_template_part(
				'shared/progress-bar',
				'',
				array(
					'splms_progress_data'    => $splms_progress_data,
					'splms_show_info'        => true,
					'splms_show_label'       => false,
					'splms_show_percentage'  => false,
					'splms_show_items_count' => true,
					'splms_css_class'        => 'splms-course-sidebar-progress',
				)
			);
		}
		?>
	</div>
	
	<?php if ( $splms_is_enrolled && ! empty( $splms_curriculum ) ) { ?>
		<div class="splms-sidebar-sections">
			<?php foreach ( $splms_curriculum as $splms_section ) { ?>
				<div class="splms-sidebar-section">
					<h4 class="splms-sidebar-section__title">
						<svg class="splms-sidebar-section__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M3 9C3 7.89543 3.89543 7 5 7H9C10.1046 7 11 7.89543 11 9V13C11 14.1046 10.1046 15 9 15H5C3.89543 15 3 14.1046 3 13V9Z" fill="currentColor" fill-opacity="0.2"/>
							<path d="M13 9C13 7.89543 13.8954 7 15 7H19C20.1046 7 21 7.89543 21 9V13C21 14.1046 20.1046 15 19 15H15C13.8954 15 13 14.1046 13 13V9Z" fill="currentColor" fill-opacity="0.2"/>
							<path d="M3 15C3 13.8954 3.89543 13 5 13H9C10.1046 13 11 13.8954 11 15V19C11 20.1046 10.1046 21 9 21H5C3.89543 21 3 20.1046 3 19V15Z" fill="currentColor" fill-opacity="0.2"/>
							<path d="M13 15C13 13.8954 13.8954 13 15 13H19C20.1046 13 21 13.8954 21 15V19C21 20.1046 20.1046 21 19 21H15C13.8954 21 13 20.1046 13 19V15Z" fill="currentColor" fill-opacity="0.2"/>
							<path d="M5 7C3.89543 7 3 7.89543 3 9V13C3 14.1046 3.89543 15 5 15H9C10.1046 15 11 14.1046 11 13V9C11 7.89543 10.1046 7 9 7H5Z" stroke="currentColor" stroke-width="2"/>
							<path d="M15 7C13.8954 7 13 7.89543 13 9V13C13 14.1046 13.8954 15 15 15H19C20.1046 15 21 14.1046 21 13V9C21 7.89543 20.1046 7 19 7H15Z" stroke="currentColor" stroke-width="2"/>
							<path d="M5 13C3.89543 13 3 13.8954 3 15V19C3 20.1046 3.89543 21 5 21H9C10.1046 21 11 20.1046 11 19V15C11 13.8954 10.1046 13 9 13H5Z" stroke="currentColor" stroke-width="2"/>
							<path d="M15 13C13.8954 13 13 13.8954 13 15V19C13 20.1046 13.8954 21 15 21H19C20.1046 21 21 20.1046 21 19V15C21 13.8954 20.1046 13 19 13H15Z" stroke="currentColor" stroke-width="2"/>
						</svg>
						<span><?php echo esc_html( $splms_section['title'] ); ?></span>
					</h4>
					<div class="splms-sidebar-section__items">
						<?php
						foreach ( $splms_section['items'] as $splms_item ) {
							$splms_is_current   = ( (int) $splms_item['id'] === (int) $splms_current_post_id );
							$splms_item_classes = array( 'splms-sidebar-item' );

							if ( $splms_is_current ) {
								$splms_item_classes[] = 'is-active';
							}

							if ( $splms_item['completed'] ) {
								$splms_item_classes[] = 'is-completed';
							}

							if ( $splms_item['locked'] ) {
								$splms_item_classes[] = 'is-locked';
							}

							$splms_item_class_string = implode( ' ', $splms_item_classes );
							?>
							<a 
								href="<?php echo esc_url( $splms_item['url'] ); ?>" 
								class="<?php echo esc_attr( $splms_item_class_string ); ?>" 
								data-item-id="<?php echo esc_attr( $splms_item['id'] ); ?>" 
								data-item-type="<?php echo esc_attr( $splms_item['type'] ); ?>"
								<?php echo esc_attr( $splms_item['locked'] ? 'tabindex="-1" aria-disabled="true"' : '' ); ?>
							>
								<span class="splms-sidebar-item__icon">
									<?php if ( 'lesson' === $splms_item['type'] ) { ?>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											<path d="M14 2V8H20" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									<?php } else { ?>
										<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
											<path d="M9.09 9C9.3251 8.33167 9.78915 7.76811 10.4 7.40913C11.0108 7.05016 11.7289 6.91894 12.4272 7.03871C13.1255 7.15849 13.7588 7.52152 14.2151 8.06353C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
											<path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
										</svg>
									<?php } ?>
								</span>
								<span class="splms-sidebar-item__content">
									<span class="splms-sidebar-item__title"><?php echo esc_html( $splms_item['title'] ); ?></span>
									<?php if ( isset( $splms_item['duration'] ) && ! empty( $splms_item['duration'] ) ) { ?>
										<span class="splms-sidebar-item__meta">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
												<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<span><?php echo esc_html( $splms_item['duration'] ); ?></span>
										</span>
									<?php } ?>
								</span>
								<span class="splms-sidebar-item__status">
									<?php if ( $splms_is_current ) { ?>
										<span class="splms-sidebar-status"><?php esc_html_e( 'Current', 'skillpulse-lms' ); ?></span>
									<?php } elseif ( $splms_item['completed'] ) { ?>
										<svg class="splms-sidebar-item__check-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									<?php } elseif ( $splms_item['locked'] ) { ?>
										<svg class="splms-sidebar-item__lock-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<rect x="5" y="11" width="14" height="10" rx="2" stroke="currentColor" stroke-width="2"/>
											<path d="M7 11V7C7 4.79086 8.79086 3 11 3H13C15.2091 3 17 4.79086 17 7V11" stroke="currentColor" stroke-width="2"/>
										</svg>
									<?php } ?>
								</span>
							</a>
						<?php } ?>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php } else { ?>
		<div class="splms-course-sidebar__notice">
			<p><?php esc_html_e( 'Enroll in the course to see the full curriculum.', 'skillpulse-lms' ); ?></p>
		</div>
	<?php } ?>
</aside>

