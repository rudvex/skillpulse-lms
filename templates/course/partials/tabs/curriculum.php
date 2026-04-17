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

$course_id       = get_the_ID();
$current_user_id = get_current_user_id();
$is_enrolled     = false;
$user_progress   = array();

if ( $current_user_id ) {
	// Use the new unified enrollment check function.
	$is_enrolled = splms_is_user_enrolled( $course_id, $current_user_id );

	if ( $is_enrolled ) {
		// Calculate progress from database tables.
		$lessons_instance = SkillPulse_LMS_Lessons::get_instance();
		$progress_data    = $lessons_instance ? $lessons_instance->calculate_course_progress( $current_user_id, $course_id ) : array();

		$user_progress = array(
			'percentage'        => $progress_data['percentage'],
			'completed_lessons' => $progress_data['completed_lessons'] + $progress_data['passed_quizzes'],
			'total_lessons'     => $progress_data['total_items'],
		);
	}
}

// Get course curriculum using centralized function with built-in access control.
$curriculum_result = splms_get_course_curriculum( $course_id, $current_user_id );

$curriculum_data = isset( $curriculum_result['sections'] ) ? $curriculum_result['sections'] : array();
$total_lessons   = isset( $curriculum_result['stats']['total_lessons'] ) ? $curriculum_result['stats']['total_lessons'] : 0;
$total_quizzes   = isset( $curriculum_result['stats']['total_quizzes'] ) ? $curriculum_result['stats']['total_quizzes'] : 0;

// Check if this course uses section-based pricing for display purposes.
$uses_section_pricing = splms_course_uses_section_pricing( $course_id );

