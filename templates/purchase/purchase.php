<?php
/**
 * Checkout Page Template
 *
 * Template for course purchase checkout process with PayPal integration.
 * This template can be overridden by copying it to your theme.
 *
 * @since      1.0.0
 * @subpackage Templates
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



if ( ! splms_is_paid_courses_enabled() ) {
	wp_die(
		esc_html__( 'Paid courses are currently disabled.', 'skillpulse-lms' ),
		esc_html__( 'Access Denied', 'skillpulse-lms' ),
		array( 'response' => 403 )
	);
}

$splms_payment_system = SkillPulse_LMS_Payment::get_instance();

if ( $splms_payment_system->handle_payment_success() ) {
	return; // Exit if payment was processed.
}

// Validate purchase token.
// Note: Don't use sanitize_text_field() as it can corrupt Base64 encoding.
// The token will be validated by the validate_token() function.
// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Token validated by splms_validate_purchase_token().
$splms_token = isset( $_GET['token'] ) ? wp_unslash( $_GET['token'] ) : '';

$splms_token_data = splms_validate_purchase_token( $splms_token );

if ( is_wp_error( $splms_token_data ) ) {
	wp_die(
		esc_html( $splms_token_data->get_error_message() ),
		esc_html__( 'Invalid Purchase Link', 'skillpulse-lms' ),
		array( 'response' => 403 )
	);
}

// Extract data from validated token.
$splms_course_id             = $splms_token_data['course_id'];
$splms_purchase_type         = $splms_token_data['purchase_type'];
$splms_requested_section_ids = isset( $splms_token_data['section_ids'] ) ? $splms_token_data['section_ids'] : array();
$splms_requested_section_id  = ! empty( $splms_requested_section_ids ) ? $splms_requested_section_ids[0] : 0;

$splms_user_id  = get_current_user_id();
$splms_currency = splms_get_setting( 'currency', 'USD' );

// Check if user has membership access (overrides payment requirement).
if ( splms_user_has_membership_access_for_paid_course( $splms_course_id, $splms_user_id ) ) {
	// User has membership → Redirect to course page (they can enroll directly, no payment needed).
	wp_safe_redirect( get_permalink( $splms_course_id ) );
	exit;
}

$splms_course_info = splms_get_course_access_info( $splms_course_id );

// Check if course is paid.
$splms_original_price = isset( $splms_course_info['price'] ) ? $splms_course_info['price'] : 0;
$splms_price          = isset( $splms_course_info['final_price'] ) ? $splms_course_info['final_price'] : $splms_original_price;
$splms_has_discount   = $splms_original_price > $splms_price && $splms_price > 0;

// Check if user should see upgrade option.
$splms_show_upgrade_option = splms_user_should_see_upgrade_option( $splms_course_id, $splms_user_id );
$splms_upgrade_data        = array();
if ( $splms_show_upgrade_option ) {
	$splms_upgrade_data = splms_calculate_upgrade_price( $splms_course_id, $splms_user_id );
}

// Determine purchase scenarios.
$splms_has_purchased_sections = ! empty( splms_get_user_purchased_sections( $splms_course_id, $splms_user_id ) );
$splms_is_upgrade_purchase    = ( 'full_course' === $splms_purchase_type && $splms_has_purchased_sections );
$splms_is_section_purchase    = ( 'sections' === $splms_purchase_type || ! empty( $splms_requested_section_ids ) );

// Only redirect if user has FULL course access AND this is NOT an upgrade or section purchase.
// Allow:
// 1. Upgrade purchase: User purchased Section A, now buying full course.
// 2. Section purchase: User purchased Section A, now buying Section B.
// Block:
// 1. User already has full course access trying to buy again.
if ( ! $splms_is_upgrade_purchase && ! $splms_is_section_purchase && splms_has_user_purchased_course( $splms_course_id, $splms_user_id ) ) {
	wp_safe_redirect( get_permalink( $splms_course_id ) );
	exit;
}

wp_enqueue_style( 'splms-frontend-style' );
wp_enqueue_script( 'splms-frontend-script' );

// Get available payment methods.
$splms_available_methods = $splms_payment_system->get_available_payment_methods( $splms_course_id );

// Enqueue PayPal SDK if PayPal is available.
if ( isset( $splms_available_methods['paypal'] ) && $splms_available_methods['paypal']['configured'] ) {
	$splms_sandbox_mode   = splms_get_setting( 'paypal_sandbox_mode', true );
	$splms_paypal_sdk_url = 'https://www.paypal.com/sdk/js?client-id=' . splms_get_setting( 'paypal_client_id' ) . '&currency=' . splms_get_setting( 'currency', 'USD' );

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External SDK, version managed by provider.
	wp_enqueue_script( 'paypal-sdk', $splms_paypal_sdk_url, array(), null, true );
}

// Enqueue Stripe SDK if Stripe is available.
if ( isset( $splms_available_methods['stripe'] ) && $splms_available_methods['stripe']['configured'] ) {
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- External SDK, version managed by provider.
	wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, true );
}

// Check if any payment methods are available.
if ( empty( $splms_available_methods ) ) {
	// Check if payment is configured at all.
	if ( ! splms_is_payment_configured() ) {
		// Payment not configured, redirect back to course page with message.
		$splms_course_url = add_query_arg(
			array(
				'splms_payment_error' => 'not_configured',
				'message'             => rawurlencode( __( 'Payment system is not configured. Please contact the administrator.', 'skillpulse-lms' ) ),
			),
			get_permalink( $splms_course_id )
		);
		wp_safe_redirect( $splms_course_url );
		exit;
	}

	wp_die(
		esc_html__( 'No payment methods are currently available. Please contact support.', 'skillpulse-lms' ),
		esc_html__( 'Checkout Error', 'skillpulse-lms' ),
		array( 'response' => 400 )
	);
}


// Build payment data for template rendering.
$splms_payment_methods = $splms_available_methods;
$splms_payment_data    = array();

foreach ( $splms_available_methods as $splms_method_id => $splms_method ) {
	$splms_payment_data[ $splms_method_id ] = array(
		'id'          => $splms_method_id,
		'name'        => $splms_method['name'],
		'icon'        => $splms_method['icon'],
		'description' => $splms_method['description'],
		/* translators: %s: Payment method name. */
		'button_text' => sprintf( __( 'Pay with %s', 'skillpulse-lms' ), $splms_method['name'] ),
		'configured'  => $splms_method['configured'],
	);

	// Add gateway-specific data.
	if ( 'paypal' === $splms_method_id ) {
		$splms_payment_data[ $splms_method_id ]['client_id']    = splms_get_setting( 'paypal_client_id' );
		$splms_payment_data[ $splms_method_id ]['sandbox_mode'] = splms_get_setting( 'paypal_sandbox_mode', true );
	} elseif ( 'stripe' === $splms_method_id ) {
		$splms_payment_data[ $splms_method_id ]['publishable_key'] = splms_get_setting( 'stripe_publishable_key' );
		$splms_payment_data[ $splms_method_id ]['test_mode']       = splms_get_setting( 'stripe_test_mode', true );
	}
}

