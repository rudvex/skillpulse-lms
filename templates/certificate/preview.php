<?php
/**
 * Certificate Preview Template
 *
 * Displays backend-configured certificates with sharing, print, and download functionality.
 * Used when students access certificate URLs: {site_url}/certificate/{hash}
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get download icon SVG.
 *
 * @return string Download icon SVG markup.
 */
function splms_get_download_icon() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M21 15V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		<polyline points="7,10 12,15 17,10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
		<line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>';
}

/**
 * Get spinner icon SVG for loading states.
 *
 * @return string Spinner icon SVG markup.
 */
function splms_get_spinner_icon() {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M12 2V6M12 18V22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12H6M18 12H22M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>';
}

/**
 * Generate social share links for certificate.
 *
 * @param string $certificate_url Certificate URL.
 * @param string $share_text      Share text.
 * @return array Social sharing platform data.
 */
function splms_get_certificate_share_links( $certificate_url, $share_text ) {
	$platforms = array(
		'linkedin' => array(
			'url'   => 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $certificate_url ),
			'label' => __( 'Share on LinkedIn', 'skillpulse-lms' ),
			'class' => 'share-linkedin',
		),
		'facebook' => array(
			'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $certificate_url ),
			'label' => __( 'Share on Facebook', 'skillpulse-lms' ),
			'class' => 'share-facebook',
		),
		'twitter'  => array(
			'url'   => 'https://twitter.com/intent/tweet?text=' . rawurlencode( $share_text ) . '&url=' . rawurlencode( $certificate_url ),
			'label' => __( 'Share on Twitter', 'skillpulse-lms' ),
			'class' => 'share-twitter',
		),
	);

	return apply_filters( 'splms_certificate_share_platforms', $platforms );
}

// Get certificate data from query var (like signup system).
$certificate_key = get_query_var( 'certificate_key' );

if ( ! $certificate_key ) {
	wp_die( esc_html__( 'Invalid certificate key.', 'skillpulse-lms' ) );
}

// Decode certificate data.
$certificates_instance = SkillPulse_LMS_Certificates::get_instance();
$data                  = $certificates_instance->decode_certificate_data( $certificate_key );

if ( ! $data ) {
	wp_die( esc_html__( 'Invalid certificate data.', 'skillpulse-lms' ) );
}

$user_id   = $data['user_id'];
$course_id = $data['course_id'];

// Validate data.
if ( ! $user_id || ! $course_id ) {
	wp_die( esc_html__( 'Invalid certificate data.', 'skillpulse-lms' ) );
}

// Get user and course data.
$user   = get_userdata( $user_id );
$course = get_post( $course_id );

if ( ! $user || ! $course ) {
	wp_die( esc_html__( 'Certificate data not found.', 'skillpulse-lms' ) );
}

// Get certificate template configured for this course.
$content_info   = splms_get_course_content_info( $course_id );
$certificate_id = isset( $content_info['certificate_template_id'] ) ? $content_info['certificate_template_id'] : '';

// If no specific certificate set, use default.
if ( ! $certificate_id ) {
	$certificate_id = $certificates_instance->get_default_certificate();
}

if ( ! $certificate_id ) {
	wp_die( esc_html__( 'Certificate template not available.', 'skillpulse-lms' ) );
}

// Get certificate post data.
$certificate_post = get_post( $certificate_id );
if ( ! $certificate_post ) {
	wp_die( esc_html__( 'Certificate template not found.', 'skillpulse-lms' ) );
}

// Certificate metadata.
$certificate_title = $certificate_post->post_title;

// Generate meta description for SEO and social sharing.
$meta_description = sprintf(
	/* translators: %1$s: User display name, %2$s: Course title. */
	__( '%1$s has successfully completed %2$s', 'skillpulse-lms' ),
	$user->display_name,
	$course->post_title
);

