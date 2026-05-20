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



$splms_course_id = get_the_ID();
$splms_user_id   = get_current_user_id();

// Check if course uses section-based pricing.
if ( ! splms_course_uses_section_pricing( $splms_course_id ) ) {
	return;
}

// Get pricing data using existing functions.
$splms_summary      = splms_get_course_section_pricing_summary( $splms_course_id, $splms_user_id );
$splms_course_info  = splms_get_course_access_info( $splms_course_id );
$splms_is_enrolled  = splms_is_user_enrolled( $splms_course_id, $splms_user_id );
$splms_upgrade_info = splms_calculate_upgrade_price( $splms_course_id, $splms_user_id );

// Get course items.
$splms_course_items_query = SPLMS_Course_Items_Query::get_instance();
$splms_course_items       = $splms_course_items_query->get_items( $splms_course_id );

// Calculate stats.
$splms_section_items = array();
foreach ( $splms_course_items as $splms_item ) {
	if ( SPLMS_POST_TYPES['section'] === $splms_item->item_type ) {
		$splms_section_pricing = splms_get_section_pricing_with_access( $splms_item->item_id, $splms_user_id );

		// Get section stats (lessons, quizzes).
		$splms_relationships_query = SPLMS_Relationships_Query::get_instance();
		$splms_children            = $splms_relationships_query->get_children( $splms_item->item_id );

		$splms_lesson_count = 0;
		$splms_quiz_count   = 0;
		foreach ( $splms_children as $splms_child ) {
			if ( SPLMS_POST_TYPES['lesson'] === $splms_child->child_type ) {
				++$splms_lesson_count;
			} elseif ( SPLMS_POST_TYPES['quiz'] === $splms_child->child_type ) {
				++$splms_quiz_count;
			}
		}

		$splms_section_items[] = array(
			'id'           => $splms_item->item_id,
			'title'        => get_the_title( $splms_item->item_id ),
			'pricing'      => $splms_section_pricing,
			'lesson_count' => $splms_lesson_count,
			'quiz_count'   => $splms_quiz_count,
			'is_purchased' => in_array( $splms_item->item_id, $splms_upgrade_info['purchased_sections'], true ),
		);
	}
}

// Apply smart pricing logic for section-based pricing courses.
// This ensures the full course price makes business sense relative to individual sections.
$splms_original_course_price = $splms_course_info['price'];
$splms_smart_course_price    = splms_calculate_smart_course_price( $splms_course_id, 'discount' ); // 15% discount strategy.