// Get user's purchased sections for this course for display purposes.
$purchased_sections = array();
if ( $uses_section_pricing && $current_user_id ) {
	$purchased_sections = splms_get_user_purchased_sections( $course_id, $current_user_id );
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
			<!-- Curriculum Overview - Minimal -->
			<div class="curriculum-overview">
				<div class="curriculum-stats-minimal">
					<span class="stat-text"><?php echo count( $curriculum_data ); ?> <?php esc_html_e( 'Sections', 'skillpulse-lms' ); ?></span>
					<span class="stat-separator">•</span>
					<span class="stat-text"><?php echo esc_html( (int) $total_lessons ); ?> <?php esc_html_e( 'Lessons', 'skillpulse-lms' ); ?></span>
					<span class="stat-separator">•</span>
					<span class="stat-text"><?php echo esc_html( (int) $total_quizzes ); ?> <?php esc_html_e( 'Quizzes', 'skillpulse-lms' ); ?></span>
					<?php if ( $is_enrolled && ! empty( $user_progress ) ) { ?>
						<span class="stat-separator">•</span>
						<span class="stat-text stat-progress"><?php echo esc_html( round( $user_progress['percentage'], 1 ) ); ?>% <?php esc_html_e( 'Complete', 'skillpulse-lms' ); ?></span>
					<?php } ?>
				</div>
			</div>

			<?php if ( ! empty( $curriculum_data ) ) { ?>
				<div class="curriculum-content">
					<?php
					foreach ( $curriculum_data as $section_index => $section ) {
						// Use standardized access control data from curriculum function.
						$has_section_access = $section['has_access'];
						$section_is_locked  = $section['is_locked'];

						// Get section pricing data for display purposes.
						$section_pricing      = null;
						$show_purchase_btn    = false;
						$section_is_purchased = false;

						if ( $uses_section_pricing ) {
							$section_pricing = splms_get_section_pricing_with_access( $section['id'], $current_user_id );

							// Check if this specific section is purchased (not full course enrollment).
							$section_is_purchased = ! $is_enrolled && in_array( (int) $section['id'], $purchased_sections, true );

							// Show purchase button if section is locked and not free (including 0 price).
							$show_purchase_btn = $section_is_locked && ! $section_pricing['is_free'] && $section_pricing['effective_price'] > 0;
						}

						$section_classes = 'curriculum-section-minimal';
						if ( $section_is_locked ) {
							$section_classes .= ' section-locked';
						}
						if ( $section_is_purchased ) {
							$section_classes .= ' section-purchased';
						}
						?>
						<div class="<?php echo esc_attr( $section_classes ); ?>" data-section-id="<?php echo esc_attr( $section['id'] ); ?>">
							<div class="section-header-minimal">
								<div class="section-header-main">
									<span class="section-number-minimal"><?php echo esc_html( $section_index + 1 ); ?></span>
									<div class="section-info-minimal">
										<h3 class="section-title-minimal"><?php echo esc_html( $section['title'] ); ?></h3>
										<div class="section-meta-minimal">
											<?php
											$lesson_count    = 0;
											$quiz_count      = 0;
											$completed_count = 0;
											foreach ( $section['children'] as $child ) {
												if ( SPLMS_POST_TYPES['lesson'] === $child['type'] ) {
													++$lesson_count;
												} elseif ( SPLMS_POST_TYPES['quiz'] === $child['type'] ) {
													++$quiz_count;
												}
												if ( $child['completed'] ) {
													++$completed_count;
												}
											}
											$total_items = $lesson_count + $quiz_count;
											?>
											<?php
											$parts = array();
											if ( $lesson_count > 0 ) {
												/* translators: %d: Number of lessons. */
												$parts[] = sprintf( _n( '%d lesson', '%d lessons', $lesson_count, 'skillpulse-lms' ), (int) $lesson_count );
											}
											if ( $quiz_count > 0 ) {
												/* translators: %d: Number of quizzes. */
												$parts[] = sprintf( _n( '%d quiz', '%d quizzes', $quiz_count, 'skillpulse-lms' ), (int) $quiz_count );
											}
											echo esc_html( implode( ', ', $parts ) );
											?>
											<?php if ( $is_enrolled && $total_items > 0 ) { ?>
												<span class="section-progress-minimal">
													• <?php echo esc_html( $completed_count ); ?>/<?php echo esc_html( $total_items ); ?>
												</span>
											<?php } ?>
										</div>
									</div>
								</div>
								<div class="section-header-right">
									<?php
									// Display purchased badge or section price.
									if ( $section_is_purchased ) {
										?>
										<span class="section-purchased-badge">
											<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
											</svg>
											<?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?>
										</span>
										<?php
									} elseif ( $uses_section_pricing && $section_pricing ) {
										$has_configured_pricing = $section_pricing['is_free'] || floatval( $section_pricing['price'] ) > 0;

										if ( $has_configured_pricing ) {
											// Generate section detail page URL.
											$section_detail_url = get_permalink( $section['id'] );

											if ( $section_pricing['is_free'] ) {
												?>
												<a href="<?php echo esc_url( $section_detail_url ); ?>" class="section-price-minimal section-price-link" title="<?php esc_attr_e( 'View section details', 'skillpulse-lms' ); ?>">Free</a>
												<?php
											} else {
												$effective_price = $section_pricing['effective_price'];
												?>
												<a href="<?php echo esc_url( $section_detail_url ); ?>" class="section-price-minimal section-price-link" title="<?php esc_attr_e( 'View section details', 'skillpulse-lms' ); ?>">$<?php echo esc_html( number_format( $effective_price, 2 ) ); ?></a>
												<?php
											}
										}
									}

									// Add section detail link icon.
									$section_detail_url = get_permalink( $section['id'] );
									?>
									<a href="<?php echo esc_url( $section_detail_url ); ?>" class="section-link-icon" title="
									<?php
									/* translators: %s: Section title */
									echo esc_attr( sprintf( __( 'View %s details', 'skillpulse-lms' ), $section['title'] ) );
									?>
									" aria-label="
									<?php
									/* translators: %s: Section title */
									echo esc_attr( sprintf( __( 'Go to %s', 'skillpulse-lms' ), $section['title'] ) );
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

							<?php if ( $show_purchase_btn ) { ?>
								<div class="section-purchase-minimal">
									<?php
									// Link to section detail page where user can see full info before purchasing.
									$section_detail_url = get_permalink( $section['id'] );
									?>
									<a href="<?php echo esc_url( $section_detail_url ); ?>" class="purchase-link-minimal">
										→ <?php esc_html_e( 'View section details', 'skillpulse-lms' ); ?>
										<?php
										if ( $section_pricing && ! $section_pricing['is_free'] && $section_pricing['effective_price'] > 0 ) {
											$effective_price = $section_pricing['effective_price'];
											echo ' ($' . esc_html( number_format( $effective_price, 2 ) ) . ')';
										}
										?>
									</a>
								</div>
							<?php } ?>

							<div class="section-divider-minimal"></div>

							<div class="section-content">
								<?php if ( ! empty( $section['children'] ) ) { ?>
									<div class="curriculum-items">
										<?php foreach ( $section['children'] as $item_index => $item ) { ?>
											<?php
											// Use standardized access control data from curriculum function.
											$item_has_access = $item['has_access'];
											$item_is_locked  = $item['is_locked'];

											// Determine preview type for display purposes.
											$is_guest_preview_available = false;
											$is_section_preview_item    = false;

											if ( $item_has_access && ! $is_enrolled ) {
												// Item is accessible to guest - determine preview type.
												if ( $uses_section_pricing && $section_pricing && ! $has_section_access ) {
													// Check if this is a section-based preview.
													if ( isset( $section_pricing['preview_enabled'] ) && $section_pricing['preview_enabled'] ) {
														$preview_items = isset( $section_pricing['preview_items'] ) ? $section_pricing['preview_items'] : array();

														if ( SPLMS_POST_TYPES['lesson'] === $item['type'] ) {
															$lesson_preview_ids      = isset( $preview_items['lessons'] ) ? $preview_items['lessons'] : array();
															$is_section_preview_item = in_array( (int) $item['id'], $lesson_preview_ids, true );
														} elseif ( SPLMS_POST_TYPES['quiz'] === $item['type'] ) {
															$quiz_preview_ids        = isset( $preview_items['quizzes'] ) ? $preview_items['quizzes'] : array();
															$is_section_preview_item = in_array( (int) $item['id'], $quiz_preview_ids, true );
														}
													}
												}

												if ( ! $is_section_preview_item ) {
													// Must be global guest preview.
													if ( SPLMS_POST_TYPES['lesson'] === $item['type'] ) {
														$is_guest_preview_available = splms_is_lesson_guest_preview_available( $item['id'] );
													} elseif ( SPLMS_POST_TYPES['quiz'] === $item['type'] ) {
														$is_guest_preview_available = splms_is_quiz_guest_preview_available( $item['id'] );
													}
												}
											}

											$item_classes = 'curriculum-item ' . esc_attr( $item['type'] );
											if ( $item['completed'] ) {
												$item_classes .= ' completed';
											} elseif ( ! $item_has_access ) {
												$item_classes .= ' locked';
											} elseif ( $is_guest_preview_available || $is_section_preview_item ) {
												$item_classes .= ' guest-preview';
											}
											?>
											<div class="<?php echo esc_attr( $item_classes ); ?>"
												data-item-id="<?php echo esc_attr( $item['id'] ); ?>">

												<div class="item-status">
													<?php if ( $item['completed'] ) { ?>
														<div class="status-icon completed">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
																		stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
															</svg>
														</div>
													<?php } elseif ( $item_has_access && ! $is_guest_preview_available && ! $is_section_preview_item ) { ?>
														<div class="status-icon incomplete">
															<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
															</svg>
														</div>
													<?php } elseif ( $is_guest_preview_available ) { ?>
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
													<?php if ( SPLMS_POST_TYPES['lesson'] === $item['type'] ) { ?>
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
																									<?php if ( $item_has_access ) { ?>
													<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="item-title">
																										<?php echo esc_html( $item['title'] ); ?>
													</a>
												<?php } else { ?>
													<span class="item-title">
																										<?php echo esc_html( $item['title'] ); ?>
													</span>
												<?php } ?>

													<div class="item-meta">
														<span class="item-type-label">
															<?php
															echo SPLMS_POST_TYPES['lesson'] === $item['type'] ? esc_html__(
																'Lesson',
																'skillpulse-lms'
															) : esc_html__( 'Quiz', 'skillpulse-lms' );
															?>
														</span>
														<?php if ( ! empty( $item['duration'] ) ) { ?>
															<span class="item-duration">
																<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
																	<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
																	<polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round"
																				stroke-linejoin="round"/>
																</svg>
																<?php echo esc_html( $item['duration'] ); ?>
															</span>
														<?php } ?>
													</div>
												</div>

												<?php if ( $item_has_access && ( $is_enrolled || $has_section_access ) ) { ?>
													<?php
													// Bookmark feature: for lessons and quizzes, only if enrolled.
													$can_bookmark_item  = false;
													$is_item_bookmarked = false;
													$is_lesson          = ( SPLMS_POST_TYPES['lesson'] === $item['type'] );
													$is_quiz            = ( SPLMS_POST_TYPES['quiz'] === $item['type'] );

													if ( ( $is_lesson || $is_quiz ) && splms_get_setting( 'enable_bookmarks', true ) ) {
														$can_bookmark_item  = true;
														$user_bookmarks     = get_user_meta( $current_user_id, '_splms_bookmarks', true );
														$user_bookmarks     = is_array( $user_bookmarks ) ? array_map( 'intval', $user_bookmarks ) : array();
														$is_item_bookmarked = in_array( (int) $item['id'], $user_bookmarks, true );
													}
													?>
													<div class="item-actions">
														<?php if ( $item['completed'] ) { ?>
															<span class="completion-badge">
																<?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?>
															</span>
														<?php } else { ?>
															<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="start-btn">
																<?php esc_html_e( 'Start', 'skillpulse-lms' ); ?>
															</a>
														<?php } ?>
														<?php if ( $can_bookmark_item ) { ?>
															<?php
															$data_attr  = $is_lesson ? 'data-lesson-id' : 'data-quiz-id';
															$title_text = $is_lesson
																? ( $is_item_bookmarked ? esc_attr__( 'Remove bookmark', 'skillpulse-lms' ) : esc_attr__( 'Bookmark lesson', 'skillpulse-lms' ) )
																: ( $is_item_bookmarked ? esc_attr__( 'Remove bookmark', 'skillpulse-lms' ) : esc_attr__( 'Bookmark quiz', 'skillpulse-lms' ) );
															?>
															<button type="button" class="bookmark-btn-item bookmark-btn <?php echo $is_item_bookmarked ? 'bookmarked active' : ''; ?>" <?php echo esc_attr( $data_attr ); ?>="<?php echo esc_attr( $item['id'] ); ?>" aria-pressed="<?php echo $is_item_bookmarked ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $title_text ); ?>">
																<svg width="16" height="16" viewBox="0 0 24 24" fill="<?php echo $is_item_bookmarked ? 'currentColor' : 'none'; ?>" xmlns="http://www.w3.org/2000/svg">
																	<path d="M5 4C5 2.89543 5.89543 2 7 2H17C18.1046 2 19 2.89543 19 4V22L12 17.5L5 22V4Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
																</svg>
															</button>
														<?php } ?>
													</div>
												<?php } elseif ( $item_has_access && ( $is_guest_preview_available || $is_section_preview_item ) ) { ?>
													<div class="item-actions">
														<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="preview-btn">
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
