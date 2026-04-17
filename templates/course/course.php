<?php
/**
 * Course card template part
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/content-course.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

extract( $args ); // phpcs:ignore

$course_id      = ! empty( $course_id ) ? $course_id : get_the_ID();
$course_post    = get_post( $course_id );
$difficulty     = splms_get_course_difficulty( $course_id );
$access_info    = splms_get_course_access_info( $course_id );
$duration       = splms_get_course_duration( $course_id );
$students_count = splms_get_course_enrollment_count( $course_id );
$rating_summary = splms_get_course_rating( $course_id );

// Ensure we have the proper structure.
if ( ! is_array( $rating_summary ) ) {
	$rating_summary = array();
}

// Set default values if keys are missing.
$rating_summary    = array_merge(
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
$is_enrolled       = splms_is_user_enrolled( $course_id );
$enrollment_status = splms_get_user_enrollment_status( $course_id );
$is_enable_rating  = splms_get_setting( 'enable_course_reviews', true );

// Wishlist: only show if NOT enrolled (pre-enrollment feature).
$can_wishlist = splms_get_setting( 'enable_course_wishlist', true ) && ! $is_enrolled;
$in_wishlist  = false;
if ( $can_wishlist && is_user_logged_in() ) {
	$user_wishlist = get_user_meta( get_current_user_id(), '_splms_course_wishlist', true );
	$user_wishlist = is_array( $user_wishlist ) ? array_map( 'intval', $user_wishlist ) : array();
	$in_wishlist   = in_array( $course_id, $user_wishlist, true );
}

/**
 * Hook: splms_before_single_course_card
 */
do_action( 'splms_before_single_course_card', $course_id );
?>