// Generate certificate content using unified HTML generator.
// Canvas dimensions and styling are handled automatically by the HTML generator.
$certificate_content = '';
if ( class_exists( 'SkillPulse_LMS_Certificate_HTML_Generator' ) ) {
	$certificate_content = SkillPulse_LMS_Certificate_HTML_Generator::generate_certificate_html(
		$certificate_id,
		array(
			'user_id'         => $user_id,
			'course_id'       => $course_id,
			'context'         => 'print', // Use print context to include print styles.
			'include_css'     => true,
			'certificate_key' => $certificate_key, // For QR code generation.
		)
	);
}

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo esc_html( sprintf( /* translators: %1$s: Certificate title, %2$s: User display name, %3$s: Course title. */ __( '%1$s - %2$s - %3$s', 'skillpulse-lms' ), $certificate_title, $user->display_name, $course->post_title ) ); ?></title>

		<!-- Social Media Meta Tags -->
		<meta property="og:title" content="<?php echo esc_attr( sprintf( /* translators: %s: User display name. */ __( 'Certificate of Completion - %s', 'skillpulse-lms' ), $user->display_name ) ); ?>" />
		<meta property="og:description" content="<?php echo esc_attr( $meta_description ); ?>" />
		<meta property="og:type" content="website" />
		<meta property="og:url" content="<?php echo esc_url( home_url( "/certificate/{$certificate_key}" ) ); ?>" />
		<meta property="og:site_name" content="<?php bloginfo( 'name' ); ?>" />

		<!-- Twitter Card Meta Tags -->
		<meta name="twitter:card" content="summary_large_image" />
		<meta name="twitter:title" content="<?php echo esc_attr( sprintf( /* translators: %s: User display name. */ __( 'Certificate of Completion - %s', 'skillpulse-lms' ), $user->display_name ) ); ?>" />
		<meta name="twitter:description" content="<?php echo esc_attr( $meta_description ); ?>" />

		<!-- LinkedIn specific -->
		<meta name="description" content="<?php echo esc_attr( $meta_description ); ?>" />

		<!-- Favicon -->
		<?php if ( function_exists( 'get_site_icon_url' ) && get_site_icon_url() ) : ?>
		<link rel="icon" href="<?php echo esc_url( get_site_icon_url() ); ?>" />
		<?php endif; ?>

		<!-- Schema.org Structured Data -->
		<script type="application/ld+json">
		{
			"@context": "https://schema.org",
			"@type": "EducationalOccupationalCredential",
			"name": "<?php echo esc_js( $certificate_title ); ?>",
			"description": "<?php echo esc_js( sprintf( /* translators: %s: Course title. */ __( 'Certificate of completion for %s', 'skillpulse-lms' ), $course->post_title ) ); ?>",
			"credentialCategory": "Certificate",
			"dateCreated": "<?php echo esc_js( get_the_date( 'c', $course_id ) ); ?>",
			"url": "<?php echo esc_js( home_url( "/certificate/{$certificate_key}" ) ); ?>",
			"recognizedBy": {
				"@type": "Organization",
				"name": "<?php echo esc_js( get_bloginfo( 'name' ) ); ?>",
				"url": "<?php echo esc_js( home_url() ); ?>"
			},
			"about": {
				"@type": "Course",
				"name": "<?php echo esc_js( $course->post_title ); ?>",
				"description": "<?php echo esc_js( wp_strip_all_tags( get_the_excerpt( $course_id ) ) ); ?>",
				"provider": {
					"@type": "Organization",
					"name": "<?php echo esc_js( get_bloginfo( 'name' ) ); ?>"
				}
			},
			"holder": {
				"@type": "Person",
				"name": "<?php echo esc_js( $user->display_name ); ?>"
			}
		}
		</script>

		<!-- Include jQuery and certificate scripts directly -->
		<?php wp_print_scripts( array( 'jquery' ) ); ?>
		<script type="text/javascript">
			// Setup AJAX variables for certificate functionality
			var splms_frontend = {
				ajax_url: '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>',
				nonces: {
					splms_certificate_nonce: '<?php echo esc_js( wp_create_nonce( 'splms_certificate_nonce' ) ); ?>'
				},
				site_url: '<?php echo esc_js( home_url() ); ?>'
			};

			// Define icon SVGs as variables to avoid escaping issues
			var splms_icons = {
				download: <?php echo wp_json_encode( splms_get_download_icon() ); ?>,
				spinner: <?php echo wp_json_encode( splms_get_spinner_icon() ); ?>
			};
		</script>

		<style>
			/* Canvas styling is now handled by the HTML generator */

			/* Reset and base styles */
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}

			body {
				font-family: 'Georgia', serif;
				font-display: swap;
				line-height: 1.6;
				color: #333;
				background: #f8f9fa;
				margin: 0;
				padding: 0;
			}

			/* Certificate container */
			.certificate-wrapper {
				min-height: 100vh;
				display: flex;
				flex-direction: column;
				padding: 20px;
			}

			.certificate-main {
				flex: 1;
				max-width: 100%;
				margin: 0 auto;
				background: white;
				box-shadow: 0 4px 20px rgba(0,0,0,0.1);
				border-radius: 8px;
				overflow: hidden;
			}

			/* Certificate content area */
			.certificate-content {
				padding: 20px;
				text-align: center;
			}

			/* Generated certificate area */
			.generated-certificate {
				width: 100%;
				overflow-x: auto;
			}

			/* Action buttons */
			.certificate-actions {
				background: #f8f9fa;
				padding: 30px 40px;
				border-top: 2px solid #007cba;
				text-align: center;
				position: relative;
				margin-top: 20px;
			}

			.certificate-actions::before {
				content: "Certificate Actions";
				position: absolute;
				top: -12px;
				left: 50%;
				transform: translateX(-50%);
				background: #007cba;
				color: white;
				padding: 5px 15px;
				border-radius: 15px;
				font-size: 12px;
				font-weight: bold;
			}

			/* Base button styles */
			.action-btn {
				display: inline-block;
				padding: 12px 24px;
				margin: 0 10px 10px 0;
				color: white;
				text-decoration: none;
				border-radius: 5px;
				border: none;
				font-size: 16px;
				cursor: pointer;
				transition: all 0.3s ease;
				font-family: inherit;
				line-height: 1.4;
			}

			.action-btn:hover {
				transform: translateY(-1px);
				box-shadow: 0 2px 8px rgba(0,0,0,0.15);
			}

			.action-btn:focus {
				outline: 2px solid currentColor;
				outline-offset: 2px;
			}

			/* Button variants */
			.action-btn--primary {
				background: #007cba;
			}

			.action-btn--primary:hover {
				background: #005a87;
			}

			.action-btn--success {
				background: #28a745;
			}

			.action-btn--success:hover {
				background: #1e7e34;
			}

			.action-btn--info {
				background: #17a2b8;
			}

			.action-btn--info:hover {
				background: #117a8b;
			}

			/* Icon styling */
			.action-btn svg {
				margin-right: 5px;
				vertical-align: middle;
			}

			/* Disabled state */
			.action-btn:disabled {
				background: #6c757d !important;
				cursor: not-allowed !important;
				transform: none !important;
				box-shadow: none !important;
			}

			/* Loading animation */
			@keyframes spin {
				0% { transform: rotate(0deg); }
				100% { transform: rotate(360deg); }
			}

			.action-btn:disabled svg {
				animation: spin 1s linear infinite;
			}

			/* Share buttons */
			.share-buttons {
				margin-top: 15px;
			}

			.share-btn {
				display: inline-block;
				padding: 8px 16px;
				margin: 0 5px;
				color: white;
				text-decoration: none;
				border-radius: 4px;
				font-size: 14px;
				transition: opacity 0.3s;
			}

			.share-btn:hover,
			.share-btn:focus {
				opacity: 0.8;
				color: white;
			}

			.share-btn:focus {
				outline: 2px solid currentColor;
				outline-offset: 2px;
			}

			.share-linkedin { background: #0077b5; }
			.share-facebook { background: #1877f2; }
			.share-twitter { background: #1da1f2; }

			/* Certificate verification info */
			.certificate-info {
				text-align: center;
				font-size: 12px;
				color: #6c757d;
				margin-top: 15px;
			}

			/* Print styles - layout only, certificate styles handled by HTML generator */
			@media print {
				/* Force full page layout and hide browser UI */
				html, body {
					width: 100% !important;
					height: 100% !important;
					margin: 0 !important;
					padding: 0 !important;
					overflow: visible !important;
					background: white !important;
					background-color: white !important;
					font-size: 12pt;
				}

				/* Reset background for wrapper elements to white */
				body, .certificate-wrapper, .certificate-main, .certificate-content {
					background: white !important;
					background-color: white !important;
				}

				.certificate-wrapper {
					padding: 0 !important;
					margin: 0 !important;
					height: 100vh !important;
					width: 100vw !important;
					display: flex !important;
					justify-content: center !important;
					align-items: center !important;
					position: fixed !important;
					top: 0 !important;
					left: 0 !important;
					background: white !important;
				}

				.certificate-main {
					box-shadow: none !important;
					border-radius: 0 !important;
					max-width: none !important;
					width: auto !important;
					margin: 0 !important;
					height: auto !important;
					flex: none !important;
					background: white !important;
					display: flex !important;
					justify-content: center !important;
					align-items: center !important;
				}

				/* Hide everything first */
				body * {
					visibility: hidden !important;
				}

				/* Show only certificate content */
				.certificate-wrapper,
				.certificate-wrapper *,
				.certificate-main,
				.certificate-main *,
				.certificate-content,
				.certificate-content *,
				.generated-certificate,
				.generated-certificate *,
				.fallback-certificate,
				.fallback-certificate * {
					visibility: visible !important;
				}

				/* Hide non-essential elements */
				.certificate-actions,
				.certificate-info,
				nav,
				header,
				footer,
				aside,
				.sidebar,
				.widget,
				#sidebar {
					display: none !important;
					visibility: hidden !important;
				}

				.certificate-content {
					padding: 0 !important;
					margin: 0 !important;
					height: 100vh !important;
					width: 100vw !important;
					display: flex !important;
					justify-content: center !important;
					align-items: center !important;
					position: absolute !important;
					top: 0 !important;
					left: 0 !important;
					background: white !important;
				}

				/* Certificate container positioning for print */
				.generated-certificate {
					width: 100% !important;
					height: auto !important;
					margin: 0 !important;
					padding: 0 !important;
					overflow: visible !important;
					page-break-inside: avoid !important;
				}

				/* Fallback certificate print styles */
				.fallback-certificate {
					page-break-inside: avoid !important;
					width: 100% !important;
					overflow: visible !important;
					height: auto !important;
					margin: 0 !important;
					padding: 20px !important;
				}
			}

			/* Mobile responsive */
			@media (max-width: 768px) {
				.certificate-wrapper {
					padding: 10px;
				}

				.certificate-content {
					padding: 15px;
				}

				.certificate-actions {
					padding: 20px 15px;
				}

				.certificate-actions::before {
					font-size: 11px;
					padding: 4px 12px;
				}

				.action-btn {
					display: block;
					margin: 8px 0;
					width: 100%;
					font-size: 14px;
					padding: 10px 20px;
				}

				.share-buttons {
					margin-top: 15px;
				}

				.share-btn {
					display: block;
					margin: 8px 0;
					text-align: center;
					padding: 10px 16px;
				}
			}

			/* Tablet responsive */
			@media (min-width: 769px) and (max-width: 1024px) {
				.certificate-wrapper {
					padding: 15px;
				}

				.action-btn {
					margin: 5px;
					font-size: 15px;
				}
			}
		</style>
	</head>
	<body>
		<div class="certificate-wrapper">
			<div class="certificate-main">
				<div class="certificate-content">
						<!-- Generated certificate content -->
						<div class="generated-certificate">
							<?php
							// Output certificate content directly since it's generated by our own system.
							echo $certificate_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>

				</div>

				<div class="certificate-actions">
					<!-- Print Button -->
					<button class="action-btn action-btn--primary print-btn"
							aria-label="<?php esc_attr_e( 'Print this certificate', 'skillpulse-lms' ); ?>"
							title="<?php esc_attr_e( 'Print Certificate', 'skillpulse-lms' ); ?>">
						<?php esc_html_e( 'Print Certificate', 'skillpulse-lms' ); ?>
					</button>


					<!-- Social Sharing -->
					<div class="share-buttons">
						<?php
						$certificate_url = esc_url( home_url( "/certificate/{$certificate_key}" ) );
						$share_text      = sprintf( /* translators: %s: Course title. */ __( 'I just completed %s and earned my certificate!', 'skillpulse-lms' ), $course->post_title );
						$share_platforms = splms_get_certificate_share_links( $certificate_url, $share_text );

						foreach ( $share_platforms as $platform => $data ) :
							?>
							<a href="<?php echo esc_url( $data['url'] ); ?>"
								target="_blank"
								class="share-btn <?php echo esc_attr( $data['class'] ); ?>"
								title="<?php echo esc_attr( $data['label'] ); ?>"
								aria-label="<?php echo esc_attr( $data['label'] ); ?>">
								<?php echo esc_html( ucfirst( $platform ) ); ?>
							</a>
							<?php
						endforeach;
						?>
					</div>

					<div class="certificate-info">
						<?php
						printf(
							/* translators: %1$s: Certificate ID, %2$s: Verification URL. */
							esc_html__( 'Certificate ID: %1$s | Verify at %2$s', 'skillpulse-lms' ),
							esc_html( substr( $certificate_key, 0, 16 ) . '...' ),
							esc_html( home_url() )
						);
						?>
					</div>
				</div>
			</div>
		</div>

		<script>
			// Enhanced print and download functionality.
			document.addEventListener('DOMContentLoaded', function() {
				// Handle print button
				const printBtn = document.querySelector('.print-btn');
				if (printBtn) {
					printBtn.addEventListener('click', function(e) {
						e.preventDefault();
						window.print();
					});
				}

				// Note: Download functionality removed - certificates now use print instead

				// Handle keyboard shortcuts.
				document.addEventListener('keydown', function(e) {
					// Ctrl+P for print
					if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
						e.preventDefault();
						window.print();
					}
				});

				// Note: Certificate download function removed - using print instead

				function setButtonLoading(button, loading) {
					if (loading) {
						button.disabled = true;
						// Store original content for restoration
						if (!button.dataset.originalContent) {
							button.dataset.originalContent = button.innerHTML;
						}
						button.innerHTML = splms_icons.spinner + ' <?php esc_html_e( 'Preparing...', 'skillpulse-lms' ); ?>';
					} else {
						button.disabled = false;
						// Restore original content or fallback
						if (button.dataset.originalContent) {
							button.innerHTML = button.dataset.originalContent;
							delete button.dataset.originalContent;
						} else {
							button.innerHTML = splms_icons.print + ' <?php esc_html_e( 'Print Certificate', 'skillpulse-lms' ); ?>';
						}
					}
				}

				// Note: Download file function removed - using browser print instead

				function showMessage(message, type) {
					// Remove any existing notifications
					const existingNotifications = document.querySelectorAll('.splms-notification');
					existingNotifications.forEach(notification => {
						if (notification.parentNode) {
							notification.parentNode.removeChild(notification);
						}
					});

					// Create notification
					const notification = document.createElement('div');
					notification.className = 'splms-notification';
					notification.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 5px; color: white; font-weight: bold; z-index: 10000; max-width: 300px; cursor: pointer; transition: opacity 0.3s ease;';
					notification.style.backgroundColor = type === 'error' ? '#dc3545' : '#28a745';
					notification.textContent = message;
					notification.setAttribute('role', 'alert');
					notification.setAttribute('aria-live', 'polite');

					document.body.appendChild(notification);

					// Auto-remove with proper cleanup
					const timeoutId = setTimeout(() => {
						removeNotification(notification);
					}, 5000);

					// Allow manual dismissal
					notification.addEventListener('click', () => {
						clearTimeout(timeoutId);
						removeNotification(notification);
					});

					// Helper function to safely remove notification
					function removeNotification(element) {
						if (element && element.parentNode) {
							element.style.opacity = '0';
							setTimeout(() => {
								if (element.parentNode) {
									element.parentNode.removeChild(element);
								}
							}, 300);
						}
					}
				}
			});
		</script>
	</body>
</html>