// If smart price is valid and different from original, use it.
if ( $splms_smart_course_price > 0 && $splms_smart_course_price !== $splms_original_course_price ) {
	// Use smart pricing that's 15% less than sections total.
	$splms_full_course_regular     = $splms_summary['total_price']; // Show sections total as "regular price".
	$splms_full_course_price       = $splms_smart_course_price; // Show discounted bundle price.
	$splms_has_course_discount     = true;
	$splms_course_discount_percent = round( ( ( $splms_full_course_regular - $splms_full_course_price ) / $splms_full_course_regular ) * 100 );
} else {
	// Use original course pricing (backward compatible).
	$splms_full_course_price       = isset( $splms_course_info['final_price'] ) ? $splms_course_info['final_price'] : $splms_course_info['price'];
	$splms_full_course_regular     = $splms_course_info['price'];
	$splms_has_course_discount     = $splms_full_course_price < $splms_full_course_regular;
	$splms_course_discount_percent = $splms_has_course_discount ? round( ( ( $splms_full_course_regular - $splms_full_course_price ) / $splms_full_course_regular ) * 100 ) : 0;
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
				<?php foreach ( $splms_section_items as $splms_index => $splms_section ) : ?>
					<div class="section-item <?php echo esc_attr( $splms_section['is_purchased'] ? 'purchased' : '' ); ?>">
						<div class="section-number"><?php echo esc_html( $splms_index + 1 ); ?></div>

						<div class="section-content">
							<h4 class="section-title"><?php echo esc_html( $splms_section['title'] ); ?></h4>
							<div class="section-meta">
								<?php if ( $splms_section['lesson_count'] > 0 ) : ?>
									<span><?php echo esc_html( $splms_section['lesson_count'] ); ?> <?php esc_html_e( 'Lessons', 'skillpulse-lms' ); ?></span>
								<?php endif; ?>
								<?php if ( $splms_section['quiz_count'] > 0 ) : ?>
									<span><?php echo esc_html( $splms_section['quiz_count'] ); ?> <?php esc_html_e( 'Quizzes', 'skillpulse-lms' ); ?></span>
								<?php endif; ?>
							</div>
						</div>

						<div class="section-price">
							<?php if ( $splms_section['is_purchased'] ) : ?>
								<span class="purchased-badge"><?php esc_html_e( 'Purchased', 'skillpulse-lms' ); ?></span>
							<?php elseif ( $splms_section['pricing']['is_free'] ) : ?>
								<span class="price-free"><?php esc_html_e( 'FREE', 'skillpulse-lms' ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $splms_section['id'] ) ); ?>" class="btn-section">
									<?php esc_html_e( 'Access Free', 'skillpulse-lms' ); ?>
								</a>
							<?php else : ?>
								<span class="price-current"><?php echo esc_html( splms_get_price_format( $splms_section['pricing']['effective_price'] ) ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $splms_section['id'] ) ); ?>" class="btn-section">
									<?php esc_html_e( 'Buy Section', 'skillpulse-lms' ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="sections-summary">
				<div class="summary-row">
					<span><?php echo esc_html( $splms_summary['free_sections'] ); ?> <?php esc_html_e( 'Free sections', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="summary-row">
					<span><?php echo esc_html( $splms_summary['priced_sections'] ); ?> <?php esc_html_e( 'Paid sections', 'skillpulse-lms' ); ?></span>
				</div>
				<div class="summary-row total-row">
					<strong><?php esc_html_e( 'Total if all bought:', 'skillpulse-lms' ); ?></strong>
					<strong><?php echo esc_html( splms_get_price_format( $splms_summary['total_price'] ) ); ?></strong>
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
						<span class="price-value"><?php echo esc_html( splms_get_price_format( $splms_summary['total_price'] ) ); ?></span>
					</div>
					<?php if ( $splms_has_course_discount ) : ?>
						<div class="price-row">
							<span class="price-label"><?php esc_html_e( 'Regular course:', 'skillpulse-lms' ); ?></span>
							<span class="price-value strikethrough"><?php echo esc_html( splms_get_price_format( $splms_full_course_regular ) ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $splms_has_course_discount ) : ?>
					<div class="discount-badge">
						<?php echo esc_html( $splms_course_discount_percent ); ?>% <?php esc_html_e( 'OFF', 'skillpulse-lms' ); ?>
					</div>
				<?php endif; ?>

				<div class="final-price">
					<span class="price-label"><?php esc_html_e( 'NOW:', 'skillpulse-lms' ); ?></span>
					<span class="price-big"><?php echo esc_html( splms_get_price_format( $splms_full_course_price ) ); ?></span>
				</div>

				<?php
				$splms_savings = $splms_has_course_discount ? ( $splms_full_course_regular - $splms_full_course_price ) : ( $splms_summary['total_price'] - $splms_full_course_price );
				if ( $splms_savings > 0 ) :
					?>
					<div class="savings-badge">
						<?php esc_html_e( 'You save:', 'skillpulse-lms' ); ?> <?php echo esc_html( splms_get_price_format( $splms_savings ) ); ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $splms_is_enrolled ) : ?>
				<button class="btn-enroll enrolled" disabled>
					<?php esc_html_e( 'Already Enrolled', 'skillpulse-lms' ); ?>
				</button>
			<?php elseif ( isset( $splms_upgrade_info['has_purchased_sections'] ) && $splms_upgrade_info['has_purchased_sections'] ) : ?>
				<a href="<?php echo esc_url( splms_get_course_purchase_url( $splms_course_id, $splms_user_id, 'full_course' ) ); ?>" class="btn-enroll upgrade">
					<?php esc_html_e( 'Upgrade to Full Course', 'skillpulse-lms' ); ?>
					<span class="upgrade-credit">
						(<?php echo esc_html( splms_get_price_format( $splms_upgrade_info['upgrade_price'] ) ); ?> <?php esc_html_e( 'after credit', 'skillpulse-lms' ); ?>)
					</span>
				</a>
			<?php else : ?>
				<a href="<?php echo esc_url( splms_get_course_purchase_url( $splms_course_id, $splms_user_id, 'full_course' ) ); ?>" class="btn-enroll">
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