$splms_course = get_post( $splms_course_id );

// Get sections with pricing if course uses section-based pricing.
$splms_sections_with_pricing = array();
$splms_show_section_pricing  = false;

if ( splms_course_uses_section_pricing( $splms_course_id ) ) {
	// Fetch sections with pricing using course items relationship.
	$splms_course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
	$splms_course_items       = $splms_course_items_query->get_items( $splms_course_id );

	foreach ( $splms_course_items as $splms_course_item ) {
		// Skip non-section items.
		if ( SPLMS_POST_TYPES['section'] !== $splms_course_item->item_type ) {
			continue;
		}

		$splms_section_id      = $splms_course_item->item_id;
		$splms_section         = get_post( $splms_section_id );
		$splms_section_pricing = get_post_meta( $splms_section_id, '_splms_section_pricing', true );

		// Skip free sections and sections without pricing.
		if ( ! is_array( $splms_section_pricing ) || ( isset( $splms_section_pricing['is_free'] ) && true === $splms_section_pricing['is_free'] ) ) {
			continue;
		}

		$splms_section_price = isset( $splms_section_pricing['price'] ) ? floatval( $splms_section_pricing['price'] ) : 0;
		if ( $splms_section_price <= 0 ) {
			continue;
		}

		// Check if user already has access to this section.
		$splms_has_access = false;
		if ( $splms_user_id ) {
			$splms_access_status = SkillPulse_LMS_Order_Access_Control::get_section_access_status( $splms_user_id, $splms_section_id );
			$splms_has_access    = isset( $splms_access_status['has_access'] ) && $splms_access_status['has_access'];
		}

		$splms_sections_with_pricing[] = array(
			'id'           => $splms_section_id,
			'title'        => $splms_section->post_title,
			'price'        => $splms_section_price,
			'sale_price'   => isset( $splms_section_pricing['sale_price'] ) ? floatval( $splms_section_pricing['sale_price'] ) : 0,
			'has_access'   => $splms_has_access,
			'items_count'  => 0, // Will be populated if needed.
			'pricing_data' => $splms_section_pricing,
		);
	}

	$splms_show_section_pricing = ! empty( $splms_sections_with_pricing );
}
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php
		/* translators: %s: Course title. */
		echo esc_html( sprintf( __( 'Checkout - %s', 'skillpulse-lms' ), $splms_course->post_title ) );
		?>
	</title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'splms-checkout-page' ); ?>>

