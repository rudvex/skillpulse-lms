<?php
/**
 * Course Instructor Tab
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/instructor.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



$splms_course_id = get_the_ID();

// Get instructor courses and stats.
$splms_instructor_courses = new WP_Query(
	array(
		'post_type'      => SPLMS_POST_TYPES['course'],
		'author'         => get_the_author_meta( 'ID' ),
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
	)
);

// Calculate total students across all courses.
$splms_total_students = 0;
if ( $splms_instructor_courses->have_posts() ) {
	while ( $splms_instructor_courses->have_posts() ) {
		$splms_instructor_courses->the_post();
		$splms_total_students += splms_get_course_enrollment_count( get_the_ID() );
	}
	wp_reset_postdata();
}

$splms_total_courses = $splms_instructor_courses->found_posts;
?>

<div class="course-content-tabs">
	<div class="course-content-section course-instructor">
		<h2 class="section-title">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php esc_html_e( 'About the Instructor', 'skillpulse-lms' ); ?>
		</h2>
		<div class="section-content">
			<!-- Instructor Profile Header - Side by Side Layout -->
			<div class="instructor-profile-header">
				<div class="instructor-avatar-container">
					<div class="instructor-avatar-large">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 100 ); ?>
						<div class="instructor-verified-badge">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
					</div>
				</div>
				<div class="instructor-info-container">
					<h3 class="instructor-name"><?php the_author(); ?></h3>
					<p class="instructor-role"><?php esc_html_e( 'Course Instructor', 'skillpulse-lms' ); ?></p>
					<div class="instructor-stats-inline">
						<div class="stat-inline-item">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<span>
								<?php
								/* translators: %d: Number of courses. */
								printf( esc_html( _n( '%d Course', '%d Courses', $splms_total_courses, 'skillpulse-lms' ) ), (int) $splms_total_courses );
								?>
							</span>
						</div>
						<div class="stat-inline-item">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
										stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
							<span>
								<?php
								/* translators: %d: Number of students. */
								printf( esc_html( _n( '%d Student', '%d Students', $splms_total_students, 'skillpulse-lms' ) ), (int) $splms_total_students );
								?>
							</span>
						</div>
					</div>
				</div>
			</div>

			<!-- Instructor Bio Section -->
			<div class="instructor-bio-section">
				<h4 class="bio-section-title"><?php esc_html_e( 'About', 'skillpulse-lms' ); ?></h4>
				<div class="instructor-bio-content">
					<?php
					$splms_bio = get_the_author_meta( 'description' );
					if ( $splms_bio ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wpautop output is safe when used with esc_html.
						echo wpautop( esc_html( $splms_bio ) );
					} else {
						echo '<p>' . esc_html__(
							'This instructor is passionate about sharing knowledge and helping students achieve their learning goals. With extensive experience in their field, they bring practical insights and real-world applications to every course.',
							'skillpulse-lms'
						) . '</p>';
					}
					?>
				</div>
			</div>

			<!-- Other Courses Section -->
			<?php if ( $splms_total_courses > 1 ) { ?>
				<div class="instructor-other-courses-section">
					<h4 class="other-courses-section-title">
						<?php
						/* translators: %s: Instructor name. */
						printf( esc_html__( 'Other Courses by %s', 'skillpulse-lms' ), esc_html( get_the_author() ) );
						?>
					</h4>
					<div class="other-courses-list">
						<?php
						$splms_other_courses = new WP_Query(
							array(
								'post_type'      => SPLMS_POST_TYPES['course'],
								'author'         => get_the_author_meta( 'ID' ),
								'post_status'    => 'publish',
								'posts_per_page' => 3,
								// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Single post exclusion; negligible performance impact.
								'post__not_in'   => array( $splms_course_id ),
							)
						);

						if ( $splms_other_courses->have_posts() ) {
							while ( $splms_other_courses->have_posts() ) {
								$splms_other_courses->the_post();
								$splms_students_count = splms_get_course_enrollment_count( get_the_ID() );
								$splms_course_price   = splms_get_course_access_info( get_the_ID() );
								?>
								<div class="course-card-horizontal">
									<div class="course-card-thumbnail">
										<a href="<?php the_permalink(); ?>">
											<?php if ( has_post_thumbnail() ) { ?>
												<?php the_post_thumbnail( 'medium' ); ?>
											<?php } else { ?>
												<div class="course-thumbnail-placeholder">
													<svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
														<path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528"
																stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
													</svg>
												</div>
											<?php } ?>
										</a>
									</div>
									<div class="course-card-content">
										<h5 class="course-card-title">
											<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
										</h5>
										<div class="course-card-meta">
											<span class="course-meta-item">
												<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
															stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
												</svg>
												<?php
												printf(
													/* translators: %d: Number of students. */
													esc_html( _n( '%d student', '%d students', $splms_students_count, 'skillpulse-lms' ) ),
													(int) $splms_students_count
												);
												?>
											</span>
											<span class="course-meta-separator">•</span>
											<span class="course-meta-item course-price-meta">
												<?php
												if ( ! empty( $splms_course_price['course_mode'] ) ) {
													switch ( $splms_course_price['course_mode'] ) {
														case 'free':
															esc_html_e( 'Free', 'skillpulse-lms' );
															break;
														case 'paid':
															// Only show paid prices if paid courses are enabled.
															if ( splms_is_paid_courses_enabled() ) {
																echo esc_html( splms_get_price_format( $splms_course_price['price'] ) );
															} else {
																esc_html_e( 'Free', 'skillpulse-lms' );
															}
															break;
														case 'prerequisite':
															esc_html_e( 'Prerequisite', 'skillpulse-lms' );
															break;
														default:
															// For invitation_only, show appropriate label.
															if ( ! empty( $splms_course_price['course_access_type'] ) ) {
																$splms_access_type = $splms_course_price['course_access_type'];
																if ( 'invitation_only' === $splms_access_type ) {
																	esc_html_e( 'Invitation Only', 'skillpulse-lms' );
																}
															}
															break;
													}
												}
												?>
											</span>
										</div>
									</div>
								</div>
								<?php
							}
							wp_reset_postdata();
						}
						?>
					</div>

					<?php if ( $splms_total_courses > 3 ) { ?>
						<div class="view-all-courses-footer">
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="btn-view-all-courses">
								<?php
								/* translators: %d: Total number of courses. */
								printf( esc_html__( 'View All %d Courses', 'skillpulse-lms' ), (int) $splms_total_courses );
								?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>
