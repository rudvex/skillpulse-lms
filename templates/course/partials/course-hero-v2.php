<?php
/**
 * Course Hero V2 Template - Instructor-focused Layout
 *
 * This template displays course information with a focus on the instructor
 * and shows individual sections available for purchase.
 *
 * @package SkillPulse_LMS
 * @version 2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$course_id      = get_the_ID();
$user_id        = get_current_user_id();
$author_id      = get_the_author_meta( 'ID' );
$rating_summary = splms_get_course_rating( $course_id );

// Ensure we have the proper structure.
if ( ! is_array( $rating_summary ) ) {
	$rating_summary = array();
}

// Set default values if keys are missing.
$rating_summary = array_merge(
	array(
		'average_rating'   => 0,
		'total_reviews'    => 0,
		'rating_breakdown' => array(
			5 => 0,
			4 => 0,
			3 => 0,
			2 => 0,
			1 => 0,
		),
	),
	$rating_summary
);

// Get instructor stats.
$instructor_courses_count = count_user_posts( $author_id, SPLMS_POST_TYPES['course'] );
$instructor_meta          = get_user_meta( $author_id, 'splms_instructor_stats', true );

if ( ! is_array( $instructor_meta ) ) {
	$instructor_meta = array();
}

$instructor_stats = array_merge(
	array(
		'total_subscribers' => 0,
		'total_views'       => 0,
		'subjects_count'    => 0,
		'instructor_rating' => 0,
	),
	$instructor_meta
);

// Get course statistics.
$duration       = splms_get_course_duration( $course_id );
$students_count = splms_get_course_enrollment_count( $course_id );

// Get all course sections.
$all_sections       = array();
$total_videos       = 0;
$total_notes        = 0;
$total_duration_min = 0;

$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
if ( $course_items_query ) {
	$course_items = $course_items_query->get_items( $course_id );

	foreach ( $course_items as $course_item ) {
		if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
			continue;
		}

		$section_id       = $course_item->item_id;
		$section_pricing  = splms_get_section_pricing_with_access( $section_id, $user_id );
		$section_stats    = splms_get_section_content_stats( $section_id );
		$section_duration = splms_get_section_duration( $section_id );

		// Calculate total stats.
		$total_videos += $section_stats['lessons'];
		$total_notes  += $section_stats['quizzes'];

		// Convert duration to minutes.
		if ( $section_duration ) {
			preg_match_all( '/(\d+)hr|(\d+)min/', $section_duration, $matches );
			$hours               = isset( $matches[1][0] ) && '' !== $matches[1][0] ? (int) $matches[1][0] : 0;
			$minutes             = isset( $matches[2][0] ) && '' !== $matches[2][0] ? (int) $matches[2][0] : 0;
			$total_duration_min += ( $hours * 60 ) + $minutes;
		}

		$all_sections[] = array(
			'id'           => $section_id,
			'title'        => get_the_title( $section_id ),
			'is_purchased' => in_array( $section_id, splms_get_user_purchased_sections( $course_id, $user_id ), true ),
			'pricing'      => $section_pricing,
			'stats'        => $section_stats,
			'duration'     => $section_duration,
			'permalink'    => get_permalink( $section_id ),
		);
	}
}

// Format total duration.
$total_duration_hours   = floor( $total_duration_min / 60 );
$total_duration_minutes = $total_duration_min % 60;
$formatted_duration     = '';
if ( $total_duration_hours > 0 ) {
	/* translators: %d: Number of hours. */
	$formatted_duration .= sprintf( _n( '%dhr', '%dhr', $total_duration_hours, 'skillpulse-lms' ), $total_duration_hours ) . ' ';
}
if ( $total_duration_minutes > 0 ) {
	/* translators: %d: Number of minutes. */
	$formatted_duration .= sprintf( _n( '%dmin', '%dmin', $total_duration_minutes, 'skillpulse-lms' ), $total_duration_minutes );
}

// Get university/institution.
$instructor_university = get_the_author_meta( 'university', $author_id );
if ( empty( $instructor_university ) ) {
	$instructor_university = get_the_author_meta( 'institution', $author_id );
}

