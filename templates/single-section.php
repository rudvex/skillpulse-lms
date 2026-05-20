<?php
/**
 * Single section template
 *
 * Modern, user-friendly section page template with improved layout,
 * enhanced content preview, and optimized purchase experience.
 *
 * This template follows WordPress theme conventions for single post pages.
 * It can be overridden by copying it to yourtheme/single-splms_section.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<div class="splms-container splms-single-section">

	<?php
	/**
	 * Hook: splms_before_single_section_content
	 *
	 * @since 1.0.0
	 */
	do_action( 'splms_before_single_section_content' );

	while ( have_posts() ) {
		the_post();
		$splms_section_id = get_the_ID();
		$splms_course_id  = SPLMS_Course_Items_Query::get_instance()->get_item_course_id( $splms_section_id );

		// Cache frequently used values to avoid repeated function calls.
		$splms_user_id                 = get_current_user_id();
		$splms_section_pricing_enabled = splms_get_setting( 'enable_section_based_pricing', false );
		$splms_course_title            = $splms_course_id ? get_the_title( $splms_course_id ) : '';
		$splms_course_permalink        = $splms_course_id ? get_permalink( $splms_course_id ) : '';
		$splms_current_permalink       = get_permalink();
		$splms_is_enrolled             = $splms_user_id && $splms_course_id ? splms_is_user_enrolled( $splms_course_id, $splms_user_id ) : false;
		$splms_section_pricing         = $splms_section_pricing_enabled ? splms_get_section_pricing_with_access( $splms_section_id, $splms_user_id ) : null;

		// If no course found, show error.
		if ( ! $splms_course_id ) {
			?>
			<div class="splms-error-message">
				<div class="error-content">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
					<h2><?php esc_html_e( 'Section Not Found', 'skillpulse-lms' ); ?></h2>
					<p><?php esc_html_e( 'This section is not associated with any course or may have been removed.', 'skillpulse-lms' ); ?></p>
					<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Browse Courses', 'skillpulse-lms' ); ?>
					</a>
				</div>
			</div>
			<?php
			continue;
		}

		$splms_course = get_post( $splms_course_id );
		?>

		<article id="section-<?php the_ID(); ?>" <?php post_class( 'splms-section-single' ); ?>>

			<!-- Modern Breadcrumb -->
			<div class="splms-breadcrumb-wrapper">
				<div class="splms-breadcrumb-container">
					<nav class="splms-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'skillpulse-lms' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="splms-breadcrumb__link">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M3 9L12 2L21 9V20C21 20.5523 20.5523 21 20 21H4C3.44772 21 3 20.5523 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<?php esc_html_e( 'Home', 'skillpulse-lms' ); ?>
						</a>
						<span class="splms-breadcrumb__separator">/</span>
						<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="splms-breadcrumb__link">
							<?php esc_html_e( 'Courses', 'skillpulse-lms' ); ?>
						</a>
						<span class="splms-breadcrumb__separator">/</span>
						<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="splms-breadcrumb__link">
							<?php echo esc_html( $splms_course_title ); ?>
						</a>
						<span class="splms-breadcrumb__separator">/</span>
						<span class="splms-breadcrumb__current"><?php the_title(); ?></span>
					</nav>
				</div>
			</div>

			<div class="splms-section-layout">
				<div class="splms-section-content">

					<!-- Enhanced Hero Section -->
					<div class="splms-section-hero">
						<div class="section-hero__header">
							<div class="section-hero__course-context">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<span class="course-label"><?php esc_html_e( 'Part of Course:', 'skillpulse-lms' ); ?></span>
								<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="course-link">
									<?php echo esc_html( $splms_course_title ); ?>
								</a>
							</div>

							<h1 class="section-hero__title"><?php the_title(); ?></h1>

							<?php if ( has_excerpt() ) : ?>
								<div class="section-hero__description">
									<?php the_excerpt(); ?>
								</div>
							<?php endif; ?>
						</div>

						<?php
						// Get section statistics.
						$splms_lessons_query   = SPLMS_Relationships_Query::get_instance();
						$splms_section_lessons = $splms_lessons_query->get_children( $splms_section_id, SPLMS_POST_TYPES['lesson'] );
						$splms_section_quizzes = $splms_lessons_query->get_children( $splms_section_id, SPLMS_POST_TYPES['quiz'] );
						$splms_lessons_count   = count( $splms_section_lessons );
						$splms_quizzes_count   = count( $splms_section_quizzes );

						// Calculate total duration from lessons.
						$splms_total_duration = 0;
						foreach ( $splms_section_lessons as $splms_lesson ) {
							$splms_lesson_duration = SPLMS_Lessons::get_instance()->get_lesson_duration( $splms_lesson->child_id );
							if ( $splms_lesson_duration ) {
								$splms_total_duration += intval( $splms_lesson_duration );
							}
						}

						// Get instructor information.
						$splms_instructor_id = get_post_field( 'post_author', $splms_course_id );
						?>

						<!-- Enhanced Stats Bar -->
						<div class="section-hero__stats">
							<?php if ( $splms_total_duration > 0 ) : ?>
								<div class="stat-item stat-item--duration">
									<div class="stat-icon">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
											<path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
										</svg>
									</div>
									<div class="stat-content">
										<span class="stat-value">
											<?php
											$splms_hours   = floor( $splms_total_duration / 60 );
											$splms_minutes = $splms_total_duration % 60;
											if ( $splms_hours > 0 ) {
												/* translators: 1: Hours, 2: Minutes. */
												printf( esc_html__( '%1$dh %2$dm', 'skillpulse-lms' ), (int) $splms_hours, (int) $splms_minutes );
											} else {
												/* translators: %d: Minutes. */
												printf( esc_html__( '%dm', 'skillpulse-lms' ), (int) $splms_minutes );
											}
											?>
										</span>
										<span class="stat-label"><?php esc_html_e( 'Duration', 'skillpulse-lms' ); ?></span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( $splms_lessons_count > 0 ) : ?>
								<div class="stat-item stat-item--lessons">
									<div class="stat-icon">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15 10L19 14M19 14L15 18M19 14H5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</div>
									<div class="stat-content">
										<span class="stat-value"><?php echo esc_html( number_format_i18n( $splms_lessons_count ) ); ?></span>
										<span class="stat-label">
											<?php echo esc_html( _n( 'Lesson', 'Lessons', $splms_lessons_count, 'skillpulse-lms' ) ); ?>
										</span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( $splms_quizzes_count > 0 ) : ?>
								<div class="stat-item stat-item--quizzes">
									<div class="stat-icon">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M9 11L12 14L22 4M21 12V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</div>
									<div class="stat-content">
										<span class="stat-value"><?php echo esc_html( number_format_i18n( $splms_quizzes_count ) ); ?></span>
										<span class="stat-label">
											<?php echo esc_html( _n( 'Quiz', 'Quizzes', $splms_quizzes_count, 'skillpulse-lms' ) ); ?>
										</span>
									</div>
								</div>
							<?php endif; ?>

							<?php if ( $splms_instructor_id ) : ?>
								<div class="stat-item stat-item--instructor">
									<div class="stat-icon">
										<?php echo get_avatar( $splms_instructor_id, 20 ); ?>
									</div>
									<div class="stat-content">
										<span class="stat-value">
											<?php echo esc_html( get_the_author_meta( 'display_name', $splms_instructor_id ) ); ?>
										</span>
										<span class="stat-label"><?php esc_html_e( 'Instructor', 'skillpulse-lms' ); ?></span>
									</div>
								</div>
							<?php endif; ?>
						</div>

						<?php if ( get_the_content() ) : ?>
							<div class="section-hero__content">
								<div class="content-wrapper">
									<?php the_content(); ?>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<!-- Enhanced Curriculum Section -->
					<div class="splms-section-curriculum">
						<div class="curriculum-header">
							<h2 class="curriculum-title">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M16 4H18C19.1046 4 20 4.89543 20 6V18C20 19.1046 19.1046 20 18 20H6C4.89543 20 4 19.1046 4 18V6C4 4.89543 4.89543 4 6 4H8M16 4V2M16 4V6M8 4V2M8 4V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M8 10H16M8 14H12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Section Curriculum', 'skillpulse-lms' ); ?>
							</h2>
							<p class="curriculum-subtitle">
								<?php
								$splms_total_items = $splms_lessons_count + $splms_quizzes_count;
								if ( $splms_total_items > 0 ) {
									printf(
										/* translators: 1: Number of items, 2: Duration */
										esc_html__( '%1$d learning activities • %2$s total length', 'skillpulse-lms' ),
										(int) $splms_total_items,
										$splms_total_duration > 0 ? sprintf(
											/* translators: Duration in minutes */
											esc_html( _n( '%d minute', '%d minutes', $splms_total_duration, 'skillpulse-lms' ) ),
											(int) $splms_total_duration
										) : esc_html__( 'Self-paced', 'skillpulse-lms' )
									);
								} else {
									esc_html_e( 'Content coming soon', 'skillpulse-lms' );
								}
								?>
							</p>
						</div>

						<?php
						// Get specific section curriculum efficiently.
						$splms_section_curriculum = splms_get_section_curriculum( $splms_section_id, $splms_user_id );

						$splms_section_data = isset( $splms_section_curriculum['section'] ) ? $splms_section_curriculum['section'] : null;
						$splms_all_items    = isset( $splms_section_curriculum['items'] ) ? $splms_section_curriculum['items'] : array();
						?>

						<?php if ( ! empty( $splms_all_items ) ) : ?>
							<div class="curriculum-content">
								<div class="curriculum-list" data-section-id="<?php echo esc_attr( $splms_section_id ); ?>">
									<?php
									foreach ( $splms_all_items as $splms_index => $splms_item ) :
										$splms_item_id      = $splms_item['id'];
										$splms_item_title   = $splms_item['title'];
										$splms_item_type    = $splms_item['type'];
										$splms_item_url     = isset( $splms_item['permalink'] ) ? $splms_item['permalink'] : get_permalink( $splms_item_id ); // Use cached permalink if available.
										$splms_is_completed = isset( $splms_item['completed'] ) && $splms_item['completed'];
										$splms_item_excerpt = isset( $splms_item['description'] ) ? $splms_item['description'] : ''; // Use cached description instead of excerpt.

										// Get access data from build_child_data method.
										$splms_has_access  = isset( $splms_item['has_access'] ) ? $splms_item['has_access'] : false;
										$splms_access_meta = isset( $splms_item['access_meta'] ) ? $splms_item['access_meta'] : array();

										// Extract access metadata for template logic.
										$splms_is_enrolled                = isset( $splms_access_meta['is_enrolled'] ) ? $splms_access_meta['is_enrolled'] : false;
										$splms_has_section_access         = isset( $splms_access_meta['has_section_access'] ) ? $splms_access_meta['has_section_access'] : false;
										$splms_is_guest_preview_available = isset( $splms_access_meta['is_guest_preview_available'] ) ? $splms_access_meta['is_guest_preview_available'] : false;
										$splms_is_section_preview_item    = isset( $splms_access_meta['is_section_preview_item'] ) ? $splms_access_meta['is_section_preview_item'] : false;
										$splms_uses_section_pricing       = isset( $splms_access_meta['uses_section_pricing'] ) ? $splms_access_meta['uses_section_pricing'] : false;

										// Get lesson duration for lessons.
										$splms_item_duration = '';
										if ( SPLMS_POST_TYPES['lesson'] === $splms_item_type ) {
											$splms_lesson_duration_meta = SPLMS_Lessons::get_instance()->get_lesson_duration( $splms_item_id );
											if ( $splms_lesson_duration_meta ) {
												$splms_item_duration = sprintf(
													/* translators: %d: Duration in minutes */
													esc_html__( '%d min', 'skillpulse-lms' ),
													(int) $splms_lesson_duration_meta
												);
											}
										}
										$splms_is_current = false; // For now, we'll set this to false, but you can add logic to determine current lesson.
										?>
										<div class="curriculum-item curriculum-item--<?php echo esc_attr( $splms_item_type ); ?> <?php echo $splms_is_completed ? 'is-completed' : ''; ?> <?php echo $splms_is_current ? 'is-current' : ''; ?> <?php echo ! $splms_has_access ? 'is-locked' : ''; ?>"
											data-item-id="<?php echo esc_attr( $splms_item_id ); ?>">

											<div class="item-main">
												<div class="item-status-border"></div>
												<div class="item-status">
													<?php if ( $splms_is_completed ) : ?>
														<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
														</svg>
													<?php elseif ( ! $splms_has_access ) : ?>
														<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
															<circle cx="12" cy="16" r="1" fill="currentColor"/>
															<path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" stroke="currentColor" stroke-width="2"/>
														</svg>
													<?php else : ?>
														<?php if ( SPLMS_POST_TYPES['lesson'] === $splms_item_type ) : ?>
															<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<polygon points="5,3 19,12 5,21" fill="currentColor"/>
															</svg>
														<?php else : ?>
															<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M9 11L12 14L22 4M21 12V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19V5C3 3.89543 3.89543 3 5 3H16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															</svg>
														<?php endif; ?>
													<?php endif; ?>
												</div>

												<div class="item-content">
													<div class="item-header">
														<h4 class="item-title"><?php echo esc_html( $splms_item_title ); ?></h4>
														<div class="item-meta">
															<span class="item-type">
																<?php
																if ( SPLMS_POST_TYPES['lesson'] === $splms_item_type ) {
																	esc_html_e( 'Lesson', 'skillpulse-lms' );
																} else {
																	esc_html_e( 'Quiz', 'skillpulse-lms' );
																}
																?>
															</span>
															<?php if ( $splms_item_duration ) : ?>
																<span class="item-duration"><?php echo esc_html( $splms_item_duration ); ?></span>
															<?php endif; ?>
														</div>
													</div>
												</div>

												<div class="item-action">
													<?php if ( $splms_is_enrolled || $splms_has_section_access ) : ?>
														<?php if ( $splms_has_access ) : ?>
															<?php if ( $splms_is_completed ) : ?>
																<span class="action-text completed"><?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?></span>
															<?php else : ?>
																<?php
																/* translators: %s: Item title */
																$splms_item_title_label = sprintf( __( 'Start %s', 'skillpulse-lms' ), $splms_item_title );
																?>
																<a href="<?php echo esc_url( $splms_item_url ); ?>" class="action-button" aria-label="<?php echo esc_attr( $splms_item_title_label ); ?>">
																	<?php esc_html_e( 'Start', 'skillpulse-lms' ); ?>
																</a>
															<?php endif; ?>
														<?php else : ?>
															<span class="action-text"><?php esc_html_e( 'Locked', 'skillpulse-lms' ); ?></span>
														<?php endif; ?>
													<?php elseif ( $splms_has_access && ( $splms_is_guest_preview_available || $splms_is_section_preview_item ) ) : ?>
														<?php
														/* translators: %s: Item title */
														$splms_item_title_label = sprintf( __( 'Preview %s', 'skillpulse-lms' ), $splms_item_title );
														?>
														<a href="<?php echo esc_url( $splms_item_url ); ?>" class="action-button preview" aria-label="<?php echo esc_attr( $splms_item_title_label ); ?>">
															<?php esc_html_e( 'Preview', 'skillpulse-lms' ); ?>
														</a>
													<?php else : ?>
														<span class="action-text"><?php esc_html_e( 'Locked', 'skillpulse-lms' ); ?></span>
													<?php endif; ?>
												</div>
											</div>

										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="curriculum-empty">
								<div class="empty-state">
									<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<h3><?php esc_html_e( 'Content Coming Soon', 'skillpulse-lms' ); ?></h3>
									<p><?php esc_html_e( 'This section is being prepared with exciting lessons and quizzes. Check back soon!', 'skillpulse-lms' ); ?></p>
								</div>
							</div>
						<?php endif; ?>
					</div>

				</div>

				<div class="splms-section-sidebar">
					<!-- Enhanced Purchase Card -->
					<div class="splms-section-purchase-card">
						<?php if ( $splms_is_enrolled ) : ?>
							<!-- Enrolled State -->
							<div class="purchase-card-enrolled">
								<div class="enrolled-badge">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span><?php esc_html_e( 'Full Course Access', 'skillpulse-lms' ); ?></span>
								</div>
								<p class="enrolled-message">
									<?php esc_html_e( 'You have complete access to this course and all its sections.', 'skillpulse-lms' ); ?>
								</p>
								<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="btn btn-primary">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										<path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<?php esc_html_e( 'Continue Course', 'skillpulse-lms' ); ?>
								</a>
							</div>

						<?php elseif ( $splms_section_pricing && $splms_section_pricing['user_has_access'] ) : ?>
							<!-- Section Owned State -->
							<div class="purchase-card-owned">
								<div class="owned-badge">
									<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12 C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
									<span><?php esc_html_e( 'Section Purchased', 'skillpulse-lms' ); ?></span>
								</div>
								<p class="owned-message">
									<?php esc_html_e( 'You have lifetime access to this section content.', 'skillpulse-lms' ); ?>
								</p>
								<?php
								// Get first lesson to start learning.
								if ( ! empty( $splms_section_lessons ) ) {
									$splms_first_lesson = reset( $splms_section_lessons );
									$splms_lesson_url   = get_permalink( $splms_first_lesson->child_id );
									?>
									<a href="<?php echo esc_url( $splms_lesson_url ); ?>" class="btn btn-primary">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<polygon points="5,3 19,12 5,21" fill="currentColor"/>
										</svg>
										<?php esc_html_e( 'Start Learning', 'skillpulse-lms' ); ?>
									</a>
								<?php } ?>
							</div>

						<?php else : ?>
							<!-- Purchase State -->
							<div class="purchase-card-header">
								<h3 class="purchase-title"><?php esc_html_e( 'Get This Section', 'skillpulse-lms' ); ?></h3>
								<p class="purchase-subtitle"><?php esc_html_e( 'Unlock all lessons and quizzes in this section', 'skillpulse-lms' ); ?></p>
							</div>

							<?php if ( $splms_section_pricing_enabled ) : ?>
								<!-- Section Pricing -->
								<div class="purchase-pricing">
									<?php if ( $splms_section_pricing && 0 === (int) $splms_section_pricing['effective_price'] ) : ?>
										<div class="price-display price-display--free">
											<span class="price-free"><?php esc_html_e( 'Free', 'skillpulse-lms' ); ?></span>
											<span class="price-subtitle"><?php esc_html_e( 'No payment required', 'skillpulse-lms' ); ?></span>
										</div>
									<?php elseif ( $splms_section_pricing ) : ?>
										<div class="price-display price-display--paid">
											<div class="price-current">
												<span class="currency-symbol">$</span>
												<span class="price-amount"><?php echo esc_html( number_format( $splms_section_pricing['effective_price'], 2 ) ); ?></span>
											</div>
											<?php if ( $splms_section_pricing['is_on_sale'] ) : ?>
												<div class="price-original">
													<span><?php echo esc_html( splms_get_price_format( $splms_section_pricing['price'] ) ); ?></span>
													<span class="sale-badge"><?php esc_html_e( 'Sale', 'skillpulse-lms' ); ?></span>
												</div>
											<?php endif; ?>
											<span class="price-subtitle"><?php esc_html_e( 'One-time payment', 'skillpulse-lms' ); ?></span>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<!-- Purchase/Access Button -->
							<?php if ( $splms_section_pricing_enabled && $splms_section_pricing && 0 === (int) $splms_section_pricing['effective_price'] ) : ?>
								<?php if ( $splms_user_id > 0 ) : ?>
									<?php
									// Get first lesson to start learning.
									if ( ! empty( $splms_section_lessons ) ) {
										$splms_first_lesson = reset( $splms_section_lessons );
										$splms_lesson_url   = get_permalink( $splms_first_lesson->child_id );
										?>
										<a href="<?php echo esc_url( $splms_lesson_url ); ?>" class="btn btn-primary">
											<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<polygon points="5,3 19,12 5,21" fill="currentColor"/>
											</svg>
											<?php esc_html_e( 'Start Learning', 'skillpulse-lms' ); ?>
										</a>
									<?php } ?>
								<?php else : ?>
									<a href="<?php echo esc_url( wp_login_url( $splms_current_permalink ) ); ?>" class="btn btn-primary">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H15M10 17L15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Log In to Access', 'skillpulse-lms' ); ?>
									</a>
								<?php endif; ?>
							<?php else : ?>
								<!-- Paid Section - Purchase Button -->
								<?php if ( $splms_user_id > 0 ) : ?>
									<?php
									$splms_purchase_url = $splms_section_pricing_enabled ?
										splms_get_section_purchase_url( $splms_section_id, $splms_course_id ) :
										$splms_course_permalink;
									$splms_button_text  = $splms_section_pricing_enabled ?
										__( 'Purchase Section', 'skillpulse-lms' ) :
										__( 'Course Detail', 'skillpulse-lms' );
									?>
									<a href="<?php echo esc_url( $splms_purchase_url ); ?>" class="btn btn-primary">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php echo esc_html( $splms_button_text ); ?>
									</a>
								<?php else : ?>
									<a href="<?php echo esc_url( wp_login_url( $splms_current_permalink ) ); ?>" class="btn btn-primary">
										<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M15 3H19C20.1046 3 21 3.89543 21 5V19C21 20.1046 20.1046 21 19 21H15M10 17L15 12L10 7M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
										<?php esc_html_e( 'Log In to Purchase', 'skillpulse-lms' ); ?>
									</a>
								<?php endif; ?>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<!-- Course Sections Navigation -->
					<div class="splms-course-navigation">
						<div class="navigation-header">
							<h3 class="navigation-title">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 3H8C9.06087 3 10.0783 3.42143 10.8284 4.17157C11.5786 4.92172 12 5.93913 12 7V21C12 20.2044 11.6839 19.4413 11.1213 18.8787C10.5587 18.3161 9.79565 18 9 18H2V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
									<path d="M22 3H16C14.9391 3 13.9217 3.42143 13.1716 4.17157C12.4214 4.92172 12 5.93913 12 7V21C12 20.2044 12.3161 19.4413 12.8787 18.8787C13.4413 18.3161 14.2044 18 15 18H22V3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
								<?php esc_html_e( 'Course Sections', 'skillpulse-lms' ); ?>
							</h3>
						</div>

						<?php
						// Get sections for navigation sidebar (lightweight).
						$splms_course_sections = splms_get_course_sections_for_navigation( $splms_course_id );
						?>

						<?php if ( ! empty( $splms_course_sections ) ) : ?>
							<div class="navigation-list">
								<?php foreach ( $splms_course_sections as $splms_nav_section ) : ?>
									<?php
									$splms_nav_section_id    = $splms_nav_section['id'];
									$splms_nav_section_title = $splms_nav_section['title'];
									$splms_nav_section_url   = $splms_nav_section['permalink'];
									$splms_nav_section_count = $splms_nav_section['item_count'];
									$splms_is_current        = intval( $splms_nav_section_id ) === intval( $splms_section_id );
									?>
									<div class="navigation-item <?php echo esc_attr( $splms_is_current ? 'is-current' : '' ); ?>">
										<div class="navigation-item-content">
											<a href="<?php echo esc_url( $splms_nav_section_url ); ?>" class="navigation-item-link">
												<span class="navigation-item-title"><?php echo esc_html( $splms_nav_section_title ); ?></span>
												<span class="navigation-item-meta">
													<?php
													/* translators: %d: Number of items. */
													printf( esc_html( _n( '%d item', '%d items', $splms_nav_section_count, 'skillpulse-lms' ) ), (int) $splms_nav_section_count );
													?>
												</span>
											</a>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<div class="navigation-empty">
								<p><?php esc_html_e( 'No sections available yet.', 'skillpulse-lms' ); ?></p>
							</div>
						<?php endif; ?>
					</div>

				</div>
			</div>

		</article>

		<?php
	}

	/**
	 * Hook: splms_after_single_section_content
	 *
	 * @since 1.0.0
	 */
	do_action( 'splms_after_single_section_content' );
	?>

