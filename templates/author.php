<?php
/**
 * Author template for instructors/users
 *
 * This template displays author information based on their role.
 * Shows courses for instructors and enrollment info for students.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/author.php
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// Get the queried author.
$author = get_queried_object();

// Safety check - make sure we have valid author data.
if ( ! $author || ! isset( $author->ID ) ) {
	echo '<div class="splms-error">Author not found.</div>';
	get_footer();
	return;
}

$author_id = $author->ID;

// Get user role and capabilities.
$user       = get_user_by( 'ID', $author_id );
$user_roles = $user ? $user->roles : array();

// Determine if user is instructor (can create courses).
$is_instructor = user_can( $author_id, 'edit_posts' ) || in_array( 'instructor', $user_roles, true ) || in_array( 'administrator', $user_roles, true );

// Get pagination parameters.
$courses_per_page = 10;

// For author pages, use a simple GET parameter for pagination.
$current_page = max( 1, isset( $_GET['course_page'] ) ? intval( $_GET['course_page'] ) : 1 ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Configuration file for admin context, no data processing.

// Get author's total published courses count (for stats and pagination).
$total_courses_query = new WP_Query(
	array(
		'post_type'      => SPLMS_POST_TYPES['course'],
		'author'         => $author_id,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	)
);
$total_courses       = $total_courses_query->found_posts;

// Get paginated author's published courses.
$author_courses_query = new WP_Query(
	array(
		'post_type'      => SPLMS_POST_TYPES['course'],
		'author'         => $author_id,
		'post_status'    => 'publish',
		'posts_per_page' => $courses_per_page,
		'paged'          => $current_page,
	)
);

$author_courses = $author_courses_query->posts;
$total_pages    = $author_courses_query->max_num_pages;

// Get enrollment information for students.
$enrolled_course_ids = array();
$enrolled_courses    = array();
$total_enrolled      = 0;
if ( ! $is_instructor || empty( $author_courses ) ) {
	$enrolled_course_ids = splms_get_user_enrolled_courses( $author_id );
	$total_enrolled      = count( $enrolled_course_ids );

	if ( ! empty( $enrolled_course_ids ) ) {
		// For students, also paginate enrolled courses.
		$enrolled_courses_query = new WP_Query(
			array(
				'post_type'      => SPLMS_POST_TYPES['course'],
				'post__in'       => $enrolled_course_ids,
				'post_status'    => 'publish',
				'posts_per_page' => $courses_per_page,
				'paged'          => $current_page,
			)
		);
		$enrolled_courses       = $enrolled_courses_query->posts;
		$total_pages            = max( $total_pages, $enrolled_courses_query->max_num_pages );
	}
}
?>

<div class="splms-author-archive">

	<?php
	/**
	 * Hook: splms_before_author_content
	 */
	do_action( 'splms_before_author_content', $author );
	?>

	<header class="splms-author-header">
		<div class="splms-author-header-content">
			<div class="splms-author-avatar">
				<?php echo get_avatar( $author_id, 80 ); ?>
			</div>
			<div class="splms-author-info">
				<div class="splms-author-role-badge">
					<?php if ( $is_instructor ) : ?>
						<span class="splms-author-role instructor">
							<?php esc_html_e( 'Instructor', 'skillpulse-lms' ); ?>
						</span>
					<?php else : ?>
						<span class="splms-author-role student">
							<?php esc_html_e( 'Student', 'skillpulse-lms' ); ?>
						</span>
					<?php endif; ?>
				</div>

				<h1 class="splms-author-name"><?php echo esc_html( $author->display_name ); ?></h1>

				<?php if ( ! empty( $author->description ) ) { ?>
					<div class="splms-author-bio">
						<?php echo wp_kses_post( $author->description ); ?>
					</div>
				<?php } ?>

				<?php if ( $is_instructor && $total_courses > 0 ) : ?>
					<div class="splms-author-stats">
						<div class="splms-stat">
							<span class="splms-stat-number"><?php echo esc_html( $total_courses ); ?></span>
							<span class="splms-stat-label">
								<?php echo esc_html( _n( 'Course', 'Courses', $total_courses, 'skillpulse-lms' ) ); ?>
							</span>
						</div>

						<?php
						// Additional stats for instructors.
						$total_students = 0;

						foreach ( $author_courses as $course ) {
							$enrolled_count  = splms_get_course_enrollment_count( $course->ID );
							$total_students += $enrolled_count;
						}

						if ( $total_students > 0 ) :
							?>
							<div class="splms-stat">
								<span class="splms-stat-number"><?php echo esc_html( number_format( $total_students ) ); ?></span>
								<span class="splms-stat-label">
									<?php echo esc_html( _n( 'Student', 'Students', $total_students, 'skillpulse-lms' ) ); ?>
								</span>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<?php
	/**
	 * Hook: splms_author_before_courses
	 */
	do_action( 'splms_author_before_courses', $author );
	?>

	<main class="splms-author-main">
		<div class="splms-author-content">

			<?php if ( $is_instructor && ! empty( $author_courses ) ) : ?>
				<!-- Instructor Courses -->
				<section class="splms-author-courses">
					<div class="splms-section-header">
						<h2 class="splms-section-title">
							<?php
							/* translators: %d: Number of courses */
							printf( esc_html( _n( '%d Course', '%d Courses', $total_courses, 'skillpulse-lms' ) ), esc_html( $total_courses ) );
							?>
						</h2>
						<p class="splms-section-description">
							<?php esc_html_e( 'Courses created by this instructor', 'skillpulse-lms' ); ?>
						</p>
					</div>

					<div class="splms-courses-grid">
						<?php
						global $post;
						foreach ( $author_courses as $course ) :
							$post = $course; // phpcs:ignore
							setup_postdata( $post );

							// Include the standard course card template.
							splms_get_template( 'course/course.php' );

						endforeach;
						wp_reset_postdata();
						?>
					</div>

					<?php
					$total_pages = $total_pages;

					if ( $total_pages <= 1 ) {
						return;
					}

					echo '<nav class="splms-pagination" role="navigation" aria-label="' . esc_attr__( 'Courses pagination', 'skillpulse-lms' ) . '">';
					echo '<div class="pagination-info">';
					splms_output_result_count();
					echo '</div>';
					echo '<div class="pagination-links">';

					$prev_label = esc_attr__( 'Previous', 'skillpulse-lms' );
					$next_label = esc_attr__( 'Next', 'skillpulse-lms' );

					$prev_text = '<span class="page-prev" aria-label="' . $prev_label . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12H19" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M5 12L9 16" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M5 12L9 8" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>';

					$next_text = '<span class="page-next" aria-label="' . $next_label . '">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M5 12H19" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 16L19 12" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                    <path d="M15 8L19 12" stroke="#374151" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </span>';

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
					echo paginate_links(
						array(
							'current'   => $current_page,
							'total'     => $total_pages,
							'prev_text' => $prev_text,
							'next_text' => $next_text,
							'type'      => 'list',
							'end_size'  => 2,
							'mid_size'  => 2,
						)
					);

					echo '</div>';
					echo '</nav>';
					?>

					<?php if ( $total_pages > 1 ) : ?>
						<div class="splms-pagination-wrapper">
							<?php
							// Custom pagination for author pages using query parameters.
							echo '<nav class="splms-pagination" role="navigation" aria-label="' . esc_attr__( 'Courses pagination', 'skillpulse-lms' ) . '">';
							echo '<ul class="splms-pagination-list">';

							$author_link = get_author_posts_url( $author_id );

							// Previous link.
							if ( $current_page > 1 ) {
								$prev_page = $current_page - 1;
								$prev_link = ( 1 === $prev_page ) ? $author_link : add_query_arg( 'course_page', $prev_page, $author_link );
								echo '<li class="splms-pagination-item"><a href="' . esc_url( $prev_link ) . '"><span class="splms-pagination-prev">‹ ' . esc_html__( 'Previous', 'skillpulse-lms' ) . '</span></a></li>';
							}

							// Page numbers.
							for ( $i = 1; $i <= $total_pages; $i++ ) {
								$page_link = ( 1 === $i ) ? $author_link : add_query_arg( 'course_page', $i, $author_link );
								$class     = ( $i === $current_page ) ? 'current' : '';

								if ( $i === $current_page ) {
									printf( '<li class="splms-pagination-item"><span class="%s">%s</span></li>', esc_attr( $class ), esc_html( $i ) );
								} else {
									printf( '<li class="splms-pagination-item"><a href="%s" >%s</a></li>', esc_url( $page_link ), esc_html( $i ) );
								}
							}

							// Next link.
							if ( $current_page < $total_pages ) {
								$next_page = $current_page + 1;
								$next_link = add_query_arg( 'course_page', $next_page, $author_link );
								echo '<li class="splms-pagination-item"><a href="' . esc_url( $next_link ) . '"><span class="splms-pagination-next">' . esc_html__( 'Next', 'skillpulse-lms' ) . ' ›</span></a></li>';
							}

							echo '</ul>';
							echo '</nav>';
							?>
						</div>
					<?php endif; ?>
				</section>

			<?php elseif ( $is_instructor && empty( $author_courses ) ) : ?>
				<!-- No Courses Message for Instructor -->
				<section class="splms-no-courses">
					<div class="splms-empty-state">
						<div class="splms-empty-icon">
							<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
								<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
								<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
								<path d="M12 7v10"/>
							</svg>
						</div>
						<h3 class="splms-empty-title"><?php esc_html_e( 'No Courses Yet', 'skillpulse-lms' ); ?></h3>
						<p class="splms-empty-description">
							<?php esc_html_e( 'This instructor hasn\'t created any courses yet. Check back later for new content!', 'skillpulse-lms' ); ?>
						</p>
					</div>
				</section>

			<?php else : ?>
				<!-- Student Profile -->
				<section class="splms-student-profile">
					<div class="splms-section-header">
						<h2 class="splms-section-title"><?php esc_html_e( 'Student Profile', 'skillpulse-lms' ); ?></h2>
						<p class="splms-section-description">
							<?php esc_html_e( 'Learning journey and course enrollments', 'skillpulse-lms' ); ?>
						</p>
					</div>

					<div class="splms-student-stats">
						<div class="splms-stat-card">
							<div class="splms-stat-icon">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
									<path d="M12 3L1 9L12 15L21 10.09V17H23V9L12 3Z"/>
								</svg>
							</div>
							<div class="splms-stat-content">
								<span class="splms-stat-number"><?php echo esc_html( $total_enrolled ); ?></span>
								<span class="splms-stat-label">
									<?php echo esc_html( _n( 'Enrolled Course', 'Enrolled Courses', $total_enrolled, 'skillpulse-lms' ) ); ?>
								</span>
							</div>
						</div>
					</div>

					<?php if ( ! empty( $enrolled_courses ) ) : ?>
						<div class="splms-enrolled-courses">
							<h3 class="splms-subsection-title"><?php esc_html_e( 'Enrolled Courses', 'skillpulse-lms' ); ?></h3>
							<div class="splms-courses-grid">
								<?php
								global $post;
								foreach ( $enrolled_courses as $course ) :
									$post = $course; // phpcs:ignore
									setup_postdata( $post );

									// Include the standard course card template.
									splms_get_template( 'course/course.php' );

								endforeach;
								wp_reset_postdata();
								?>
							</div>

							<?php if ( $max_pages > 1 ) : ?>
								<div class="splms-pagination-wrapper">
									<?php
									// Custom pagination for author pages using query parameters.
									echo '<nav class="splms-pagination" role="navigation" aria-label="' . esc_attr__( 'Courses pagination', 'skillpulse-lms' ) . '">';
									echo '<ul class="splms-pagination-list">';

									$author_link = get_author_posts_url( $author_id );

									// Previous link.
									if ( $current_page > 1 ) {
										$prev_page = $current_page - 1;
										$prev_link = ( 1 === $prev_page ) ? $author_link : add_query_arg( 'course_page', $prev_page, $author_link );
										echo '<li class="splms-pagination-item"><a href="' . esc_url( $prev_link ) . '"><span class="splms-pagination-prev">‹ ' . esc_html__( 'Previous', 'skillpulse-lms' ) . '</span></a></li>';
									}

									// Page numbers.
									for ( $i = 1; $i <= $max_pages; $i++ ) {
										$page_link = ( 1 === $i ) ? $author_link : add_query_arg( 'course_page', $i, $author_link );
										$class     = ( $i === $current_page ) ? 'current' : '';

										if ( $i === $current_page ) {
											printf( '<li class="splms-pagination-item"><span class="%s">%s</span></li>', esc_attr( $class ), esc_html( $i ) );
										} else {
											printf( '<li class="splms-pagination-item"><a href="%s">%s</a></li>', esc_url( $page_link ), esc_html( $i ) );
										}
									}

									// Next link.
									if ( $current_page < $max_pages ) {
										$next_page = $current_page + 1;
										$next_link = add_query_arg( 'course_page', $next_page, $author_link );
										echo '<li class="splms-pagination-item"><a href="' . esc_url( $next_link ) . '"><span class="splms-pagination-next">' . esc_html__( 'Next', 'skillpulse-lms' ) . ' ›</span></a></li>';
									}

									echo '</ul>';
									echo '</nav>';
									?>
								</div>
							<?php endif; ?>
						</div>
					<?php else : ?>
						<div class="splms-empty-state">
							<div class="splms-empty-icon">
								<svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
									<circle cx="12" cy="12" r="10"/>
									<path d="M12 6v6l4 2"/>
									<path d="M16 8V6a4 4 0 0 0-8 0v2"/>
								</svg>
							</div>
							<h3 class="splms-empty-title"><?php esc_html_e( 'No Enrollments Yet', 'skillpulse-lms' ); ?></h3>
							<p class="splms-empty-description">
								<?php esc_html_e( 'This student hasn\'t enrolled in any courses yet.', 'skillpulse-lms' ); ?>
							</p>

							<?php if ( get_current_user_id() === $author_id ) : ?>
								<div class="splms-empty-actions">
									<a href="<?php echo esc_url( get_post_type_archive_link( SPLMS_POST_TYPES['course'] ) ); ?>" class="splms-button splms-button-primary">
										<?php esc_html_e( 'Browse Courses', 'skillpulse-lms' ); ?>
									</a>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</section>
			<?php endif; ?>
		</div>
	</main>

	<?php
	/**
	 * Hook: splms_after_author_content
	 */
	do_action( 'splms_after_author_content', $author );
	?>

</div>

<?php
get_footer();