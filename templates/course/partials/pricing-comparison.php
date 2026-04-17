<?php
/**
 * Course Pricing Comparison
 *
 * Displays section-based pricing vs full course pricing.
 *
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$course_id = get_the_ID();
$user_id   = get_current_user_id();

// Check if course uses section-based pricing.
if ( ! splms_course_uses_section_pricing( $course_id ) ) {
	return;
}

// Get pricing data using existing functions.
$summary      = splms_get_course_section_pricing_summary( $course_id, $user_id );
$course_info  = splms_get_course_access_info( $course_id );
$is_enrolled  = splms_is_user_enrolled( $course_id, $user_id );
$upgrade_info = splms_calculate_upgrade_price( $course_id, $user_id );

// Get course items.
$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
$course_items       = $course_items_query->get_items( $course_id );

// Calculate stats.
$section_items = array();
foreach ( $course_items as $item ) {
	if ( SPLMS_POST_TYPES['section'] === $item->item_type ) {
		$section_pricing = splms_get_section_pricing_with_access( $item->item_id, $user_id );

		// Get section stats (lessons, quizzes).
		$relationships_query = SkillPulse_LMS_Relationships_Query::get_instance();
		$children            = $relationships_query->get_children( $item->item_id );

		$lesson_count = 0;
		$quiz_count   = 0;
		foreach ( $children as $child ) {
			if ( SPLMS_POST_TYPES['lesson'] === $child->child_type ) {
				++$lesson_count;
			} elseif ( SPLMS_POST_TYPES['quiz'] === $child->child_type ) {
				++$quiz_count;
			}
		}

		$section_items[] = array(
			'id'           => $item->item_id,
			'title'        => get_the_title( $item->item_id ),
			'pricing'      => $section_pricing,
			'lesson_count' => $lesson_count,
			'quiz_count'   => $quiz_count,
			'is_purchased' => in_array( $item->item_id, $upgrade_info['purchased_sections'], true ),
		);
	}
}

// Apply smart pricing logic for section-based pricing courses.
// This ensures the full course price makes business sense relative to individual sections.
$original_course_price = $course_info['price'];
$smart_course_price    = splms_calculate_smart_course_price( $course_id, 'discount' ); // 15% discount strategy.

// If smart price is valid and different from original, use it.
if ( $smart_course_price > 0 && $smart_course_price !== $original_course_price ) {
	// Use smart pricing that's 15% less than sections total.
	$full_course_regular     = $summary['total_price']; // Show sections total as "regular price".
	$full_course_price       = $smart_course_price; // Show discounted bundle price.
	$has_course_discount     = true;
	$course_discount_percent = round( ( ( $full_course_regular - $full_course_price ) / $full_course_regular ) * 100 );
} else {
	// Use original course pricing (backward compatible).
	$full_course_price       = isset( $course_info['final_price'] ) ? $course_info['final_price'] : $course_info['price'];
	$full_course_regular     = $course_info['price'];
	$has_course_discount     = $full_course_price < $full_course_regular;
	$course_discount_percent = $has_course_discount ? round( ( ( $full_course_regular - $full_course_price ) / $full_course_regular ) * 100 ) : 0;
}
?>

<div class="splms-pricing-comparison">
	<div class="pricing-header">
		<h2><?php esc_html_e( 'Choose Your Learning Path', 'skillpulse-lms' ); ?></h2>
	</div>

	<div class="pricing-columns">
		<!-- Left Column: Individual Sections -->
		<div class="pricing-column pricing-sections">
			<div class="column-header">
				<h3><?php esc_html_e( 'Individual Sections', 'skillpulse-lms' ); ?></h3>
				<p class="column-subtitle"><?php esc_html_e( 'Flexible • Pay as you learn', 'skillpulse-lms' ); ?></p>
			</div>

			<div class="sections-list">
				<?php foreach ( $section_items as $index => $section ) : ?>
					<div class="section-item <?php echo $section['is_purchased'] ? 'purchased' : ''; ?>">
						<div class="section-number"><?php echo esc_html( $index + 1 ); ?></div>

						<div class="section-content">
							<h4 class="section-title"><?php echo esc_html( $section['title'] ); ?></h4>
							<div class="section-meta">
								<?php if ( $section['lesson_count'] > 0 ) : ?>
									<span><?php echo esc_html( $section['lesson_count'] ); ?> <?php esc_html_e( 'Lessons', 'skillpulse-lms' ); ?></span>
								<?php endif; ?>
								<?php if ( $section['quiz_count'] > 0 ) : ?>
									<span><?php echo esc_html( $section['quiz_count'] ); ?> <?php esc_html_e( 'Quizzes', 'skillpulse-lms' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="section-price">
							<?php if ( $section['is_purchased'] ) : ?>
								<span class="purchased-badge"><?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?></span>
							<?php elseif ( $section['pricing']['is_free'] ) : ?>
								<span class="price-free"><?php esc_html_e( 'FREE', 'skillpulse-lms' ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $section['id'] ) ); ?>" class="btn-section">
									<?php esc_html_e( 'Access Free', 'skillpulse-lms' ); ?>
								</a>
							<?php else : ?>
								<span class="price-current"><?php echo esc_html( get_splms_price_format( $section['pricing']['effective_price'] ) ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $section['id'] ) ); ?>" class="btn-section">
									<?php esc_html_e( 'Buy Section', 'skillpulse-lms' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="sections-summary">
				<div class="summary-row">
					<span><?php echo esc_html( $summary['free_sections'] ); ?> <?php esc_html_e( 'Free sections', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="summary-row">
					<span><?php echo esc_html( $summary['priced_sections'] ); ?> <?php esc_html_e( 'Paid sections', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="summary-row total-row">
					<strong><?php esc_html_e( 'Total if all bought:', 'skillpulse-lms' ); ?></strong>
					<strong><?php echo esc_html( get_splms_price_format( $summary['total_price'] ) ); ?></strong>
				</div>
				<div class="summary-note">
					<?php esc_html_e( '⚠️ Individual sections do not include certificate', 'skillpulse-lms' ); ?>
				</div>
			</div>
		</div>

		<!-- Right Column: Complete Course -->
		<div class="pricing-column pricing-full-course">
			<div class="column-header featured">
				<span class="featured-badge"><?php esc_html_e( 'Most Popular', 'skillpulse-lms' ); ?></span>
				<h3><?php esc_html_e( 'Complete Course', 'skillpulse-lms' ); ?></h3>
				<p class="column-subtitle"><?php esc_html_e( 'Best Value • Everything included', 'skillpulse-lms' ); ?></p>
			</div>

			<div class="features-list">
				<div class="feature-item">
					<svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
						<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
					</svg>
					<span><?php esc_html_e( 'All sections included', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="feature-item">
					<svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
						<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
					</svg>
					<span><?php esc_html_e( 'Verified certificate', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="feature-item">
					<svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
						<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
					</svg>
					<span><?php esc_html_e( 'Priority support', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="feature-item">
					<svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
						<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
					</svg>
					<span><?php esc_html_e( 'Lifetime access', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="feature-item">
					<svg class="check-icon" width="20" height="20" viewBox="0 0 24 24" fill="none">
						<path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
					</svg>
					<span><?php esc_html_e( 'Future updates', 'skillpulse-lms' ); ?></span>
				</div>
			</div>

			<div class="pricing-box">
				<div class="pricing-comparison-grid">
					<div class="price-row">
						<span class="price-label"><?php esc_html_e( 'Sections individually:', 'skillpulse-lms' ); ?></span>
						<span class="price-value"><?php echo esc_html( get_splms_price_format( $summary['total_price'] ) ); ?></span>
					</div>
					<?php if ( $has_course_discount ) : ?>
						<div class="price-row">
							<span class="price-label"><?php esc_html_e( 'Regular course:', 'skillpulse-lms' ); ?></span>
							<span class="price-value strikethrough"><?php echo esc_html( get_splms_price_format( $full_course_regular ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $has_course_discount ) : ?>
					<div class="discount-badge">
						<?php echo esc_html( $course_discount_percent ); ?>% <?php esc_html_e( 'OFF', 'skillpulse-lms' ); ?>
					</div>
				<?php endif; ?>

				<div class="final-price">
					<span class="price-label"><?php esc_html_e( 'NOW:', 'skillpulse-lms' ); ?></span>
					<span class="price-big"><?php echo esc_html( get_splms_price_format( $full_course_price ) ); ?></span>
				</div>

				<?php
				$savings = $has_course_discount ? ( $full_course_regular - $full_course_price ) : ( $summary['total_price'] - $full_course_price );
				if ( $savings > 0 ) :
					?>
					<div class="savings-badge">
						<?php esc_html_e( 'You save:', 'skillpulse-lms' ); ?> <?php echo esc_html( get_splms_price_format( $savings ) ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $is_enrolled ) : ?>
				<button class="btn-enroll enrolled" disabled>
					<?php esc_html_e( 'Already Enrolled', 'skillpulse-lms' ); ?>
				</button>
			<?php elseif ( isset( $upgrade_info['has_purchased_sections'] ) && $upgrade_info['has_purchased_sections'] ) : ?>
				<a href="<?php echo esc_url( splms_get_course_purchase_url( $course_id, $user_id, 'full_course' ) ); ?>" class="btn-enroll upgrade">
					<?php esc_html_e( 'Upgrade to Full Course', 'skillpulse-lms' ); ?>
					<span class="upgrade-credit">
						(<?php echo esc_html( get_splms_price_format( $upgrade_info['upgrade_price'] ) ); ?> <?php esc_html_e( 'after credit', 'skillpulse-lms' ); ?>)
					</span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( splms_get_course_purchase_url( $course_id, $user_id, 'full_course' ) ); ?>" class="btn-enroll">
					<?php esc_html_e( 'Enroll in Complete Course', 'skillpulse-lms' ); ?>
				</a>
			<?php endif; ?>

			<div class="social-proof">
				<div class="rating">⭐⭐⭐⭐⭐ 4.9/5</div>
				<div class="students-count">1,234 <?php esc_html_e( 'students enrolled', 'skillpulse-lms' ); ?></div>
				<div class="popular-choice">💡 92% <?php esc_html_e( 'choose complete course', 'skillpulse-lms' ); ?></div>
			</div>
		</div>
	</div>
</div>