<div class="splms-checkout-container">
	<div class="splms-checkout-wrapper">

		<!-- Header -->
		<div class="splms-checkout-header">
			<h1 class="splms-checkout-title">
				<?php echo esc_html__( 'Complete Your Purchase', 'skillpulse-lms' ); ?>
			</h1>
			<p class="splms-checkout-subtitle">
				<?php
				/* translators: %s: Course title. */
				echo esc_html( sprintf( __( 'You are purchasing: %s', 'skillpulse-lms' ), $splms_course->post_title ) );
				?>
			</p>
		</div>

		<!-- Course Details -->
		<div class="splms-course-details">
			<div class="splms-course-info">
				<h2 class="splms-course-title">
					<a href="<?php echo esc_url( get_permalink( $splms_course_id ) ); ?>">
						<?php echo esc_html( $splms_course->post_title ); ?>
					</a>
				</h2>
				<?php if ( $splms_course->post_excerpt ) { ?>
					<p class="splms-course-excerpt">
						<?php echo esc_html( $splms_course->post_excerpt ); ?>
					</p>
				<?php } ?>
			</div>

			<?php if ( ! $splms_show_section_pricing ) { ?>
				<div class="splms-course-price">
					<?php if ( $splms_has_discount ) { ?>
						<div class="splms-price-wrapper">
							<span class="splms-original-price"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_original_price, 2 ) ); ?></span>
							<span class="splms-sale-price"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?></span>
							<?php
							$splms_discount_percentage = round( ( ( $splms_original_price - $splms_price ) / $splms_original_price ) * 100 );
							?>
							<span class="splms-discount-badge"><?php echo esc_html( $splms_discount_percentage . '% OFF' ); ?></span>
						</div>
					<?php } else { ?>
						<span class="splms-price-amount">
							<?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?>
						</span>
					<?php } ?>
				</div>
			<?php } ?>
		</div>

		<!-- Upgrade Pricing Section (if applicable) -->
		<?php if ( ! $splms_show_section_pricing && $splms_show_upgrade_option && ! empty( $splms_upgrade_data ) ) { ?>
			<div class="splms-upgrade-pricing-section">
				<div class="splms-upgrade-badge">
					<?php echo esc_html__( 'Upgrade to Full Course', 'skillpulse-lms' ); ?>
				</div>
				<div class="splms-upgrade-summary">
					<div class="splms-upgrade-line">
						<span class="splms-upgrade-label"><?php echo esc_html__( 'Full Course Price:', 'skillpulse-lms' ); ?></span>
						<span class="splms-upgrade-value"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['full_course_price'], 2 ) ); ?></span>
					</div>
					<div class="splms-upgrade-line splms-credit-line">
						<span class="splms-upgrade-label"><?php echo esc_html__( 'Already Paid:', 'skillpulse-lms' ); ?></span>
						<span class="splms-upgrade-value splms-credit-value">- <?php echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['already_paid'], 2 ) ); ?></span>
					</div>
					<div class="splms-upgrade-details">
						<?php foreach ( $splms_upgrade_data['purchased_sections'] as $splms_purchased_section ) { ?>
							<div class="splms-purchased-section-item">
								<span class="splms-section-icon">✓</span>
								<span class="splms-section-name"><?php echo esc_html( $splms_purchased_section['section_title'] ); ?></span>
								<span class="splms-section-paid"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_purchased_section['paid_price'], 2 ) ); ?></span>
							</div>
						<?php } ?>
					</div>
					<div class="splms-upgrade-line splms-upgrade-total-line">
						<span class="splms-upgrade-label"><?php echo esc_html__( 'Upgrade Price:', 'skillpulse-lms' ); ?></span>
						<span class="splms-upgrade-value splms-upgrade-final-price">
							<?php
							if ( $splms_upgrade_data['paid_more_than_course'] || $splms_upgrade_data['upgrade_price'] <= 0 ) {
								echo esc_html__( 'FREE', 'skillpulse-lms' );
							} else {
								echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['upgrade_price'], 2 ) );
							}
							?>
						</span>
					</div>
					<?php if ( $splms_upgrade_data['savings'] > 0 ) { ?>
						<div class="splms-savings-message">
							<?php
							/* translators: %s: Saved amount. */
							echo esc_html( sprintf( __( 'You have %s in credits from your previous purchases!', 'skillpulse-lms' ), $splms_currency . ' ' . number_format( $splms_upgrade_data['savings'], 2 ) ) );
							?>
						</div>
					<?php } ?>
				</div>
			</div>
		<?php } ?>

		<?php if ( $splms_show_section_pricing ) { ?>
			<!-- Pricing Options -->
			<div class="splms-pricing-options">
				<div class="splms-step-header">
					<span class="splms-step-number">1</span>
					<h3><?php echo esc_html__( 'Choose Your Purchase Option', 'skillpulse-lms' ); ?></h3>
				</div>

				<!-- Full Course Option -->
				<?php
				// Show both options when course has section pricing AND full course is also paid.
				$splms_show_both_options = splms_course_uses_section_pricing( $splms_course_id ) && $splms_price > 0;
				?>
				<?php if ( $splms_show_both_options ) { ?>
					<div class="splms-pricing-option splms-full-course-option">
						<input type="radio" id="purchase-full-course" name="purchase_type" value="full_course" checked>
						<label for="purchase-full-course" class="splms-pricing-label">
							<div class="splms-pricing-info">
								<div class="splms-pricing-title">
									<?php echo esc_html__( 'Full Course', 'skillpulse-lms' ); ?>
									<span class="splms-badge"><?php echo esc_html__( 'Best Value', 'skillpulse-lms' ); ?></span>
								</div>
								<div class="splms-pricing-description">
									<?php echo esc_html__( 'Get access to all sections and lessons', 'skillpulse-lms' ); ?>
								</div>
							</div>
							<div class="splms-pricing-amount">
								<?php if ( $splms_has_discount ) { ?>
									<div class="splms-price-details">
										<span class="splms-original-price-small"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_original_price, 2 ) ); ?></span>
										<span class="splms-sale-price-large"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?></span>
									</div>
								<?php } else { ?>
									<?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?>
								<?php } ?>
							</div>
						</label>
					</div>
				<?php } ?>

				<!-- Section-Based Option -->
				<div class="splms-pricing-option splms-sections-option">
					<?php if ( $splms_show_both_options ) { ?>
						<input type="radio" id="purchase-sections" name="purchase_type" value="sections">
						<label for="purchase-sections" class="splms-pricing-label">
							<div class="splms-pricing-info">
								<div class="splms-pricing-title">
									<?php echo esc_html__( 'Individual Sections', 'skillpulse-lms' ); ?>
								</div>
								<div class="splms-pricing-description">
									<?php echo esc_html__( 'Purchase only the sections you need', 'skillpulse-lms' ); ?>
								</div>
							</div>
							<div class="splms-pricing-amount splms-pricing-amount-flexible">
								<?php echo esc_html__( 'Custom', 'skillpulse-lms' ); ?>
							</div>
						</label>
					<?php } else { ?>
						<div class="splms-sections-header">
							<h4><?php echo esc_html__( 'Select Sections to Purchase', 'skillpulse-lms' ); ?></h4>
							<p class="splms-sections-description"><?php echo esc_html__( 'Choose the sections you want to purchase', 'skillpulse-lms' ); ?></p>
						</div>
					<?php } ?>

					<div class="splms-sections-list" <?php echo esc_attr( $splms_show_both_options ? 'style="display:none;"' : '' ); ?>>
						<?php foreach ( $splms_sections_with_pricing as $splms_section ) { ?>
							<?php
							$splms_final_section_price = $splms_section['sale_price'] > 0 ? $splms_section['sale_price'] : $splms_section['price'];
							$splms_disabled            = $splms_section['has_access'] ? 'disabled' : '';
							?>
							<?php
							// Pre-select section if it was requested in the token.
							$splms_is_requested = ! empty( $splms_requested_section_ids ) && in_array( (int) $splms_section['id'], $splms_requested_section_ids, true );
							?>
							<div class="splms-section-item <?php echo esc_attr( $splms_disabled ); ?> <?php echo $splms_is_requested ? 'splms-requested-section' : ''; ?>">
								<input type="checkbox"
									id="section-<?php echo esc_attr( $splms_section['id'] ); ?>"
									name="selected_sections[]"
									value="<?php echo esc_attr( $splms_section['id'] ); ?>"
									data-price="<?php echo esc_attr( $splms_final_section_price ); ?>"
									data-section-id="<?php echo esc_attr( $splms_section['id'] ); ?>"
									class="splms-section-checkbox"
									<?php echo esc_attr( $splms_is_requested && ! $splms_disabled ? 'checked' : '' ); ?>
									<?php echo esc_attr( $splms_disabled ); ?>>
								<label for="section-<?php echo esc_attr( $splms_section['id'] ); ?>">
									<div class="splms-section-info">
										<span class="splms-section-title"><?php echo esc_html( $splms_section['title'] ); ?></span>
										<?php if ( $splms_section['has_access'] ) { ?>
											<span class="splms-section-owned"><?php echo esc_html__( 'Already Purchased', 'skillpulse-lms' ); ?></span>
										<?php } ?>
									</div>
									<div class="splms-section-price">
										<?php if ( $splms_section['sale_price'] > 0 ) { ?>
											<span class="splms-original-price"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_section['price'], 2 ) ); ?></span>
											<span class="splms-sale-price"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_section['sale_price'], 2 ) ); ?></span>
										<?php } else { ?>
											<span class="splms-section-price-amount"><?php echo esc_html( $splms_currency . ' ' . number_format( $splms_section['price'], 2 ) ); ?></span>
										<?php } ?>
									</div>
								</label>
							</div>
						<?php } ?>
					</div>
				</div>
			</div>
		<?php } ?>

		<!-- Payment Processing States -->
		<div id="splms-payment-processing" class="splms-payment-state" style="display: none;">
			<div class="splms-spinner"></div>
			<p class="splms-processing-message">
				<?php echo esc_html( __( 'Processing Payment...', 'skillpulse-lms' ) ); ?>
			</p>
		</div>

		<div id="splms-payment-success" class="splms-payment-state splms-success" style="display: none;">
			<div class="splms-success-icon">✓</div>
			<h3><?php echo esc_html( __( 'Payment Successful!', 'skillpulse-lms' ) ); ?></h3>
			<p><?php echo esc_html( __( 'You will be redirected to your course shortly.', 'skillpulse-lms' ) ); ?></p>
		</div>

		<div id="splms-payment-error" class="splms-payment-state splms-error" style="display: none;">
			<div class="splms-error-icon">✗</div>
			<h3><?php echo esc_html( __( 'Payment Failed', 'skillpulse-lms' ) ); ?></h3>
			<p class="splms-error-message"></p>
			<button type="button" class="splms-try-again-btn">
				<?php echo esc_html( __( 'Try Again', 'skillpulse-lms' ) ); ?>
			</button>
		</div>

		<!-- Payment Form -->
		<div id="splms-payment-form" class="splms-payment-form">
			<form id="splms-checkout-form" method="post">

				<!-- Billing Information -->
				<div class="splms-billing-info">
					<div class="splms-step-header">
						<span class="splms-step-number"><?php echo esc_attr( $splms_show_section_pricing ? '2' : '1' ); ?></span>
						<h3><?php echo esc_html__( 'Billing Information', 'skillpulse-lms' ); ?></h3>
					</div>

					<?php
					// Get user billing info if exists.
					$splms_billing_info = array(
						'first_name' => get_user_meta( $splms_user_id, 'first_name', true ),
						'last_name'  => get_user_meta( $splms_user_id, 'last_name', true ),
						'email'      => wp_get_current_user()->user_email,
						'phone'      => get_user_meta( $splms_user_id, 'billing_phone', true ),
						'address_1'  => get_user_meta( $splms_user_id, 'billing_address_1', true ),
						'address_2'  => get_user_meta( $splms_user_id, 'billing_address_2', true ),
						'city'       => get_user_meta( $splms_user_id, 'billing_city', true ),
						'state'      => get_user_meta( $splms_user_id, 'billing_state', true ),
						'postcode'   => get_user_meta( $splms_user_id, 'billing_postcode', true ),
						'country'    => get_user_meta( $splms_user_id, 'billing_country', true ),
					);

					// Default country if not set.
					if ( empty( $splms_billing_info['country'] ) ) {
						$splms_billing_info['country'] = 'US';
					}
					?>

					<div class="splms-billing-fields">
						<div class="splms-field-row">
							<div class="splms-field">
								<label for="billing_first_name">
									<?php echo esc_html__( 'First Name', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="text"
									id="billing_first_name"
									name="billing_first_name"
									value="<?php echo esc_attr( $splms_billing_info['first_name'] ); ?>"
									required>
							</div>

							<div class="splms-field">
								<label for="billing_last_name">
									<?php echo esc_html__( 'Last Name', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="text"
									id="billing_last_name"
									name="billing_last_name"
									value="<?php echo esc_attr( $splms_billing_info['last_name'] ); ?>"
									required>
							</div>
						</div>

						<div class="splms-field-row">
							<div class="splms-field">
								<label for="billing_email">
									<?php echo esc_html__( 'Email Address', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="email"
									id="billing_email"
									name="billing_email"
									value="<?php echo esc_attr( $splms_billing_info['email'] ); ?>"
									required>
							</div>

							<div class="splms-field">
								<label for="billing_phone">
									<?php echo esc_html__( 'Phone Number', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="tel"
									id="billing_phone"
									name="billing_phone"
									placeholder="<?php echo esc_attr__( '+1234567890', 'skillpulse-lms' ); ?>"
									value="<?php echo esc_attr( $splms_billing_info['phone'] ); ?>"
									required>
							</div>
						</div>

						<div class="splms-field">
							<label for="billing_address_1">
								<?php echo esc_html__( 'Street Address', 'skillpulse-lms' ); ?>
								<span class="required">*</span>
							</label>
							<input type="text"
								id="billing_address_1"
								name="billing_address_1"
								placeholder="<?php echo esc_attr__( 'House number and street name', 'skillpulse-lms' ); ?>"
								value="<?php echo esc_attr( $splms_billing_info['address_1'] ); ?>"
								required>
						</div>

						<div class="splms-field">
							<label for="billing_address_2">
								<?php echo esc_html__( 'Apartment, suite, etc.', 'skillpulse-lms' ); ?>
								<span class="optional"><?php echo esc_html__( '(optional)', 'skillpulse-lms' ); ?></span>
							</label>
							<input type="text"
								id="billing_address_2"
								name="billing_address_2"
								value="<?php echo esc_attr( $splms_billing_info['address_2'] ); ?>">
						</div>

						<div class="splms-field-row">
							<div class="splms-field">
								<label for="billing_city">
									<?php echo esc_html__( 'City', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="text"
									id="billing_city"
									name="billing_city"
									value="<?php echo esc_attr( $splms_billing_info['city'] ); ?>"
									required>
							</div>

							<div class="splms-field">
								<label for="billing_state">
									<?php echo esc_html__( 'State / Province', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="text"
									id="billing_state"
									name="billing_state"
									value="<?php echo esc_attr( $splms_billing_info['state'] ); ?>"
									required>
							</div>
						</div>

						<div class="splms-field-row">
							<div class="splms-field">
								<label for="billing_postcode">
									<?php echo esc_html__( 'ZIP / Postal Code', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<input type="text"
									id="billing_postcode"
									name="billing_postcode"
									value="<?php echo esc_attr( $splms_billing_info['postcode'] ); ?>"
									required>
							</div>

							<div class="splms-field">
								<label for="billing_country">
									<?php echo esc_html__( 'Country', 'skillpulse-lms' ); ?>
									<span class="required">*</span>
								</label>
								<select id="billing_country" name="billing_country" required>
									<option value=""><?php echo esc_html__( 'Select a country...', 'skillpulse-lms' ); ?></option>
									<?php
									$splms_countries = array(
										'US' => __( 'United States', 'skillpulse-lms' ),
										'CA' => __( 'Canada', 'skillpulse-lms' ),
										'GB' => __( 'United Kingdom', 'skillpulse-lms' ),
										'AU' => __( 'Australia', 'skillpulse-lms' ),
										'IN' => __( 'India', 'skillpulse-lms' ),
										'DE' => __( 'Germany', 'skillpulse-lms' ),
										'FR' => __( 'France', 'skillpulse-lms' ),
										'IT' => __( 'Italy', 'skillpulse-lms' ),
										'ES' => __( 'Spain', 'skillpulse-lms' ),
										'NL' => __( 'Netherlands', 'skillpulse-lms' ),
										'BR' => __( 'Brazil', 'skillpulse-lms' ),
										'MX' => __( 'Mexico', 'skillpulse-lms' ),
										'JP' => __( 'Japan', 'skillpulse-lms' ),
										'CN' => __( 'China', 'skillpulse-lms' ),
										'SG' => __( 'Singapore', 'skillpulse-lms' ),
										'AE' => __( 'United Arab Emirates', 'skillpulse-lms' ),
									);

									foreach ( $splms_countries as $splms_code => $splms_name ) {
										printf(
											'<option value="%s"%s>%s</option>',
											esc_attr( $splms_code ),
											selected( $splms_billing_info['country'], $splms_code, false ),
											esc_html( $splms_name )
										);
									}
									?>
								</select>
							</div>
						</div>
					</div>
				</div>

				<!-- Payment Method Selection -->
				<div class="splms-payment-methods">
					<h3 class="splms-section-title"><?php echo esc_html( __( 'Select Payment Method', 'skillpulse-lms' ) ); ?></h3>

					<?php if ( ! empty( $splms_payment_methods ) ) { ?>
						<?php $splms_first_method = true; ?>
						<?php foreach ( $splms_payment_methods as $splms_method_id => $splms_method ) { ?>
							<div class="splms-payment-option">
								<?php
								// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Form display only, nonce verified on form submission.
								?>
								<input type="radio" id="<?php echo esc_attr( $splms_method_id ); ?>-payment" name="payment_method"
										value="<?php echo esc_attr( $splms_method_id ); ?>" <?php echo esc_attr( $splms_first_method ? 'checked' : '' ); ?>>
								<label for="<?php echo esc_attr( $splms_method_id ); ?>-payment" class="splms-payment-label">
									<div class="splms-payment-icon">
										<?php if ( 'paypal' === $splms_method_id ) { ?>
											<img src="<?php echo esc_url( SKILLPULSE_LMS_ASSETS_URL . 'images/paypal-logo.svg' ); ?>" alt="PayPal" width="80" height="20">
										<?php } elseif ( 'stripe' === $splms_method_id ) { ?>
											<img src="<?php echo esc_url( SKILLPULSE_LMS_ASSETS_URL . 'images/stripe-logo.svg' ); ?>" alt="Stripe" width="60" height="20">
										<?php } else { ?>
											<span class="splms-payment-icon-text"><?php echo esc_html( strtoupper( $splms_method_id ) ); ?></span>
										<?php } ?>
									</div>
									<div class="splms-payment-info">
										<span class="splms-payment-name"><?php echo esc_html( $splms_method['name'] ); ?></span>
										<span class="splms-payment-description">
											<?php echo esc_html( $splms_method['description'] ); ?>
										</span>
									</div>
								</label>
							</div>
							<?php $splms_first_method = false; ?>
						<?php } ?>
					<?php } else { ?>
						<div class="splms-no-payment-methods">
							<p><?php echo esc_html( __( 'No payment methods are currently available.', 'skillpulse-lms' ) ); ?></p>
						</div>
					<?php } ?>
				</div>

				<!-- Order Summary -->
				<div class="splms-order-summary">
					<div class="splms-step-header">
						<span class="splms-step-number"><?php echo esc_attr( $splms_show_section_pricing ? '3' : '2' ); ?></span>
						<h3><?php echo esc_html( __( 'Order Summary', 'skillpulse-lms' ) ); ?></h3>
					</div>
					<div id="splms-summary-items">
						<?php if ( ! $splms_show_section_pricing ) { ?>
							<?php if ( $splms_show_upgrade_option && ! empty( $splms_upgrade_data ) ) { ?>
								<!-- Upgrade Order Summary -->
								<div class="splms-summary-line">
									<span class="splms-summary-label">
										<?php
										/* translators: %s: Course title. */
										echo esc_html( sprintf( __( '%s Course (Full Access)', 'skillpulse-lms' ), $splms_course->post_title ) );
										?>
									</span>
									<span class="splms-summary-value">
										<?php echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['full_course_price'], 2 ) ); ?>
									</span>
								</div>
								<div class="splms-summary-line splms-credit-summary">
									<span class="splms-summary-label">
										<?php
										/* translators: %d: Number of sections. */
										echo esc_html( sprintf( _n( 'Credit from %d section', 'Credit from %d sections', $splms_upgrade_data['section_count'], 'skillpulse-lms' ), $splms_upgrade_data['section_count'] ) );
										?>
									</span>
									<span class="splms-summary-value splms-credit-amount">
										- <?php echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['already_paid'], 2 ) ); ?>
									</span>
								</div>
							<?php } else { ?>
								<!-- Regular Order Summary -->
								<div class="splms-summary-line">
									<span class="splms-summary-label">
										<?php
										/* translators: %s: Course title. */
										echo esc_html( sprintf( __( '%s Course', 'skillpulse-lms' ), $splms_course->post_title ) );
										?>
									</span>
									<span class="splms-summary-value">
										<?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?>
									</span>
								</div>
							<?php } ?>
						<?php } else { ?>
							<div class="splms-summary-line splms-full-course-summary" style="<?php echo esc_attr( $splms_show_both_options ? '' : 'display:none;' ); ?>">
								<span class="splms-summary-label">
									<?php
									/* translators: %s: Course title. */
									echo esc_html( sprintf( __( '%s Course (Full Access)', 'skillpulse-lms' ), $splms_course->post_title ) );
									?>
								</span>
								<span class="splms-summary-value">
									<?php echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) ); ?>
								</span>
							</div>
							<div class="splms-sections-summary" style="<?php echo esc_attr( $splms_show_both_options ? 'display:none;' : '' ); ?>">
								<!-- Section items will be added dynamically by JavaScript -->
							</div>
						<?php } ?>
					</div>
					<div class="splms-summary-total">
						<span class="splms-total-label">
							<?php echo esc_html( __( 'Total', 'skillpulse-lms' ) ); ?>
						</span>
						<span class="splms-total-value" data-full-course-price="<?php echo esc_attr( $splms_price ); ?>">
							<?php
							if ( $splms_show_upgrade_option && ! empty( $splms_upgrade_data ) ) {
								if ( $splms_upgrade_data['paid_more_than_course'] || $splms_upgrade_data['upgrade_price'] <= 0 ) {
									echo esc_html__( 'FREE', 'skillpulse-lms' );
								} else {
									echo esc_html( $splms_currency . ' ' . number_format( $splms_upgrade_data['upgrade_price'], 2 ) );
								}
							} else {
								echo esc_html( $splms_currency . ' ' . number_format( $splms_price, 2 ) );
							}
							?>
						</span>
					</div>
				</div>

				<!-- Payment Button Container -->
				<div id="splms-payment-button-container" class="splms-payment-container">
					<?php if ( ! empty( $splms_payment_methods ) ) { ?>
						<?php foreach ( $splms_payment_methods as $splms_method_id => $splms_method ) { ?>
							<div id="splms-<?php echo esc_attr( $splms_method_id ); ?>-button-container" class="splms-<?php echo esc_attr( $splms_method_id ); ?>-container"
								style="display: none;">
								<!-- <?php echo esc_html( $splms_method['name'] ); ?> button will be rendered here -->
							</div>
						<?php } ?>
					<?php } ?>
				</div>

				<!-- Terms and Conditions -->
				<div class="splms-terms">
					<p class="splms-terms-text">
						<?php
						// Get terms and privacy URLs.
						$splms_terms_page_id = splms_get_setting( 'terms_conditions_page_id', 0 );
						$splms_terms_url     = '';
						if ( $splms_terms_page_id ) {
							$splms_terms_page = get_post( $splms_terms_page_id );
							if ( $splms_terms_page && 'publish' === $splms_terms_page->post_status ) {
								$splms_terms_url = get_permalink( $splms_terms_page_id );
							}
						}

						$splms_privacy_page_id = splms_get_setting( 'privacy_policy_page_id', 0 );
						$splms_privacy_url     = '';
						if ( $splms_privacy_page_id ) {
							$splms_privacy_page = get_post( $splms_privacy_page_id );
							if ( $splms_privacy_page && 'publish' === $splms_privacy_page->post_status ) {
								$splms_privacy_url = get_permalink( $splms_privacy_page_id );
							}
						}
						if ( empty( $splms_privacy_url ) ) {
							$splms_privacy_url = get_privacy_policy_url();
						}

						// Build the message with links.
						$splms_terms_link   = $splms_terms_url ? '<a href="' . esc_url( $splms_terms_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__(
							'Terms of Service',
							'skillpulse-lms'
						) . '</a>' : esc_html__( 'Terms of Service', 'skillpulse-lms' );
						$splms_privacy_link = $splms_privacy_url ? '<a href="' . esc_url( $splms_privacy_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__(
							'Privacy Policy',
							'skillpulse-lms'
						) . '</a>' : esc_html__( 'Privacy Policy', 'skillpulse-lms' );

						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Links are properly escaped above, and wp_kses_post will sanitize the output.
						echo wp_kses_post(
							sprintf(
							/* translators: %1$s: Terms of Service link, %2$s: Privacy Policy link */
								__( 'By completing this purchase, you agree to our %1$s and %2$s.', 'skillpulse-lms' ),
								$splms_terms_link,
								$splms_privacy_link
							)
						);
						?>
					</p>
				</div>

			</form>
		</div>
	</div>
</div>

<!-- Initialize Purchase Data (ALWAYS runs for all courses) -->
<script>
(function() {
	'use strict';

	// CRITICAL: Initialize purchase data for ALL payment gateways.
	// All gateways (Stripe, PayPal, Razorpay) use window.splmsPurchaseData.courseId.
	// This must run for ALL courses, not just those with section pricing.
	window.splmsPurchaseData = {
		type: 'full_course',
		courseId: <?php echo esc_js( $splms_course_id ); ?>,
		fullCoursePrice: parseFloat('<?php echo esc_js( $splms_price ); ?>'),
		selectedSections: [],
		<?php if ( $splms_show_upgrade_option && ! empty( $splms_upgrade_data ) ) { ?>
		isUpgrade: true,
		upgradePrice: parseFloat('<?php echo esc_js( $splms_upgrade_data['upgrade_price'] ); ?>'),
		alreadyPaid: parseFloat('<?php echo esc_js( $splms_upgrade_data['already_paid'] ); ?>'),
		purchasedSections: <?php echo wp_json_encode( $splms_upgrade_data['purchased_sections'] ); ?>,
		<?php } else { ?>
		isUpgrade: false,
		<?php } ?>
	};
})();
</script>

<?php if ( $splms_show_section_pricing ) { ?>
<script>
(function() {
	'use strict';

	const currency = '<?php echo esc_js( $splms_currency ); ?>';
	const fullCoursePrice = parseFloat('<?php echo esc_js( $splms_price ); ?>');
	const sectionsData = <?php echo wp_json_encode( $splms_sections_with_pricing ); ?>;
	const showBothOptions = <?php echo esc_attr( $splms_show_both_options ? 'true' : 'false' ); ?>;

	// DOM Elements.
	const purchaseTypeRadios = document.querySelectorAll('input[name="purchase_type"]');
	const sectionsList = document.querySelector('.splms-sections-list');
	const sectionCheckboxes = document.querySelectorAll('.splms-section-checkbox');
	const totalValueElement = document.querySelector('.splms-total-value');
	const sectionsSummary = document.querySelector('.splms-sections-summary');
	const fullCourseSummary = document.querySelector('.splms-full-course-summary');

	// Toggle between full course and sections.
	if (showBothOptions) {
		purchaseTypeRadios.forEach(radio => {
			radio.addEventListener('change', function() {
				if (this.value === 'full_course') {
					sectionsList.style.display = 'none';
					fullCourseSummary.style.display = '';
					sectionsSummary.style.display = 'none';
					updateTotal();
				} else {
					sectionsList.style.display = '';
					fullCourseSummary.style.display = 'none';
					sectionsSummary.style.display = '';
					updateTotal();
				}
			});
		});
	}

	// Handle section selection.
	sectionCheckboxes.forEach(checkbox => {
		checkbox.addEventListener('change', updateTotal);
	});

	function updateTotal() {
		const purchaseType = showBothOptions
			? document.querySelector('input[name="purchase_type"]:checked')?.value || 'full_course'
			: 'sections';

		if (purchaseType === 'full_course') {
			// Show full course price.
			totalValueElement.textContent = currency + ' ' + fullCoursePrice.toFixed(2);
			sectionsSummary.innerHTML = '';
		} else {
			// Calculate total from selected sections.
			let total = 0;
			const selectedSections = [];

			sectionCheckboxes.forEach(checkbox => {
				if (checkbox.checked && !checkbox.disabled) {
					const price = parseFloat(checkbox.dataset.price);
					const sectionId = parseInt(checkbox.dataset.sectionId);
					total += price;

					// Find section data.
					const section = sectionsData.find(s => parseInt( s.id ) === parseInt( sectionId ));
					if (section) {
						selectedSections.push(section);
					}
				}
			});

			// Update total.
			totalValueElement.textContent = currency + ' ' + total.toFixed(2);

			// Update sections summary.
			if (selectedSections.length > 0) {
				let summaryHTML = '';
				selectedSections.forEach(section => {
					const displayPrice = section.sale_price > 0 ? section.sale_price : section.price;
					summaryHTML += `
						<div class="splms-summary-line">
							<span class="splms-summary-label">${escapeHtml(section.title)}</span>
							<span class="splms-summary-value">${currency} ${displayPrice.toFixed(2)}</span>
						</div>
					`;
				});
				sectionsSummary.innerHTML = summaryHTML;
			} else {
				sectionsSummary.innerHTML = '<div class="splms-no-selection"><?php echo esc_js( __( 'No sections selected', 'skillpulse-lms' ) ); ?></div>';
			}
		}

		// Store purchase data for payment gateway.
		storePurchaseData(purchaseType);
	}

	function storePurchaseData(purchaseType) {
		const purchaseData = {
			type: purchaseType,
			courseId: <?php echo esc_js( $splms_course_id ); ?>,
			fullCoursePrice: fullCoursePrice,
			selectedSections: []
		};

		if (purchaseType === 'sections') {
			sectionCheckboxes.forEach(checkbox => {
				if (checkbox.checked && !checkbox.disabled) {
					const sectionId = parseInt(checkbox.dataset.sectionId);
					const section = sectionsData.find(s => parseInt(s.id) === parseInt(sectionId));
					if (section) {
						purchaseData.selectedSections.push({
							id: section.id,
							title: section.title,
							price: section.sale_price > 0 ? section.sale_price : section.price
						});
					}
				}
			});
		}

		// Store in window object for payment gateway access.
		window.splmsPurchaseData = purchaseData;
	}

	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize on page load.
	updateTotal();

	// If a specific section is pre-selected, switch to "Individual Sections" option.
	if (showBothOptions) {
		const hasPreselectedSection = Array.from(sectionCheckboxes).some(checkbox => checkbox.checked && !checkbox.disabled);
		if (hasPreselectedSection) {
			const sectionsRadio = document.getElementById('purchase-sections');
			if (sectionsRadio) {
				sectionsRadio.checked = true;
				sectionsList.style.display = '';
				fullCourseSummary.style.display = 'none';
				sectionsSummary.style.display = '';
				updateTotal();
			}
		}
	}
})();
</script>
<?php } ?>

<?php wp_footer(); ?>
</body>
</html>