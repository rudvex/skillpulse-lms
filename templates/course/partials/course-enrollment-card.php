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



$splms_course_id      = get_the_ID();
$splms_user_id        = get_current_user_id();
$splms_author_id      = get_the_author_meta( 'ID' );
$splms_rating_summary = splms_get_course_rating( $splms_course_id );

// Ensure we have the proper structure.
if ( ! is_array( $splms_rating_summary ) ) {
	$splms_rating_summary = array();
}

// Set default values if keys are missing.
$splms_rating_summary = array_merge(
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
	$splms_rating_summary
);

// Get instructor stats.
$splms_instructor_courses_count = count_user_posts( $splms_author_id, SPLMS_POST_TYPES['course'] );
$splms_instructor_meta          = get_user_meta( $splms_author_id, 'splms_instructor_stats', true );

if ( ! is_array( $splms_instructor_meta ) ) {
	$splms_instructor_meta = array();
}

$splms_instructor_stats = array_merge(
	array(
		'total_subscribers' => 0,
		'total_views'       => 0,
		'subjects_count'    => 0,
		'instructor_rating' => 0,
	),
	$splms_instructor_meta
);

// Get course statistics.
$splms_duration       = splms_get_course_duration( $splms_course_id );
$splms_students_count = splms_get_course_enrollment_count( $splms_course_id );

// Get all course sections.
$splms_all_sections       = array();
$splms_total_videos       = 0;
$splms_total_notes        = 0;
$splms_total_duration_min = 0;

$splms_course_items_query = SPLMS_Course_Items_Query::get_instance();
if ( $splms_course_items_query ) {
	$splms_course_items = $splms_course_items_query->get_items( $splms_course_id );

	foreach ( $splms_course_items as $splms_course_item ) {
		if ( SPLMS_POST_TYPES['section'] !== $splms_course_item->item_type ) {
			continue;
		}

		$splms_section_id       = $splms_course_item->item_id;
		$splms_section_pricing  = splms_get_section_pricing_with_access( $splms_section_id, $splms_user_id );
		$splms_section_stats    = splms_get_section_content_stats( $splms_section_id );
		$splms_section_duration = splms_get_section_duration( $splms_section_id );

		// Calculate total stats.
		$splms_total_videos += $splms_section_stats['lessons'];
		$splms_total_notes  += $splms_section_stats['quizzes'];

		// Convert duration to minutes.
		if ( $splms_section_duration ) {
			preg_match_all( '/(\d+)hr|(\d+)min/', $splms_section_duration, $splms_matches );
			$splms_hours               = isset( $splms_matches[1][0] ) && '' !== $splms_matches[1][0] ? (int) $splms_matches[1][0] : 0;
			$splms_minutes             = isset( $splms_matches[2][0] ) && '' !== $splms_matches[2][0] ? (int) $splms_matches[2][0] : 0;
			$splms_total_duration_min += ( $splms_hours * 60 ) + $splms_minutes;
		}

		$splms_all_sections[] = array(
			'id'           => $splms_section_id,
			'title'        => get_the_title( $splms_section_id ),
			'is_purchased' => in_array( $splms_section_id, splms_get_user_purchased_sections( $splms_course_id, $splms_user_id ), true ),
			'pricing'      => $splms_section_pricing,
			'stats'        => $splms_section_stats,
			'duration'     => $splms_section_duration,
			'permalink'    => get_permalink( $splms_section_id ),
		);
	}
}

// Format total duration.
$splms_total_duration_hours   = floor( $splms_total_duration_min / 60 );
$splms_total_duration_minutes = $splms_total_duration_min % 60;
$splms_formatted_duration     = '';
if ( $splms_total_duration_hours > 0 ) {
	/* translators: %d: Number of hours. */
	$splms_formatted_duration .= sprintf( _n( '%dhr', '%dhr', $splms_total_duration_hours, 'skillpulse-lms' ), $splms_total_duration_hours ) . ' ';
}
if ( $splms_total_duration_minutes > 0 ) {
	/* translators: %d: Number of minutes. */
	$splms_formatted_duration .= sprintf( _n( '%dmin', '%dmin', $splms_total_duration_minutes, 'skillpulse-lms' ), $splms_total_duration_minutes );
}

// Get university/institution.
$splms_instructor_university = get_the_author_meta( 'university', $splms_author_id );
if ( empty( $splms_instructor_university ) ) {
	$splms_instructor_university = get_the_author_meta( 'institution', $splms_author_id );
}