// Get access info.
$access_info        = splms_get_course_access_info( $course_id, $user_id );
$course_access_type = isset( $access_info['course_access_type'] ) ? $access_info['course_access_type'] : 'public_free';
$enrollment_dates   = splms_get_course_enrollment_dates( $course_id );
$capacity_info      = splms_get_course_max_enrollment_info( $course_id );
$is_enrolled        = splms_is_user_enrolled( $course_id );
$can_wishlist       = splms_get_setting( 'enable_course_wishlist', true ) && ! $is_enrolled;
$user_wishlist      = is_user_logged_in() ? get_user_meta( $user_id, '_splms_course_wishlist', true ) : array();
$user_wishlist      = is_array( $user_wishlist ) ? array_map( 'intval', $user_wishlist ) : array();
$in_wishlist        = in_array( $course_id, $user_wishlist, true );
?>

<div class="splms-hero-v2">
	<div class="splms-hero-v2__container">
		<!-- Top Section: Course Title and Instructor Info -->
		<div class="splms-hero-v2__header">
			<!-- Left: Course Image -->
			<div class="splms-hero-v2__thumbnail">
				<?php
				if ( has_post_thumbnail() ) {
					the_post_thumbnail( 'medium', array( 'class' => 'splms-hero-v2__image' ) );
				} else {
					echo '<div class="splms-hero-v2__placeholder"></div>';
				}
				?>
			</div>

			<!-- Center: Course Info -->
			<div class="splms-hero-v2__info">
				<h1 class="splms-hero-v2__title"><?php the_title(); ?></h1>

				<!-- Instructor Name (Arabic) -->
				<div class="splms-hero-v2__instructor-name">
					<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>">
						<?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?>
					</a>
				</div>

				<!-- Institution -->
				<?php if ( $instructor_university ) { ?>
					<div class="splms-hero-v2__institution">
						<?php echo esc_html( $instructor_university ); ?>
					</div>
				<?php } ?>

				<!-- Rating and Stats -->
				<div class="splms-hero-v2__rating-stats">
					<?php if ( $rating_summary['average_rating'] > 0 ) { ?>
						<div class="splms-hero-v2__rating">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
							</svg>
							<span class="splms-hero-v2__rating-value"><?php echo number_format( $rating_summary['average_rating'], 1 ); ?>/5</span>
						</div>
					<?php } ?>
					<span class="splms-hero-v2__separator">•</span>
					<span class="splms-hero-v2__stat">
						<?php
						/* translators: %d: Number of courses. */
						printf( esc_html( _n( '%d Course', '%d Courses', $instructor_courses_count, 'skillpulse-lms' ) ), (int) $instructor_courses_count );
						?>
					</span>
				</div>
			</div>

			<!-- Right: WhatsApp Group -->
			<div class="splms-hero-v2__actions">
				<button type="button" class="splms-hero-v2__whatsapp-btn">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
						<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
					</svg>
					<?php esc_html_e( 'Subscribers Group', 'skillpulse-lms' ); ?>
				</button>
			</div>
		</div>

		<!-- Stats Bar -->
		<div class="splms-hero-v2__stats-bar">
			<div class="splms-hero-v2__stat-item">
				<div class="splms-hero-v2__stat-value">
					<?php
					// Format subscriber count.
					if ( $students_count >= 1000 ) {
						echo esc_html( number_format( $students_count / 1000, 2 ) . 'K' );
					} else {
						echo esc_html( number_format( $students_count ) );
					}
					?>
				</div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Subscribers', 'skillpulse-lms' ); ?></div>
			</div>

			<div class="splms-hero-v2__stat-item">
				<div class="splms-hero-v2__stat-value"><?php echo esc_html( $formatted_duration ); ?></div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Total Duration', 'skillpulse-lms' ); ?></div>
			</div>

			<div class="splms-hero-v2__stat-item">
				<div class="splms-hero-v2__stat-value"><?php echo esc_html( $total_videos ); ?></div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Videos', 'skillpulse-lms' ); ?></div>
			</div>
		</div>

		<!-- Instructor Card -->
		<div class="splms-hero-v2__instructor-card">
			<h3 class="splms-hero-v2__section-title"><?php esc_html_e( 'Instructed by', 'skillpulse-lms' ); ?></h3>
			<div class="splms-hero-v2__instructor-details">
				<!-- Avatar -->
				<div class="splms-hero-v2__instructor-avatar">
					<?php echo get_avatar( $author_id, 48, '', get_the_author_meta( 'display_name', $author_id ), array( 'class' => 'splms-avatar' ) ); ?>
				</div>

				<!-- Name and Info -->
				<div class="splms-hero-v2__instructor-info">
					<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>" class="splms-hero-v2__instructor-link">
						<?php echo esc_html( get_the_author_meta( 'display_name', $author_id ) ); ?>
					</a>
					<?php if ( $instructor_stats['instructor_rating'] > 0 ) { ?>
						<div class="splms-hero-v2__instructor-rating">
							<?php for ( $i = 0; $i < 5; $i++ ) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo esc_attr( $i <= $instructor_stats['instructor_rating'] ? 'currentColor' : 'none' ); ?>" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="2"/>
								</svg>
							<?php endfor; ?>
							<span class="splms-hero-v2__rating-number"><?php echo esc_html( $instructor_stats['instructor_rating'] ); ?>/5</span>
						</div>
					<?php } ?>
				</div>
			</div>

			<!-- Instructor Stats -->
			<div class="splms-hero-v2__instructor-stats">
				<div class="splms-hero-v2__instructor-stat">
					<span class="splms-hero-v2__instructor-stat-value">
						<?php
						// Format views.
						if ( $instructor_stats['total_views'] >= 1000000 ) {
							echo esc_html( number_format( $instructor_stats['total_views'] / 1000000, 2 ) . 'M' );
						} elseif ( $instructor_stats['total_views'] >= 1000 ) {
							echo esc_html( number_format( $instructor_stats['total_views'] / 1000, 2 ) . 'K' );
						} else {
							echo esc_html( number_format( $instructor_stats['total_views'] ) );
						}
						?>
						<?php esc_html_e( 'Views', 'skillpulse-lms' ); ?>
					</span>
				</div>
				<span class="splms-hero-v2__separator">•</span>
				<div class="splms-hero-v2__instructor-stat">
					<span class="splms-hero-v2__instructor-stat-value">
						<?php
						// Format subscribers.
						if ( $instructor_stats['total_subscribers'] >= 1000 ) {
							echo esc_html( number_format( $instructor_stats['total_subscribers'] / 1000, 2 ) . 'K' );
						} else {
							echo esc_html( number_format( $instructor_stats['total_subscribers'] ) );
						}
						?>
						<?php esc_html_e( 'Subscribers', 'skillpulse-lms' ); ?>
					</span>
				</div>
				<span class="splms-hero-v2__separator">•</span>
				<div class="splms-hero-v2__instructor-stat">
					<span class="splms-hero-v2__instructor-stat-value">
						<?php
						/* translators: %d: Number of subjects. */
						printf( esc_html( _n( '%d Subject', '%d Subjects', $instructor_stats['subjects_count'], 'skillpulse-lms' ) ), (int) $instructor_stats['subjects_count'] );
						?>
					</span>
				</div>
			</div>
		</div>
	</div>

	<!-- Right Sidebar: Course Sections -->
	<div class="splms-hero-v2__sidebar">
		<div class="splms-hero-v2__sidebar-card">
			<h3 class="splms-hero-v2__sidebar-title"><?php esc_html_e( 'Courses', 'skillpulse-lms' ); ?></h3>

			<!-- Sections List -->
			<div class="splms-hero-v2__sections-list">
				<?php foreach ( $all_sections as $index => $section ) : ?>
					<a href="<?php echo esc_url( $section['permalink'] ); ?>" class="splms-hero-v2__section-item">
						<div class="splms-hero-v2__section-content">
							<h4 class="splms-hero-v2__section-name"><?php echo esc_html( $section['title'] ); ?></h4>
							<div class="splms-hero-v2__section-label"><?php esc_html_e( 'Section', 'skillpulse-lms' ); ?></div>
							<div class="splms-hero-v2__section-meta">
								<?php if ( $section['duration'] ) : ?>
									<span><?php echo esc_html( $section['duration'] ); ?></span>
								<?php endif; ?>
								<?php if ( $section['duration'] && $section['stats']['lessons'] > 0 ) : ?>
									<span class="splms-hero-v2__meta-separator">•</span>
								<?php endif; ?>
								<?php if ( $section['stats']['lessons'] > 0 ) : ?>
									<span>
										<?php
										printf(
											/* translators: %d: Number of videos. */
											esc_html( _n( '%d Video', '%d Videos', $section['stats']['lessons'], 'skillpulse-lms' ) ),
											(int) $section['stats']['lessons']
										);
										?>
									</span>
								<?php endif; ?>
								<?php if ( $section['stats']['quizzes'] > 0 ) : ?>
									<span class="splms-hero-v2__meta-separator">•</span>
									<span>
										<?php
										printf(
											/* translators: %d: Number of notes. */
											esc_html( _n( '%d Note', '%d Notes', $section['stats']['quizzes'], 'skillpulse-lms' ) ),
											(int) $section['stats']['quizzes']
										);
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="splms-hero-v2__section-actions">
							<?php if ( $section['is_purchased'] ) : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--purchased">
									<?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?>
								</span>
							<?php elseif ( $section['pricing']['is_free'] ) : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--free">
									<?php esc_html_e( 'Free', 'skillpulse-lms' ); ?>
								</span>
							<?php else : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--paid">
									<?php echo esc_html( get_splms_price_format( $section['pricing']['effective_price'] ) ); ?>
								</span>
							<?php endif; ?>
							<svg class="splms-hero-v2__arrow-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M9 5L16 12L9 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
					</a>
				<?php endforeach; ?>
			</div>

			<!-- Course Enrollment Card -->
			<div class="splms-hero-v2__bundle-offer">
				<?php
				$course_type_data = splms_get_course_type_data( $access_info );
				$full_price       = isset( $access_info['effective_price'] ) ? $access_info['effective_price'] : 0;
				$original_price   = isset( $access_info['regular_price'] ) ? $access_info['regular_price'] : $full_price;
				$discount_percent = 0;

				if ( $original_price > 0 && $full_price < $original_price ) {
					$discount_percent = round( ( ( $original_price - $full_price ) / $original_price ) * 100 );
				}

				// Show discount badge for paid courses with discount.
				if ( 'public_paid' === $course_access_type && $discount_percent > 0 ) :
					?>
					<div class="splms-hero-v2__discount-badge">
						<?php
						/* translators: %d: Discount percentage. */
						printf( esc_html__( 'At %d%% OFF', 'skillpulse-lms' ), (int) $discount_percent );
						?>
					</div>
				<?php endif; ?>

				<div class="splms-hero-v2__bundle-text"><?php esc_html_e( 'Get all courses', 'skillpulse-lms' ); ?></div>

				<?php
				// Display pricing based on course type.
				if ( 'public_free' === $course_access_type ) :
					?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--free">
							<?php esc_html_e( 'Free', 'skillpulse-lms' ); ?>
						</span>
					</div>
				<?php elseif ( 'public_paid' === $course_access_type && splms_is_paid_courses_enabled() ) : ?>
					<div class="splms-hero-v2__bundle-price">
						<span class="splms-hero-v2__bundle-price-current">
							<?php echo esc_html( get_splms_price_format( $full_price ) ); ?>
						</span>
						<?php if ( $discount_percent > 0 ) : ?>
							<span class="splms-hero-v2__bundle-price-original">
								<?php echo esc_html( get_splms_price_format( $original_price ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php elseif ( 'invitation_only' === $course_access_type ) : ?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--invitation">
							<?php esc_html_e( 'Invitation Only', 'skillpulse-lms' ); ?>
						</span>
					</div>
				<?php elseif ( 'prerequisite_required' === $course_access_type ) : ?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--prerequisite">
							<?php esc_html_e( 'Prerequisite Required', 'skillpulse-lms' ); ?>
						</span>
					</div>
					<?php
					// Get prerequisite course info.
					$prerequisite_courses = isset( $access_info['prerequisite_courses'] ) ? $access_info['prerequisite_courses'] : array();
					if ( ! empty( $prerequisite_courses ) ) {
						$prereq_course_id = $prerequisite_courses[0];
						?>
						<div class="splms-hero-v2__prerequisite-info">
							<p class="splms-hero-v2__prerequisite-text">
								<?php esc_html_e( 'Complete the prerequisite course first:', 'skillpulse-lms' ); ?>
							</p>
							<a href="<?php echo esc_url( get_permalink( $prereq_course_id ) ); ?>" class="splms-hero-v2__prerequisite-link">
								<?php echo esc_html( get_the_title( $prereq_course_id ) ); ?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M9 5L16 12L9 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</a>
						</div>
						<?php
					}
					?>
				<?php endif; ?>

				<?php
				// Show enrollment button based on course type.
				if ( 'public_free' === $course_access_type ) :
					?>
					<a href="<?php echo esc_url( add_query_arg( 'enroll', 'free', get_permalink( $course_id ) ) ); ?>" class="splms-hero-v2__enroll-btn splms-hero-v2__enroll-btn--free">
						<?php esc_html_e( 'Enroll Now', 'skillpulse-lms' ); ?>
					</a>
				<?php elseif ( 'public_paid' === $course_access_type && splms_is_paid_courses_enabled() && $full_price > 0 ) : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
					echo splms_render_enrollment_button(
						$course_id,
						is_user_logged_in() ? $user_id : 0,
						$access_info,
						$enrollment_dates,
						$capacity_info,
						$is_enrolled
					);
					?>
				<?php elseif ( 'invitation_only' !== $course_access_type && 'prerequisite_required' !== $course_access_type ) : ?>
					<?php
					// For other course types, show generic enrollment button.
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
					echo splms_render_enrollment_button(
						$course_id,
						is_user_logged_in() ? $user_id : 0,
						$access_info,
						$enrollment_dates,
						$capacity_info,
						$is_enrolled
					);
					?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<style>
.splms-hero-v2 {
	display: grid;
	grid-template-columns: 1fr 400px;
	gap: 32px;
	padding: 32px 0;
	max-width: 1400px;
	margin: 0 auto;
}

.splms-hero-v2__container {
	display: flex;
	flex-direction: column;
	gap: 24px;
}

/* Header Section */
.splms-hero-v2__header {
	display: flex;
	gap: 24px;
	align-items: flex-start;
}

.splms-hero-v2__thumbnail {
	width: 200px;
	height: 200px;
	flex-shrink: 0;
	border-radius: 12px;
	overflow: hidden;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.splms-hero-v2__image {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.splms-hero-v2__placeholder {
	width: 100%;
	height: 100%;
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.splms-hero-v2__info {
	flex: 1;
}

.splms-hero-v2__title {
	margin: 0 0 8px 0;
	font-size: 32px;
	font-weight: 700;
	color: #111827;
	line-height: 1.2;
}

.splms-hero-v2__instructor-name {
	margin-bottom: 4px;
}

.splms-hero-v2__instructor-name a {
	font-size: 18px;
	color: #6b7280;
	text-decoration: none;
	transition: color 0.2s;
}

.splms-hero-v2__instructor-name a:hover {
	color: #7e75ff;
}

.splms-hero-v2__institution {
	font-size: 16px;
	color: #6b7280;
	margin-bottom: 12px;
}

.splms-hero-v2__rating-stats {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 14px;
	color: #6b7280;
}

.splms-hero-v2__rating {
	display: flex;
	align-items: center;
	gap: 4px;
	color: #f59e0b;
}

.splms-hero-v2__rating-value {
	font-weight: 600;
	color: #111827;
}

.splms-hero-v2__separator {
	color: #d1d5db;
}

.splms-hero-v2__actions {
	flex-shrink: 0;
}

.splms-hero-v2__whatsapp-btn {
	display: flex;
	align-items: center;
	gap: 8px;
	padding: 12px 24px;
	background: #25d366;
	color: #fff;
	border: none;
	border-radius: 8px;
	font-size: 14px;
	font-weight: 600;
	cursor: pointer;
	transition: background 0.2s;
}

.splms-hero-v2__whatsapp-btn:hover {
	background: #20ba5a;
}

/* Stats Bar */
.splms-hero-v2__stats-bar {
	display: flex;
	gap: 48px;
	padding: 24px 0;
	border-top: 1px solid #e5e7eb;
	border-bottom: 1px solid #e5e7eb;
}

.splms-hero-v2__stat-item {
	text-align: center;
}

.splms-hero-v2__stat-value {
	font-size: 24px;
	font-weight: 700;
	color: #111827;
	margin-bottom: 4px;
}

.splms-hero-v2__stat-label {
	font-size: 14px;
	color: #6b7280;
}

/* Instructor Card */
.splms-hero-v2__instructor-card {
	padding: 24px;
	background: #f9fafb;
	border-radius: 12px;
}

.splms-hero-v2__section-title {
	margin: 0 0 16px 0;
	font-size: 18px;
	font-weight: 700;
	color: #111827;
}

.splms-hero-v2__instructor-details {
	display: flex;
	gap: 12px;
	margin-bottom: 16px;
}

.splms-hero-v2__instructor-avatar {
	flex-shrink: 0;
}

.splms-hero-v2__instructor-avatar img {
	width: 48px;
	height: 48px;
	border-radius: 50%;
}

.splms-hero-v2__instructor-info {
	flex: 1;
}

.splms-hero-v2__instructor-link {
	display: block;
	font-size: 16px;
	font-weight: 600;
	color: #111827;
	text-decoration: none;
	margin-bottom: 4px;
	transition: color 0.2s;
}

.splms-hero-v2__instructor-link:hover {
	color: #7e75ff;
}

.splms-hero-v2__instructor-rating {
	display: flex;
	align-items: center;
	gap: 2px;
	color: #f59e0b;
}

.splms-hero-v2__rating-number {
	margin-left: 4px;
	font-size: 13px;
	color: #6b7280;
}

.splms-hero-v2__instructor-stats {
	display: flex;
	align-items: center;
	gap: 8px;
	font-size: 14px;
	color: #6b7280;
}

.splms-hero-v2__instructor-stat-value {
	font-weight: 500;
}

/* Sidebar */
.splms-hero-v2__sidebar {
	position: sticky;
	top: 32px;
	height: fit-content;
}

.splms-hero-v2__sidebar-card {
	background: #fff;
	border: 1px solid #e5e7eb;
	border-radius: 12px;
	padding: 24px;
	box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.splms-hero-v2__sidebar-title {
	margin: 0 0 16px 0;
	font-size: 20px;
	font-weight: 700;
	color: #111827;
}

.splms-hero-v2__sections-list {
	display: flex;
	flex-direction: column;
	gap: 0;
	margin-bottom: 24px;
	max-height: 500px;
	overflow-y: auto;
	border: 1px solid #e5e7eb;
	border-radius: 8px;
}

.splms-hero-v2__section-item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 16px;
	text-decoration: none;
	color: inherit;
	transition: background 0.2s;
	border-bottom: 1px solid #f3f4f6;
}

.splms-hero-v2__section-item:last-child {
	border-bottom: none;
}

.splms-hero-v2__section-item:hover {
	background: #f9fafb;
}

.splms-hero-v2__section-content {
	flex: 1;
	min-width: 0;
	padding-right: 16px;
}

.splms-hero-v2__section-name {
	margin: 0 0 6px 0;
	font-size: 16px;
	font-weight: 600;
	line-height: 1.4;
	color: #111827;
}

.splms-hero-v2__section-label {
	font-size: 14px;
	color: #6b7280;
	margin-bottom: 8px;
	font-weight: 400;
}

.splms-hero-v2__section-meta {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	gap: 4px;
	font-size: 14px;
	color: #6b7280;
}

.splms-hero-v2__meta-separator {
	margin: 0 4px;
	color: #d1d5db;
}

.splms-hero-v2__section-actions {
	display: flex;
	align-items: center;
	gap: 8px;
	flex-shrink: 0;
}

.splms-hero-v2__price-badge {
	display: inline-flex;
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 14px;
	font-weight: 600;
	white-space: nowrap;
	transition: all 0.2s;
}

.splms-hero-v2__price-badge--free {
	background: #ecfdf5;
	color: #059669;
}

.splms-hero-v2__price-badge--paid {
	background: #fee2e2;
	color: #dc2626;
}

.splms-hero-v2__price-badge--purchased {
	background: #dbeafe;
	color: #1e40af;
}

.splms-hero-v2__arrow-icon {
	color: #9ca3af;
	flex-shrink: 0;
}

/* Bundle Offer */
.splms-hero-v2__bundle-offer {
	padding: 20px;
	background: #fff;
	border-radius: 8px;
	border: 1px solid #e5e7eb;
	text-align: left;
}

.splms-hero-v2__discount-badge {
	display: inline-block;
	padding: 4px 10px;
	background: #dc2626;
	color: #fff;
	border-radius: 4px;
	font-size: 13px;
	font-weight: 600;
	margin-bottom: 12px;
}

.splms-hero-v2__bundle-text {
	font-size: 15px;
	font-weight: 400;
	color: #6b7280;
	margin-bottom: 8px;
}

.splms-hero-v2__bundle-price {
	display: flex;
	align-items: center;
	gap: 12px;
	margin-bottom: 16px;
}

.splms-hero-v2__bundle-price-current {
	font-size: 22px;
	font-weight: 700;
	color: #111827;
}

.splms-hero-v2__bundle-price-original {
	font-size: 16px;
	color: #9ca3af;
	text-decoration: line-through;
	font-weight: 400;
}

/* Course Type Badges */
.splms-hero-v2__course-type {
	margin-bottom: 16px;
}

.splms-hero-v2__type-badge {
	display: inline-flex;
	padding: 8px 16px;
	border-radius: 20px;
	font-size: 15px;
	font-weight: 600;
	white-space: nowrap;
}

.splms-hero-v2__type-badge--free {
	background: #ecfdf5;
	color: #059669;
}

.splms-hero-v2__type-badge--invitation {
	background: #fef3c7;
	color: #d97706;
}

.splms-hero-v2__type-badge--prerequisite {
	background: #dbeafe;
	color: #1e40af;
}

/* Prerequisite Info */
.splms-hero-v2__prerequisite-info {
	margin-top: 12px;
	margin-bottom: 16px;
}

.splms-hero-v2__prerequisite-text {
	margin: 0 0 8px 0;
	font-size: 14px;
	color: #6b7280;
}

.splms-hero-v2__prerequisite-link {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	font-size: 15px;
	color: #7e75ff;
	text-decoration: none;
	font-weight: 600;
	transition: color 0.2s;
}

.splms-hero-v2__prerequisite-link:hover {
	color: #6366f1;
}

.splms-hero-v2__prerequisite-link svg {
	width: 14px;
	height: 14px;
}

/* Enrollment Buttons */
.splms-hero-v2__enroll-btn {
	display: block;
	width: 100%;
	padding: 14px 24px;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 600;
	text-align: center;
	text-decoration: none;
	cursor: pointer;
	transition: background 0.2s;
}

.splms-hero-v2__enroll-btn--free {
	background: #059669;
	color: #fff;
}

.splms-hero-v2__enroll-btn--free:hover {
	background: #047857;
}

.splms-hero-v2__bundle-offer button[class*="enroll"],
.splms-hero-v2__bundle-offer a[class*="enroll"] {
	width: 100%;
	padding: 14px 24px;
	background: #991b1b !important;
	color: #fff !important;
	border: none;
	border-radius: 8px;
	font-size: 16px;
	font-weight: 600;
	text-align: center;
	text-decoration: none;
	cursor: pointer;
	transition: background 0.2s;
	display: block;
}

.splms-hero-v2__bundle-offer button[class*="enroll"]:hover,
.splms-hero-v2__bundle-offer a[class*="enroll"]:hover {
	background: #7f1d1d !important;
}

/* Responsive */
@media (max-width: 1024px) {
	.splms-hero-v2 {
		grid-template-columns: 1fr;
	}

	.splms-hero-v2__sidebar {
		position: static;
	}
}

@media (max-width: 768px) {
	.splms-hero-v2__header {
		flex-direction: column;
		align-items: center;
		text-align: center;
	}

	.splms-hero-v2__thumbnail {
		width: 150px;
		height: 150px;
	}

	.splms-hero-v2__title {
		font-size: 24px;
	}

	.splms-hero-v2__stats-bar {
		flex-wrap: wrap;
		gap: 24px;
	}

	.splms-hero-v2__stat-item {
		flex: 1;
		min-width: 100px;
	}
}
</style>