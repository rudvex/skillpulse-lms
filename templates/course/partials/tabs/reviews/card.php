<?php
/**
 * Single Review Card
 *
 * Displays a single review with rating, content, helpful votes, and instructor reply.
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/course/partials/tabs/reviews/card.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$review    = isset( $args['review'] ) ? $args['review'] : array();
$course_id = isset( $args['course_id'] ) ? $args['course_id'] : get_the_ID();

if ( empty( $review ) ) {
	return;
}

$reviewer = get_userdata( $review['user_id'] ?? 0 );
// phpcs:ignore WordPress.WP.Capabilities.Unknown -- edit_courses is a custom capability defined by the plugin.
$is_instructor    = $reviewer && user_can( $reviewer->ID, 'edit_courses' );
$is_course_author = $reviewer && (int) get_post_field( 'post_author', $course_id ) === (int) $reviewer->ID;

// Get proper reviewer name.
$reviewer_name = __( 'Anonymous Student', 'skillpulse-lms' );
if ( $reviewer && $reviewer->display_name ) {
	$reviewer_name = $reviewer->display_name;
} elseif ( ! empty( $review['author_name'] ) && 'Anonymous Student' !== $review['author_name'] ) {
	$reviewer_name = $review['author_name'];
}

// Check if current user has voted this review as helpful.
$user_has_voted = false;
if ( is_user_logged_in() ) {
	$current_user_id = get_current_user_id();
	$voted_reviews   = get_user_meta( $current_user_id, 'voted_reviews', true );
	$user_has_voted  = is_array( $voted_reviews ) && in_array( $review['id'], $voted_reviews, true );
}
?>

<div class="review-item <?php echo $is_instructor ? 'instructor-review' : 'student-review'; ?> <?php echo $is_course_author ? 'course-author-review' : ''; ?>" data-review-id="<?php echo esc_attr( $review['id'] ?? 0 ); ?>">
	<div class="review-header">
		<div class="student-info">
			<div class="student-avatar">
				<?php echo get_avatar( $review['user_id'] ?? 0, 40 ); ?>
			</div>
			<div class="student-details">
				<h4 class="student-name"><?php echo esc_html( $reviewer_name ); ?></h4>
				<div class="review-meta">
					<div class="review-rating">
						<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
							<span class="star <?php echo $i <= ( $review['rating'] ?? 0 ) ? 'filled' : 'empty'; ?>">★</span>
						<?php } ?>
					</div>
					<span class="review-date"><?php echo esc_html( human_time_diff( strtotime( $review['created_at'] ?? 'now' ) ) ); ?> <?php esc_html_e( 'ago', 'skillpulse-lms' ); ?></span>
				</div>
			</div>
		</div>
		<div class="reviewer-role">
			<?php if ( $is_course_author ) { ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M12 2L3 7L12 12L21 7L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M3 17L12 22L21 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<path d="M3 12L12 17L21 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Course Author', 'skillpulse-lms' ); ?>
			<?php } else { ?>
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<?php esc_html_e( 'Student', 'skillpulse-lms' ); ?>
			<?php } ?>
		</div>
	</div>

	<div class="review-content">
		<div class="review-text">
			<?php if ( ! empty( $review['content'] ) ) { ?>
				<?php echo wp_kses_post( wpautop( $review['content'] ) ); ?>
			<?php } else { ?>
				<em><?php esc_html_e( 'No review text provided.', 'skillpulse-lms' ); ?></em>
			<?php } ?>
		</div>

		<div class="review-actions">
			<button class="helpful-btn <?php echo $user_has_voted ? 'active' : ''; ?>" data-review-id="<?php echo esc_attr( $review['id'] ?? 0 ); ?>" <?php echo ! is_user_logged_in() ? 'disabled title="' . esc_attr__( 'Please login to vote', 'skillpulse-lms' ) . '"' : ''; ?>>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M14 9V5C14 3.89543 13.1046 3 12 3C10.8954 3 10 3.89543 10 5V9L7 12V20H20.28C20.7623 20.0047 21.2304 19.8369 21.6056 19.524C21.9808 19.2111 22.2377 18.7744 22.33 18.29L23.73 11.29C23.8202 10.8048 23.7498 10.3038 23.5321 9.86619C23.3144 9.42862 22.9616 9.08262 22.53 8.88L21 8.17C20.6755 8.05752 20.3245 8.05752 20 8.17L18.47 8.88C18.0384 9.08262 17.6856 9.42862 17.4679 9.86619C17.2502 10.3038 17.1798 10.8048 17.27 11.29L18.67 18.29C18.7623 18.7744 19.0192 19.2111 19.3944 19.524C19.7696 19.8369 20.2377 20.0047 20.72 20H7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
				<span class="helpful-count"><?php echo esc_html( (int) ( $review['helpful_count'] ?? 0 ) ); ?> <?php esc_html_e( 'helpful', 'skillpulse-lms' ); ?></span>
			</button>
		</div>
	</div>

	<?php if ( ! empty( $review['author_reply'] ) ) { ?>
		<?php
		$reply_data      = $review['author_reply'];
		$reply_author_id = $reply_data['reply_author'] ?? 0;
		$reply_author    = get_userdata( $reply_author_id );
		?>
		<div class="instructor-reply">
			<div class="reply-header">
				<div class="instructor-avatar">
					<?php echo get_avatar( $reply_author_id, 32 ); ?>
				</div>
				<div class="reply-author">
					<strong><?php echo esc_html( $reply_author ? $reply_author->display_name : __( 'Instructor', 'skillpulse-lms' ) ); ?></strong>
				</div>
				<div class="reply-date">
					<?php
					$reply_date = $reply_data['reply_date'] ?? '';
					if ( $reply_date ) {
						/* translators: %s: Time ago. */
						$reply_time_text = esc_html( human_time_diff( strtotime( $reply_date ) ) ) . ' ' . esc_html__( 'ago', 'skillpulse-lms' );
						echo esc_html( $reply_time_text );
					}
					?>
				</div>
			</div>
			<div class="reply-content">
				<div class="reply-text">
					<?php
					$reply_text = $reply_data['reply_text'] ?? '';
					echo wp_kses_post( wpautop( $reply_text ) );
					?>
				</div>
			</div>
		</div>
	<?php } ?>
</div>