</div>

<!-- Mobile Sticky Purchase Bar -->
<div class="splms-mobile-purchase-bar" style="display:none">
	<div class="mobile-purchase-content">
		<div class="mobile-purchase-info">
			<?php if ( $splms_section_pricing_enabled && $splms_section_pricing && ! $splms_section_pricing['is_free'] && $splms_section_pricing['effective_price'] > 0 ) : ?>
				<div class="mobile-price">
					<span class="mobile-price-current">$<?php echo esc_html( number_format( $splms_section_pricing['effective_price'], 2 ) ); ?></span>
					<?php if ( $splms_section_pricing['is_on_sale'] ) : ?>
						<span class="mobile-price-original">$<?php echo esc_html( number_format( $splms_section_pricing['price'], 2 ) ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="mobile-purchase-action">
			<?php if ( $splms_is_enrolled ) : ?>
				<a href="<?php echo esc_url( $splms_course_permalink ); ?>" class="btn btn-primary btn-mobile">
					<?php esc_html_e( 'Continue Course', 'skillpulse-lms' ); ?>
				</a>
			<?php elseif ( $splms_section_pricing && $splms_section_pricing['user_has_access'] && ! empty( $splms_section_lessons ) ) : ?>
				<?php
				$splms_first_lesson = reset( $splms_section_lessons );
				$splms_lesson_url   = get_permalink( $splms_first_lesson->child_id );
				?>
				<a href="<?php echo esc_url( $splms_lesson_url ); ?>" class="btn btn-primary btn-mobile">
					<?php esc_html_e( 'Start Learning', 'skillpulse-lms' ); ?>
				</a>
			<?php elseif ( $splms_user_id > 0 ) : ?>
				<?php
				$splms_purchase_url = $splms_section_pricing_enabled ?
					splms_get_section_purchase_url( $splms_section_id, $splms_course_id ) :
					$splms_course_permalink;
				$splms_button_text  = $splms_section_pricing_enabled ?
					__( 'Purchase', 'skillpulse-lms' ) :
					__( 'Enroll', 'skillpulse-lms' );
				?>
				<a href="<?php echo esc_url( $splms_purchase_url ); ?>" class="btn btn-primary btn-mobile">
					<?php echo esc_html( $splms_button_text ); ?>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( wp_login_url( $splms_current_permalink ) ); ?>" class="btn btn-primary btn-mobile">
					<?php esc_html_e( 'Log In', 'skillpulse-lms' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php get_footer(); ?>