// Get access info.
$splms_access_info        = splms_get_course_access_info( $splms_course_id, $splms_user_id );
$splms_course_access_type = isset( $splms_access_info['course_access_type'] ) ? $splms_access_info['course_access_type'] : 'public_free';
$splms_enrollment_dates   = splms_get_course_enrollment_dates( $splms_course_id );
$splms_capacity_info      = splms_get_course_max_enrollment_info( $splms_course_id );
$splms_is_enrolled        = splms_is_user_enrolled( $splms_course_id );
$splms_can_wishlist       = splms_get_setting( 'enable_course_wishlist', true ) && ! $splms_is_enrolled;
$splms_user_wishlist      = is_user_logged_in() ? get_user_meta( $splms_user_id, '_splms_course_wishlist', true ) : array();
$splms_user_wishlist      = is_array( $splms_user_wishlist ) ? array_map( 'intval', $splms_user_wishlist ) : array();
$splms_in_wishlist        = in_array( $splms_course_id, $splms_user_wishlist, true );
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
					<a href="<?php echo esc_url( get_author_posts_url( $splms_author_id ) ); ?>">
						<?php echo esc_html( get_the_author_meta( 'display_name', $splms_author_id ) ); ?>
					</a>
				</div>

				<!-- Institution -->
				<?php if ( $splms_instructor_university ) { ?>
					<div class="splms-hero-v2__institution">
						<?php echo esc_html( $splms_instructor_university ); ?>
					</div>
				<?php } ?>

				<!-- Rating and Stats -->
				<div class="splms-hero-v2__rating-stats">
					<?php if ( $splms_rating_summary['average_rating'] > 0 ) { ?>
						<div class="splms-hero-v2__rating">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
								<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/>
							</svg>
							<span class="splms-hero-v2__rating-value"><?php echo number_format( $splms_rating_summary['average_rating'], 1 ); ?>/5</span>
						</div>
					<?php } ?>
					<span class="splms-hero-v2__separator">•</span>
					<span class="splms-hero-v2__stat">
						<?php
						/* translators: %d: Number of courses. */
						printf( esc_html( _n( '%d Course', '%d Courses', $splms_instructor_courses_count, 'skillpulse-lms' ) ), (int) $splms_instructor_courses_count );
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
					if ( $splms_students_count >= 1000 ) {
						echo esc_html( number_format( $splms_students_count / 1000, 2 ) . 'K' );
					} else {
						echo esc_html( number_format( $splms_students_count ) );
					}
					?>
				</div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Subscribers', 'skillpulse-lms' ); ?></div>
			</div>

			<div class="splms-hero-v2__stat-item">
				<div class="splms-hero-v2__stat-value"><?php echo esc_html( $splms_formatted_duration ); ?></div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Total Duration', 'skillpulse-lms' ); ?></div>
			</div>

			<div class="splms-hero-v2__stat-item">
				<div class="splms-hero-v2__stat-value"><?php echo esc_html( $splms_total_videos ); ?></div>
				<div class="splms-hero-v2__stat-label"><?php esc_html_e( 'Videos', 'skillpulse-lms' ); ?></div>
			</div>
		</div>

		<!-- Instructor Card -->
		<div class="splms-hero-v2__instructor-card">
			<h3 class="splms-hero-v2__section-title"><?php esc_html_e( 'Instructed by', 'skillpulse-lms' ); ?></h3>
			<div class="splms-hero-v2__instructor-details">
				<!-- Avatar -->
				<div class="splms-hero-v2__instructor-avatar">
					<?php echo get_avatar( $splms_author_id, 48, '', get_the_author_meta( 'display_name', $splms_author_id ), array( 'class' => 'splms-avatar' ) ); ?>
				</div>

				<!-- Name and Info -->
				<div class="splms-hero-v2__instructor-info">
					<a href="<?php echo esc_url( get_author_posts_url( $splms_author_id ) ); ?>" class="splms-hero-v2__instructor-link">
						<?php echo esc_html( get_the_author_meta( 'display_name', $splms_author_id ) ); ?>
					</a>
					<?php if ( $splms_instructor_stats['instructor_rating'] > 0 ) { ?>
						<div class="splms-hero-v2__instructor-rating">
							<?php for ( $splms_i = 0; $splms_i < 5; $splms_i++ ) : ?>
								<svg width="14" height="14" viewBox="0 0 24 24" fill="<?php echo esc_attr( $splms_i <= $splms_instructor_stats['instructor_rating'] ? 'currentColor' : 'none' ); ?>" xmlns="http://www.w3.org/2000/svg">
									<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" stroke="currentColor" stroke-width="2"/>
								</svg>
							<?php endfor; ?>
							<span class="splms-hero-v2__rating-number"><?php echo esc_html( $splms_instructor_stats['instructor_rating'] ); ?>/5</span>
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
						if ( $splms_instructor_stats['total_views'] >= 1000000 ) {
							echo esc_html( number_format( $splms_instructor_stats['total_views'] / 1000000, 2 ) . 'M' );
						} elseif ( $splms_instructor_stats['total_views'] >= 1000 ) {
							echo esc_html( number_format( $splms_instructor_stats['total_views'] / 1000, 2 ) . 'K' );
						} else {
							echo esc_html( number_format( $splms_instructor_stats['total_views'] ) );
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
						if ( $splms_instructor_stats['total_subscribers'] >= 1000 ) {
							echo esc_html( number_format( $splms_instructor_stats['total_subscribers'] / 1000, 2 ) . 'K' );
						} else {
							echo esc_html( number_format( $splms_instructor_stats['total_subscribers'] ) );
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
						printf( esc_html( _n( '%d Subject', '%d Subjects', $splms_instructor_stats['subjects_count'], 'skillpulse-lms' ) ), (int) $splms_instructor_stats['subjects_count'] );
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
				<?php foreach ( $splms_all_sections as $splms_index => $splms_section ) : ?>
					<a href="<?php echo esc_url( $splms_section['permalink'] ); ?>" class="splms-hero-v2__section-item">
						<div class="splms-hero-v2__section-content">
							<h4 class="splms-hero-v2__section-name"><?php echo esc_html( $splms_section['title'] ); ?></h4>
							<div class="splms-hero-v2__section-label"><?php esc_html_e( 'Section', 'skillpulse-lms' ); ?></div>
							<div class="splms-hero-v2__section-meta">
								<?php if ( $splms_section['duration'] ) : ?>
									<span><?php echo esc_html( $splms_section['duration'] ); ?></span>
								<?php endif; ?>
								<?php if ( $splms_section['duration'] && $splms_section['stats']['lessons'] > 0 ) : ?>
									<span class="splms-hero-v2__meta-separator">•</span>
								<?php endif; ?>
								<?php if ( $splms_section['stats']['lessons'] > 0 ) : ?>
									<span>
										<?php
										printf(
											/* translators: %d: Number of videos. */
											esc_html( _n( '%d Video', '%d Videos', $splms_section['stats']['lessons'], 'skillpulse-lms' ) ),
											(int) $splms_section['stats']['lessons']
										);
										?>
									</span>
								<?php endif; ?>
								<?php if ( $splms_section['stats']['quizzes'] > 0 ) : ?>
									<span class="splms-hero-v2__meta-separator">•</span>
									<span>
										<?php
										printf(
											/* translators: %d: Number of notes. */
											esc_html( _n( '%d Note', '%d Notes', $splms_section['stats']['quizzes'], 'skillpulse-lms' ) ),
											(int) $splms_section['stats']['quizzes']
										);
										?>
									</span>
								<?php endif; ?>
							</div>
						</div>
						<div class="splms-hero-v2__section-actions">
							<?php if ( $splms_section['is_purchased'] ) : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--purchased">
									<?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?>
								</span>
							<?php elseif ( $splms_section['pricing']['is_free'] ) : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--free">
									<?php esc_html_e( 'Free', 'skillpulse-lms' ); ?>
								</span>
							<?php else : ?>
								<span class="splms-hero-v2__price-badge splms-hero-v2__price-badge--paid">
									<?php echo esc_html( splms_get_price_format( $splms_section['pricing']['effective_price'] ) ); ?>
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
				$splms_course_type_data = splms_get_course_type_data( $splms_access_info );
				$splms_full_price       = isset( $splms_access_info['effective_price'] ) ? $splms_access_info['effective_price'] : 0;
				$splms_original_price   = isset( $splms_access_info['regular_price'] ) ? $splms_access_info['regular_price'] : $splms_full_price;
				$splms_discount_percent = 0;

				if ( $splms_original_price > 0 && $splms_full_price < $splms_original_price ) {
					$splms_discount_percent = round( ( ( $splms_original_price - $splms_full_price ) / $splms_original_price ) * 100 );
				}

				// Show discount badge for paid courses with discount.
				if ( 'public_paid' === $splms_course_access_type && $splms_discount_percent > 0 ) :
					?>
					<div class="splms-hero-v2__discount-badge">
						<?php
						/* translators: %d: Discount percentage. */
						printf( esc_html__( 'At %d%% OFF', 'skillpulse-lms' ), (int) $splms_discount_percent );
						?>
					</div>
				<?php endif; ?>

				<div class="splms-hero-v2__bundle-text"><?php esc_html_e( 'Get all courses', 'skillpulse-lms' ); ?></div>

				<?php
				// Display pricing based on course type.
				if ( 'public_free' === $splms_course_access_type ) :
					?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--free">
							<?php esc_html_e( 'Free', 'skillpulse-lms' ); ?>
						</span>
					</div>
				<?php elseif ( 'public_paid' === $splms_course_access_type && splms_is_paid_courses_enabled() ) : ?>
					<div class="splms-hero-v2__bundle-price">
						<span class="splms-hero-v2__bundle-price-current">
							<?php echo esc_html( splms_get_price_format( $splms_full_price ) ); ?>
						</span>
						<?php if ( $splms_discount_percent > 0 ) : ?>
							<span class="splms-hero-v2__bundle-price-original">
								<?php echo esc_html( splms_get_price_format( $splms_original_price ) ); ?>
							</span>
						<?php endif; ?>
					</div>
				<?php elseif ( 'invitation_only' === $splms_course_access_type ) : ?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--invitation">
							<?php esc_html_e( 'Invitation Only', 'skillpulse-lms' ); ?>
						</span>
					</div>
				<?php elseif ( 'prerequisite_required' === $splms_course_access_type ) : ?>
					<div class="splms-hero-v2__course-type">
						<span class="splms-hero-v2__type-badge splms-hero-v2__type-badge--prerequisite">
							<?php esc_html_e( 'Prerequisite Required', 'skillpulse-lms' ); ?>
						</span>
					</div>
					<?php
					// Get prerequisite course info.
					$splms_prerequisite_courses = isset( $splms_access_info['prerequisite_courses'] ) ? $splms_access_info['prerequisite_courses'] : array();
					if ( ! empty( $splms_prerequisite_courses ) ) {
						$splms_prereq_course_id = $splms_prerequisite_courses[0];
						?>
						<div class="splms-hero-v2__prerequisite-info">
							<p class="splms-hero-v2__prerequisite-text">
								<?php esc_html_e( 'Complete the prerequisite course first:', 'skillpulse-lms' ); ?>
							</p>
							<a href="<?php echo esc_url( get_permalink( $splms_prereq_course_id ) ); ?>" class="splms-hero-v2__prerequisite-link">
								<?php echo esc_html( get_the_title( $splms_prereq_course_id ) ); ?>
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
				if ( 'public_free' === $splms_course_access_type ) :
					?>
					<a href="<?php echo esc_url( add_query_arg( 'enroll', 'free', get_permalink( $splms_course_id ) ) ); ?>" class="splms-hero-v2__enroll-btn splms-hero-v2__enroll-btn--free">
						<?php esc_html_e( 'Enroll Now', 'skillpulse-lms' ); ?>
					</a>
				<?php elseif ( 'public_paid' === $splms_course_access_type && splms_is_paid_courses_enabled() && $splms_full_price > 0 ) : ?>
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
					echo splms_render_enrollment_button(
						$splms_course_id,
						is_user_logged_in() ? $splms_user_id : 0,
						$splms_access_info,
						$splms_enrollment_dates,
						$splms_capacity_info,
						$splms_is_enrolled
					);
					?>
				<?php elseif ( 'invitation_only' !== $splms_course_access_type && 'prerequisite_required' !== $splms_course_access_type ) : ?>
					<?php
					// For other course types, show generic enrollment button.
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Function returns escaped HTML.
					echo splms_render_enrollment_button(
						$splms_course_id,
						is_user_logged_in() ? $splms_user_id : 0,
						$splms_access_info,
						$splms_enrollment_dates,
						$splms_capacity_info,
						$splms_is_enrolled
					);
					?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>
