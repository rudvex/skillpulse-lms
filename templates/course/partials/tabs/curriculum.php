<?php
/**
 * Course Curriculum Tab
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/curriculum.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id       = get_the_ID();
$splms_current_user_id = get_current_user_id();
$splms_is_enrolled     = false;
$splms_user_progress   = array();

if ( $splms_current_user_id ) {
	// Use the new unified enrollment check function.
	$splms_is_enrolled = splms_is_user_enrolled( $splms_course_id, $splms_current_user_id );

	if ( $splms_is_enrolled ) {
		// Calculate progress from database tables.
		$splms_lessons_instance = SPLMS_Lessons::get_instance();
		$splms_progress_data    = $splms_lessons_instance ? $splms_lessons_instance->calculate_course_progress( $splms_current_user_id, $splms_course_id ) : array();

		$splms_user_progress = array(
			'percentage'        => $splms_progress_data['percentage'],
			'completed_lessons' => $splms_progress_data['completed_lessons'] + $splms_progress_data['passed_quizzes'],
			'total_lessons'     => $splms_progress_data['total_items'],
		);
	}
}

// Check for live class schedule.
$splms_scheduling_settings = get_post_meta( $splms_course_id, '_splms_course_scheduling_settings', true );
$splms_delivery_mode       = is_array( $splms_scheduling_settings ) && isset( $splms_scheduling_settings['course_delivery'] ) ? $splms_scheduling_settings['course_delivery'] : 'self_paced';
$splms_live_schedule       = ( 'live' === $splms_delivery_mode && is_array( $splms_scheduling_settings ) && isset( $splms_scheduling_settings['live_class_schedule'] ) ) ? $splms_scheduling_settings['live_class_schedule'] : array();

// Get course curriculum using centralized function with built-in access control.
$splms_curriculum_result = splms_get_course_curriculum( $splms_course_id, $splms_current_user_id );

$splms_curriculum_data = isset( $splms_curriculum_result['sections'] ) ? $splms_curriculum_result['sections'] : array();
$splms_total_lessons   = isset( $splms_curriculum_result['stats']['total_lessons'] ) ? $splms_curriculum_result['stats']['total_lessons'] : 0;
$splms_total_quizzes   = isset( $splms_curriculum_result['stats']['total_quizzes'] ) ? $splms_curriculum_result['stats']['total_quizzes'] : 0;

// Check if this course uses section-based pricing for display purposes.
$splms_uses_section_pricing = splms_course_uses_section_pricing( $splms_course_id );

// Get user's purchased sections for this course for display purposes.
$splms_purchased_sections = array();
if ( $splms_uses_section_pricing && $splms_current_user_id ) {
	$splms_purchased_sections = splms_get_user_purchased_sections( $splms_course_id, $splms_current_user_id );
}
?>

<div class="course-content-tabs">
	<div class="course-content-section course-curriculum">
		<h2 class="section-title">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
						stroke="currentColor" stroke-width="1.5"/>
			</svg>
			<?php esc_html_e( 'Course Curriculum', 'skillpulse-lms' ); ?>
		</h2>
		<div class="section-content">
			<?php if ( ! empty( $splms_live_schedule ) ) : ?>
				<!-- Live Class Schedule -->
				<div class="splms-live-schedule">
					<h3 class="splms-live-schedule__title">
						<span class="dashicons dashicons-video-alt2"></span>
						<?php esc_html_e( 'Live Class Schedule', 'skillpulse-lms' ); ?>
					</h3>
					<div class="splms-live-schedule__sessions">
						<?php
						$splms_now = time();
						foreach ( $splms_live_schedule as $splms_session ) :
							$splms_s_date     = isset( $splms_session['session_date'] ) ? $splms_session['session_date'] : '';
							$splms_s_title    = isset( $splms_session['session_title'] ) ? $splms_session['session_title'] : '';
							$splms_s_duration = isset( $splms_session['session_duration'] ) ? absint( $splms_session['session_duration'] ) : 60;
							$splms_s_join_url = isset( $splms_session['zoom_join_url'] ) ? $splms_session['zoom_join_url'] : '';
							$splms_s_rec_url  = isset( $splms_session['zoom_recording_url'] ) ? $splms_session['zoom_recording_url'] : '';
							$splms_s_ts       = ! empty( $splms_s_date ) ? strtotime( $splms_s_date ) : 0;
							$splms_s_is_past  = $splms_s_ts > 0 && $splms_s_ts < $splms_now;

							if ( empty( $splms_s_date ) ) {
								continue;
							}
							?>
							<div class="splms-live-schedule__session <?php echo esc_attr( $splms_s_is_past ? 'is-past' : 'is-upcoming' ); ?>">
								<div class="splms-live-schedule__session-info">
									<?php if ( ! empty( $splms_s_title ) ) : ?>
										<strong><?php echo esc_html( $splms_s_title ); ?></strong>
									<?php endif; ?>
									<span class="splms-live-schedule__session-date">
										<?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $splms_s_ts ) ); ?>
									</span>
									<span class="splms-live-schedule__session-duration">
										<?php
										/* translators: %d: Session duration in minutes. */
										printf( esc_html__( '%d min', 'skillpulse-lms' ), absint( $splms_s_duration ) );
										?>
									</span>
								</div>
								<div class="splms-live-schedule__session-action">
									<?php if ( ! $splms_s_is_past && ! empty( $splms_s_join_url ) && $splms_is_enrolled ) : ?>
										<a href="<?php echo esc_url( $splms_s_join_url ); ?>" target="_blank" rel="noopener noreferrer" class="splms-btn splms-btn-sm splms-btn-primary">
											<?php esc_html_e( 'Join', 'skillpulse-lms' ); ?>
										</a>
									<?php elseif ( $splms_s_is_past && ! empty( $splms_s_rec_url ) ) : ?>
										<a href="<?php echo esc_url( $splms_s_rec_url ); ?>" target="_blank" rel="noopener noreferrer" class="splms-btn splms-btn-sm splms-btn-secondary">
											<?php esc_html_e( 'Recording', 'skillpulse-lms' ); ?>
										</a>
									<?php elseif ( $splms_s_is_past ) : ?>
										<span class="splms-badge splms-badge--muted"><?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?></span>
									<?php else : ?>
										<span class="splms-badge splms-badge--info"><?php esc_html_e( 'Upcoming', 'skillpulse-lms' ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<!-- Curriculum Overview - Minimal -->
			<div class="curriculum-overview">
				<div class="curriculum-stats-minimal">
					<span class="stat-text"><?php echo count( $splms_curriculum_data ); ?> <?php esc_html_e( 'Sections', 'skillpulse-lms' ); ?></span>
					<span class="stat-separator">•</span>
					<span class="stat-text"><?php echo esc_html( (int) $splms_total_lessons ); ?> <?php esc_html_e( 'Lessons', 'skillpulse-lms' ); ?></span>
					<span class="stat-separator">•</span>
					<span class="stat-text"><?php echo esc_html( (int) $splms_total_quizzes ); ?> <?php esc_html_e( 'Quizzes', 'skillpulse-lms' ); ?></span>
					<?php if ( $splms_is_enrolled && ! empty( $splms_user_progress ) ) { ?>
						<span class="stat-separator">•</span>
						<span class="stat-text stat-progress"><?php echo esc_html( round( $splms_user_progress['percentage'], 1 ) ); ?>% <?php esc_html_e( 'Complete', 'skillpulse-lms' ); ?></span>
					<?php } ?>
				</div>
			</div>

			<?php if ( ! empty( $splms_curriculum_data ) ) { ?>
				<div class="curriculum-content">
					<?php
					foreach ( $splms_curriculum_data as $splms_section_index => $splms_section ) {
						// Use standardized access control data from curriculum function.
						$splms_has_section_access = $splms_section['has_access'];
						$splms_section_is_locked  = $splms_section['is_locked'];

						// Get section pricing data for display purposes.
						$splms_section_pricing      = null;
						$splms_show_purchase_btn    = false;
						$splms_section_is_purchased = false;

						if ( $splms_uses_section_pricing ) {
							$splms_section_pricing = splms_get_section_pricing_with_access( $splms_section['id'], $splms_current_user_id );

							// Check if this specific section is purchased (not full course enrollment).
							$splms_section_is_purchased = ! $splms_is_enrolled && in_array( (int) $splms_section['id'], $splms_purchased_sections, true );

							// Show purchase button if section is locked and not free (including 0 price).
							$splms_show_purchase_btn = $splms_section_is_locked && ! $splms_section_pricing['is_free'] && $splms_section_pricing['effective_price'] > 0;
						}

						$splms_section_classes = 'curriculum-section-minimal';
						if ( $splms_section_is_locked ) {
							$splms_section_classes .= ' section-locked';
						}
						if ( $splms_section_is_purchased ) {
							$splms_section_classes .= ' section-purchased';
						}
						?>
						<div class="<?php echo esc_attr( $splms_section_classes ); ?>" data-section-id="<?php echo esc_attr( $splms_section['id'] ); ?>">
							<div class="section-header-minimal">
								<div class="section-header-main">
									<span class="section-number-minimal"><?php echo esc_html( $splms_section_index + 1 ); ?></span>
									<div class="section-info-minimal">
										<h3 class="section-title-minimal"><?php echo esc_html( $splms_section['title'] ); ?></h3>
										<div class="section-meta-minimal">
											<?php
											$splms_lesson_count    = 0;
											$splms_quiz_count      = 0;
											$splms_completed_count = 0;
											foreach ( $splms_section['children'] as $splms_child ) {
												if ( SPLMS_POST_TYPES['lesson'] === $splms_child['type'] ) {
													++$splms_lesson_count;
												} elseif ( SPLMS_POST_TYPES['quiz'] === $splms_child['type'] ) {
													++$splms_quiz_count;
												}
												if ( $splms_child['completed'] ) {
													++$splms_completed_count;
												}
											}
											$splms_total_items = $splms_lesson_count + $splms_quiz_count;
											?>
											<?php
											$splms_parts = array();
											if ( $splms_lesson_count > 0 ) {
												/* translators: %d: Number of lessons. */
												$splms_parts[] = sprintf( _n( '%d lesson', '%d lessons', $splms_lesson_count, 'skillpulse-lms' ), (int) $splms_lesson_count );
											}
											if ( $splms_quiz_count > 0 ) {
												/* translators: %d: Number of quizzes. */
												$splms_parts[] = sprintf( _n( '%d quiz', '%d quizzes', $splms_quiz_count, 'skillpulse-lms' ), (int) $splms_quiz_count );
											}
											echo esc_html( implode( ', ', $splms_parts ) );
											?>
											<?php if ( $splms_is_enrolled && $splms_total_items > 0 ) { ?>
												<span class="section-progress-minimal">
													• <?php echo esc_html( $splms_completed_count ); ?>/<?php echo esc_html( $splms_total_items ); ?>
												</span>
											<?php } ?>
										</div>
									</div>
								</div>
								<div class="section-header-right">
									<?php
									// Display purchased badge or section price.
									if ( $splms_section_is_purchased ) {
										?>
										<span class="section-purchased-badge">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?>
										</span>
										<?php
									} elseif ( $splms_uses_section_pricing && $splms_section_pricing ) {
										$splms_has_configured_pricing = $splms_section_pricing['is_free'] || floatval( $splms_section_pricing['price'] ) > 0;

										if ( $splms_has_configured_pricing ) {
											// Generate section detail page URL.
											$splms_section_detail_url = get_permalink( $splms_section['id'] );

											if ( $splms_section_pricing['is_free'] ) {
												?>
												<a href="<?php echo esc_url( $splms_section_detail_url ); ?>" class="section-price-minimal section-price-link" title="<?php esc_attr_e( 'View section details', 'skillpulse-lms' ); ?>">Free</a>
												<?php
											} else {
												$splms_effective_price = $splms_section_pricing['effective_price'];
												?>
												<a href="<?php echo esc_url( $splms_section_detail_url ); ?>" class="section-price-minimal section-price-link" title="<?php esc_attr_e( 'View section details', 'skillpulse-lms' ); ?>">$<?php echo esc_html( number_format( $splms_effective_price, 2 ) ); ?></a>
												<?php
											}
										}
									}

									// Add section detail link icon.
									$splms_section_detail_url = get_permalink( $splms_section['id'] );
									?>
									<a href="<?php echo esc_url( $splms_section_detail_url ); ?>" class="section-link-icon" title="
									<?php
									/* translators: %s: Section title */
									echo esc_attr( sprintf( __( 'View %s details', 'skillpulse-lms' ), $splms_section['title'] ) );
									?>
									" aria-label="
									<?php
									/* translators: %s: Section title */
									echo esc_attr( sprintf( __( 'Go to %s', 'skillpulse-lms' ), $splms_section['title'] ) );
									?>
									">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M10 6H6C4.89543 6 4 6.89543 4 8V18C4 19.1046 4.89543 20 6 20H16C17.1046 20 18 19.1046 18 18V14M14 4H20M20 4V10M20 4L10 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</a>
									<button class="section-toggle-minimal" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle section', 'skillpulse-lms' ); ?>">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
											<polyline points="9,18 15,12 9,6"></polyline>
										</svg>
									</button>
								</div>
							</div>

							<?php if ( $splms_show_purchase_btn ) { ?>
								<div class="section-purchase-minimal">
									<?php
									// Link to section detail page where user can see full info before purchasing.
									$splms_section_detail_url = get_permalink( $splms_section['id'] );
									?>
									<a href="<?php echo esc_url( $splms_section_detail_url ); ?>" class="purchase-link-minimal">
										→ <?php esc_html_e( 'View section details', 'skillpulse-lms' ); ?>
										<?php
										if ( $splms_section_pricing && ! $splms_section_pricing['is_free'] && $splms_section_pricing['effective_price'] > 0 ) {
											$splms_effective_price = $splms_section_pricing['effective_price'];
											echo ' ($' . esc_html( number_format( $splms_effective_price, 2 ) ) . ')';
										}
										?>
									</a>
								</div>
							<?php } ?>

							<div class="section-divider-minimal"></div>

							<div class="section-content">
								<?php if ( ! empty( $splms_section['children'] ) ) { ?>
									<div class="curriculum-items">
										<?php foreach ( $splms_section['children'] as $splms_item_index => $splms_item ) { ?>
											<?php
											// Use standardized access control data from curriculum function.
											$splms_item_has_access = $splms_item['has_access'];
											$splms_item_is_locked  = $splms_item['is_locked'];

											// Determine preview type for display purposes.
											$splms_is_guest_preview_available = false;
											$splms_is_section_preview_item    = false;

											if ( $splms_item_has_access && ! $splms_is_enrolled ) {
												// Item is accessible to guest - determine preview type.
												if ( $splms_uses_section_pricing && $splms_section_pricing && ! $splms_has_section_access ) {
													// Check if this is a section-based preview.
													if ( isset( $splms_section_pricing['preview_enabled'] ) && $splms_section_pricing['preview_enabled'] ) {
														$splms_preview_items = isset( $splms_section_pricing['preview_items'] ) ? $splms_section_pricing['preview_items'] : array();

														if ( SPLMS_POST_TYPES['lesson'] === $splms_item['type'] ) {
															$splms_lesson_preview_ids      = isset( $splms_preview_items['lessons'] ) ? $splms_preview_items['lessons'] : array();
															$splms_is_section_preview_item = in_array( (int) $splms_item['id'], $splms_lesson_preview_ids, true );
														} elseif ( SPLMS_POST_TYPES['quiz'] === $splms_item['type'] ) {
															$splms_quiz_preview_ids        = isset( $splms_preview_items['quizzes'] ) ? $splms_preview_items['quizzes'] : array();
															$splms_is_section_preview_item = in_array( (int) $splms_item['id'], $splms_quiz_preview_ids, true );
														}
													}
												}

												if ( ! $splms_is_section_preview_item ) {
													// Must be global guest preview.
													if ( SPLMS_POST_TYPES['lesson'] === $splms_item['type'] ) {
														$splms_is_guest_preview_available = splms_is_lesson_guest_preview_available( $splms_item['id'] );
													} elseif ( SPLMS_POST_TYPES['quiz'] === $splms_item['type'] ) {
														$splms_is_guest_preview_available = splms_is_quiz_guest_preview_available( $splms_item['id'] );
													}
												}
											}

											$splms_item_classes = 'curriculum-item ' . esc_attr( $splms_item['type'] );
											if ( $splms_item['completed'] ) {
												$splms_item_classes .= ' completed';
											} elseif ( ! $splms_item_has_access ) {
												$splms_item_classes .= ' locked';
											} elseif ( $splms_is_guest_preview_available || $splms_is_section_preview_item ) {
												$splms_item_classes .= ' guest-preview';
											}
											?>
											<div class="<?php echo esc_attr( $splms_item_classes ); ?>"
												data-item-id="<?php echo esc_attr( $splms_item['id'] ); ?>">

												<div class="item-status">
													<?php if ( $splms_item['completed'] ) { ?>
														<div class="status-icon completed">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
																		stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															</svg>
														</div>
													<?php } elseif ( $splms_item_has_access && ! $splms_is_guest_preview_available && ! $splms_is_section_preview_item ) { ?>
														<div class="status-icon incomplete">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
															</svg>
														</div>
													<?php } elseif ( $splms_is_guest_preview_available ) { ?>
														<div class="status-icon guest-preview">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M1 12S5 4 12 4S23 12 23 12S19 20 12 20S1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
																<path d="M12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9Z" stroke="currentColor" stroke-width="2"/>
															</svg>
														</div>
													<?php } else { ?>
														<div class="status-icon locked">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M5 11H19M5 11C3.89543 11 3 11.8954 3 13V19C3 20.1046 3.89543 21 5 21H19C20.1046 21 21 20.1046 21 19V13C21 11.8954 20.1046 11 19 11M5 11V7C5 5.67392 5.52678 4.40215 6.46447 3.46447C7.40215 2.52678 8.67392 2 10 2H14C15.3261 2 16.5979 2.52678 17.5355 3.46447C18.4732 4.40215 19 5.67392 19 7V11"
																		stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															</svg>
														</div>
													<?php } ?>
												</div>

												<div class="item-type-icon">
													<?php if ( SPLMS_POST_TYPES['lesson'] === $splms_item['type'] ) { ?>
														<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<path d="M14 2H6C4.89543 2 4 2.89543 4 4V20C4 21.1046 4.89543 22 6 22H18C19.1046 22 20 21.1046 20 20V8L14 2Z"
																	stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															<polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2" stroke-linecap="round"
																		stroke-linejoin="round"/>
														</svg>
													<?php } else { ?>
														<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
															<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
															<path d="M9.09 9C9.3251 8.33167 9.78915 7.76811 10.4 7.40913C11.0108 7.05016 11.7289 6.91894 12.4272 7.03871C13.1255 7.15849 13.7588 7.52152 14.2151 8.06353C14.6713 8.60553 14.9211 9.29152 14.92 10C14.92 12 11.92 13 11.92 13"
																	stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
															<path d="M12 17H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
														</svg>
													<?php } ?>
												</div>

												<div class="item-content">
																									<?php if ( $splms_item_has_access ) { ?>
													<a href="<?php echo esc_url( $splms_item['permalink'] ); ?>" class="item-title">
																										<?php echo esc_html( $splms_item['title'] ); ?>
													</a>
												<?php } else { ?>
													<span class="item-title">
																										<?php echo esc_html( $splms_item['title'] ); ?>
													</span>
												<?php } ?>

													<div class="item-meta">
														<span class="item-type-label">
															<?php
															echo SPLMS_POST_TYPES['lesson'] === $splms_item['type'] ? esc_html__(
																'Lesson',
																'skillpulse-lms'
															) : esc_html__( 'Quiz', 'skillpulse-lms' );
															?>
														</span>
														<?php if ( ! empty( $splms_item['duration'] ) ) { ?>
															<span class="item-duration">
																<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
																	<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round"
																				stroke-linejoin="round"/>
																</svg>
																<?php echo esc_html( $splms_item['duration'] ); ?>
															</span>
														<?php } ?>
													</div>
												</div>

												<?php if ( $splms_item_has_access && ( $splms_is_enrolled || $splms_has_section_access ) ) { ?>
													<?php
													// Bookmark feature: for lessons and quizzes, only if enrolled.
													$splms_can_bookmark_item  = false;
													$splms_is_item_bookmarked = false;
													$splms_is_lesson          = ( SPLMS_POST_TYPES['lesson'] === $splms_item['type'] );
													$splms_is_quiz            = ( SPLMS_POST_TYPES['quiz'] === $splms_item['type'] );

													if ( ( $splms_is_lesson || $splms_is_quiz ) && splms_get_setting( 'enable_bookmarks', true ) ) {
														$splms_can_bookmark_item  = true;
														$splms_user_bookmarks     = get_user_meta( $splms_current_user_id, '_splms_bookmarks', true );
														$splms_user_bookmarks     = is_array( $splms_user_bookmarks ) ? array_map( 'intval', $splms_user_bookmarks ) : array();
														$splms_is_item_bookmarked = in_array( (int) $splms_item['id'], $splms_user_bookmarks, true );
													}
													?>
													<div class="item-actions">
														<?php if ( $splms_item['completed'] ) { ?>
															<span class="completion-badge">
																<?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?>
															</span>
														<?php } else { ?>
															<a href="<?php echo esc_url( $splms_item['permalink'] ); ?>" class="start-btn">
																<?php esc_html_e( 'Start', 'skillpulse-lms' ); ?>
															</a>
														<?php } ?>
														<?php if ( $splms_can_bookmark_item ) { ?>
															<?php
															$splms_data_attr  = $splms_is_lesson ? 'data-lesson-id' : 'data-quiz-id';
															$splms_title_text = $splms_is_lesson
																? ( $splms_is_item_bookmarked ? esc_attr__( 'Remove bookmark', 'skillpulse-lms' ) : esc_attr__( 'Bookmark lesson', 'skillpulse-lms' ) )
																: ( $splms_is_item_bookmarked ? esc_attr__( 'Remove bookmark', 'skillpulse-lms' ) : esc_attr__( 'Bookmark quiz', 'skillpulse-lms' ) );
															?>
															<button type="button" class="bookmark-btn-item bookmark-btn <?php echo esc_attr( $splms_is_item_bookmarked ? 'bookmarked active' : '' ); ?>" <?php echo esc_attr( $splms_data_attr ); ?>="<?php echo esc_attr( $splms_item['id'] ); ?>" aria-pressed="<?php echo esc_attr( $splms_is_item_bookmarked ? 'true' : 'false' ); ?>" title="<?php echo esc_attr( $splms_title_text ); ?>">
																<svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo esc_attr( $splms_is_item_bookmarked ? 'currentColor' : 'none' ); ?>" xmlns="http://www.w3.org/2000/svg">
																	<path d="M5 4C5 2.89543 5.89543 2 7 2H17C18.1046 2 19 2.89543 19 4V22L12 17.5L5 22V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
																</svg>
															</button>
														<?php } ?>
													</div>
												<?php } elseif ( $splms_item_has_access && ( $splms_is_guest_preview_available || $splms_is_section_preview_item ) ) { ?>
													<div class="item-actions">
														<a href="<?php echo esc_url( $splms_item['permalink'] ); ?>" class="preview-btn">
															<?php esc_html_e( 'Preview', 'skillpulse-lms' ); ?>
														</a>
													</div>
												<?php } else { ?>
													<div class="item-actions">
														<span class="locked-badge">
															<?php esc_html_e( 'Locked', 'skillpulse-lms' ); ?>
														</span>
													</div>
												<?php } ?>
											</div>
										<?php } ?>
									</div>
								<?php } else { ?>
									<div class="empty-section">
										<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
											<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
											<path d="M8 12H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
										</svg>
										<p><?php esc_html_e( 'No content available in this section.', 'skillpulse-lms' ); ?></p>
									</div>
								<?php } ?>
							</div>
						</div>
					<?php } ?>
				</div>
			<?php } else { ?>
				<div class="no-curriculum">
					<div class="no-curriculum-icon">
						<svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
									stroke="currentColor" stroke-width="1.5"/>
						</svg>
					</div>
					<h3><?php esc_html_e( 'Curriculum Coming Soon', 'skillpulse-lms' ); ?></h3>
					<p><?php esc_html_e( 'The instructor is still working on the course content. Please check back later.', 'skillpulse-lms' ); ?></p>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