<div class="splms-course-card <?php echo esc_attr( $is_enrolled ? 'enrolled' : '' ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>">
	<div class="course-card-image">
		<a href="<?php the_permalink(); ?>" class="course-image-link">
			<?php splms_course_thumbnail( $course_id, 'medium' ); ?>
		</a>

		<div class="course-card-overlay">
			<span class="course-difficulty difficulty-<?php echo esc_attr( strtolower( $difficulty ) ); ?>">
				<?php echo esc_html( ucfirst( $difficulty ) ); ?>
			</span>
			<?php if ( $can_wishlist ) { ?>
				<button type="button" class="course-wishlist-btn wishlist-btn <?php echo esc_attr( $in_wishlist ? 'in-wishlist active' : '' ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>" aria-pressed="<?php echo esc_attr( $in_wishlist ? 'true' : 'false' ); ?>" title="<?php echo $in_wishlist ? esc_attr__( 'Remove from Wishlist', 'skillpulse-lms' ) : esc_attr__( 'Add to Wishlist', 'skillpulse-lms' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo esc_attr( $in_wishlist ? 'currentColor' : 'none' ); ?>" xmlns="http://www.w3.org/2000/svg">
						<path d="M20.84 4.61A5.5 5.5 0 0 0 16.5 2.5A5.5 5.5 0 0 0 12 5.5A5.5 5.5 0 0 0 7.5 2.5A5.5 5.5 0 0 0 3.16 4.61A5.5 5.5 0 0 0 2 8.89A5.5 5.5 0 0 0 3.16 13.17L12 22L20.84 13.17A5.5 5.5 0 0 0 22 8.89A5.5 5.5 0 0 0 20.84 4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</button>
			<?php } ?>
		</div>
	</div>

	<div class="course-card-content">
		<div class="course-author">
			<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" class="author-link">
				<span class="images">
					<?php echo get_avatar( get_the_author_meta( 'ID' ), 24, '', '', array( 'class' => 'author-avatar' ) ); ?>
				</span>
				<span class="author-name"><?php the_author(); ?></span>
			</a>
		</div>

		<h3 class="course-title">
			<a href="<?php the_permalink(); ?>" class="course-title-link">
				<?php echo esc_html( get_the_title( $course_post ) ); ?>
			</a>
		</h3>

		<div class="course-excerpt">
			<?php
			// Debug: Check if excerpt contains access restriction message.
			$excerpt = get_the_excerpt( $course_post );
			if ( false !== strpos( $excerpt, 'Access Restricted' ) || false !== strpos( $excerpt, 'Access Denied' ) ) {
				// Show course description from content instead.
				$content = get_the_content( null, false, $course_post );
				if ( ! empty( $content ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_trim_words output is safe.
					echo wp_trim_words( wp_strip_all_tags( $content ), 15, '...' );
				} else {
					// Use manual excerpt.
					echo esc_html__( 'Learn new skills with this comprehensive course designed to help you advance your knowledge and career.', 'skillpulse-lms' );
				}
			} else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_trim_words output is safe.
				echo wp_trim_words( $excerpt, 15, '...' );
			}
			?>
		</div>

		<div class="course-meta">

			<?php if ( $duration ) { ?>
				<div class="course-meta-item">
					<span class="icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<g clip-path="url(#clip0_191_13)">
								<path d="M2.5 10C2.5 10.9849 2.69399 11.9602 3.0709 12.8701C3.44781 13.7801 4.00026 14.6069 4.6967 15.3033C5.39314 15.9997 6.21993 16.5522 7.12987 16.9291C8.03982 17.306 9.01509 17.5 10 17.5C10.9849 17.5 11.9602 17.306 12.8701 16.9291C13.7801 16.5522 14.6069 15.9997 15.3033 15.3033C15.9997 14.6069 16.5522 13.7801 16.9291 12.8701C17.306 11.9602 17.5 10.9849 17.5 10C17.5 9.01509 17.306 8.03982 16.9291 7.12987C16.5522 6.21993 15.9997 5.39314 15.3033 4.6967C14.6069 4.00026 13.7801 3.44781 12.8701 3.0709C11.9602 2.69399 10.9849 2.5 10 2.5C9.01509 2.5 8.03982 2.69399 7.12987 3.0709C6.21993 3.44781 5.39314 4.00026 4.6967 4.6967C4.00026 5.39314 3.44781 6.21993 3.0709 7.12987C2.69399 8.03982 2.5 9.01509 2.5 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								<path d="M10 10L12.5 11.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								<path d="M10 5.83337V10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							</g>
							<defs>
								<clipPath id="clip0_191_13">
									<rect width="20" height="20" fill="white" />
								</clipPath>
							</defs>
						</svg>
					</span>
					<span class="text"><?php echo esc_html( $duration ); ?></span>
				</div>
			<?php } ?>

			<div class="course-meta-item">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
						<g clip-path="url(#clip0_191_18)">
							<path d="M6.66675 5.83333C6.66675 6.71739 7.01794 7.56523 7.64306 8.19036C8.26818 8.81548 9.11603 9.16667 10.0001 9.16667C10.8841 9.16667 11.732 8.81548 12.3571 8.19036C12.9822 7.56523 13.3334 6.71739 13.3334 5.83333C13.3334 4.94928 12.9822 4.10143 12.3571 3.47631C11.732 2.85119 10.8841 2.5 10.0001 2.5C9.11603 2.5 8.26818 2.85119 7.64306 3.47631C7.01794 4.10143 6.66675 4.94928 6.66675 5.83333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M5 17.5V15.8333C5 14.9493 5.35119 14.1014 5.97631 13.4763C6.60143 12.8512 7.44928 12.5 8.33333 12.5H11.6667C12.5507 12.5 13.3986 12.8512 14.0237 13.4763C14.6488 14.1014 15 14.9493 15 15.8333V17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</g>
						<defs>
							<clipPath id="clip0_191_18">
								<rect width="20" height="20" fill="white" />
							</clipPath>
						</defs>
					</svg>
				</span>
				<span class="text">
					<?php
					/* translators: %d: Number of students. */
					echo esc_html( sprintf( _n( '%d student', '%d students', $students_count, 'skillpulse-lms' ), (int) $students_count ) );
					?>
				</span>
			</div>
			<div class="course-price course-meta-item">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
						<g clip-path="url(#clip0_191_25)">
							<path d="M2.5 10C2.5 10.9849 2.69399 11.9602 3.0709 12.8701C3.44781 13.7801 4.00026 14.6069 4.6967 15.3033C5.39314 15.9997 6.21993 16.5522 7.12987 16.9291C8.03982 17.306 9.01509 17.5 10 17.5C10.9849 17.5 11.9602 17.306 12.8701 16.9291C13.7801 16.5522 14.6069 15.9997 15.3033 15.3033C15.9997 14.6069 16.5522 13.7801 16.9291 12.8701C17.306 11.9602 17.5 10.9849 17.5 10C17.5 9.01509 17.306 8.03982 16.9291 7.12987C16.5522 6.21993 15.9997 5.39314 15.3033 4.6967C14.6069 4.00026 13.7801 3.44781 12.8701 3.0709C11.9602 2.69399 10.9849 2.5 10 2.5C9.01509 2.5 8.03982 2.69399 7.12987 3.0709C6.21993 3.44781 5.39314 4.00026 4.6967 4.6967C4.00026 5.39314 3.44781 6.21993 3.0709 7.12987C2.69399 8.03982 2.5 9.01509 2.5 10Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M12.3333 7.50005C12.1824 7.23818 11.963 7.0223 11.6988 6.87551C11.4346 6.72872 11.1354 6.65654 10.8333 6.66672H9.16667C8.72464 6.66672 8.30072 6.84231 7.98816 7.15487C7.6756 7.46743 7.5 7.89135 7.5 8.33338C7.5 8.77541 7.6756 9.19933 7.98816 9.51189C8.30072 9.82445 8.72464 10 9.16667 10H10.8333C11.2754 10 11.6993 10.1756 12.0118 10.4882C12.3244 10.8008 12.5 11.2247 12.5 11.6667C12.5 12.1087 12.3244 12.5327 12.0118 12.8452C11.6993 13.1578 11.2754 13.3334 10.8333 13.3334H9.16667C8.86458 13.3436 8.56541 13.2714 8.30118 13.1246C8.03696 12.9778 7.81763 12.7619 7.66667 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
							<path d="M10 5.83337V14.1667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
						</g>
						<defs>
							<clipPath id="clip0_191_25">
								<rect width="20" height="20" fill="white" />
							</clipPath>
						</defs>
					</svg>
				</span>
				<span class="text">
					<?php
					// Display price based on course_mode.
					if ( ! empty( $access_info['course_mode'] ) ) {
						switch ( $access_info['course_mode'] ) {
							case 'free':
								echo '<span class="price-free">' . esc_html__( 'Free', 'skillpulse-lms' ) . '</span>';
								break;
							case 'paid':
								// Only show paid prices if paid courses are enabled.
								if ( splms_is_paid_courses_enabled() ) {
									if ( $access_info['final_price'] && $access_info['final_price'] < $access_info['price'] ) {
										echo '<span class="price-sale">' . esc_html( get_splms_price_format( $access_info['final_price'] ) ) . '</span>';
										echo '<span class="price-regular">' . esc_html( get_splms_price_format( $access_info['price'] ) ) . '</span>';
									} else {
										echo '<span class="price-current">' . esc_html( get_splms_price_format( $access_info['price'] ) ) . '</span>';
									}
								} else {
									echo '<span class="price-free">' . esc_html__( 'Free', 'skillpulse-lms' ) . '</span>';
								}
								break;
							case 'prerequisite':
								echo '<span class="price-prerequisite">' . esc_html__( 'Prerequisite', 'skillpulse-lms' ) . '</span>';
								break;
							case 'invitation':
								echo '<span class="price-invitation">' . esc_html__( 'Invitation Only', 'skillpulse-lms' ) . '</span>';
								break;
						}
					}
					?>
				</span>
			</div>
			<?php if ( $rating_summary['average_rating'] > 0 && $is_enable_rating ) { ?>
				<?php
				// Format review count for display.
				$review_count           = $rating_summary['total_reviews'];
				$average_rating         = $rating_summary['average_rating'];
				$review_count_formatted = $review_count;
				if ( $review_count >= 1000 ) {
					$review_count_formatted = number_format_i18n( $review_count / 1000, 2 );
					// Remove trailing zeros after decimal point.
					$review_count_formatted = rtrim( rtrim( $review_count_formatted, '0' ), '.' ) . 'k';
				} else {
					$review_count_formatted = number_format_i18n( $review_count );
				}
				?>
				<div class="course-rating course-meta-item">
					<span class="icon" data-rating="<?php echo esc_attr( $average_rating ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
							<g clip-path="url(#clip0_191_22)">
								<path d="M6.86922 6.11672L1.55255 6.88756L1.45838 6.90672C1.31583 6.94457 1.18588 7.01956 1.08179 7.12406C0.977707 7.22855 0.903219 7.3588 0.865934 7.5015C0.82865 7.64419 0.829905 7.79423 0.869572 7.93628C0.909239 8.07834 0.985896 8.20732 1.09172 8.31006L4.94338 12.0592L4.03505 17.3551L4.02422 17.4467C4.01549 17.5942 4.0461 17.7413 4.11292 17.873C4.17974 18.0047 4.28037 18.1163 4.4045 18.1963C4.52862 18.2764 4.67179 18.322 4.81934 18.3285C4.96689 18.335 5.11352 18.3022 5.24422 18.2334L9.99922 15.7334L14.7434 18.2334L14.8267 18.2717C14.9643 18.3259 15.1138 18.3425 15.2598 18.3199C15.4059 18.2972 15.5434 18.2361 15.658 18.1428C15.7727 18.0495 15.8605 17.9273 15.9124 17.7889C15.9643 17.6505 15.9785 17.5008 15.9534 17.3551L15.0442 12.0592L18.8975 8.30922L18.9625 8.23839C19.0554 8.12403 19.1163 7.9871 19.139 7.84155C19.1617 7.696 19.1454 7.54704 19.0918 7.40983C19.0382 7.27263 18.9492 7.15208 18.8338 7.06049C18.7184 6.96889 18.5808 6.9095 18.435 6.88839L13.1184 6.11672L10.7417 1.30006C10.6729 1.1605 10.5665 1.04299 10.4344 0.960811C10.3023 0.878636 10.1498 0.835083 9.99422 0.835083C9.83864 0.835083 9.68616 0.878636 9.55406 0.960811C9.42195 1.04299 9.31549 1.1605 9.24672 1.30006L6.86922 6.11672Z" fill="#ffc107" />
							</g>
							<defs>
								<clipPath id="clip0_191_22">
									<rect width="20" height="20" fill="white" />
								</clipPath>
							</defs>
						</svg>
					</span>
					<div class="rating-count text">
						<?php echo esc_html( number_format_i18n( $average_rating, 1 ) ); ?>
						<span>(<?php echo esc_html( $review_count_formatted ); ?>)</span>
					</div>
				</div>
			<?php } ?>

		</div>
		<div class="course-actions">
			<?php if ( $is_enrolled ) { ?>
				<?php if ( 'completed' === $enrollment_status ) { ?>
					<a href="<?php the_permalink(); ?>" class="btn btn-primary">
						<?php esc_html_e( 'Completed', 'skillpulse-lms' ); ?>
					</a>
				<?php } else { ?>
					<a href="<?php the_permalink(); ?>" class="btn btn-continue">
						<?php esc_html_e( 'Continue Learning', 'skillpulse-lms' ); ?>
					</a>
				<?php } ?>
			<?php } else { ?>
				<a href="<?php the_permalink(); ?>" class="btn btn-primary">
					<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
				</a>
			<?php } ?>
		</div>
	</div>

</div>

<?php
/**
 * Hook: splms_after_single_course_card
 */
do_action( 'splms_after_single_course_card', $course_id );

// Reset post data.
wp_reset_postdata();
?>