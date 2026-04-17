<?php
/**
 * Certificate HTML Generator
 *
 * Unified certificate HTML generation for web display and print.
 * Provides consistent styling and layout across all certificate contexts.
 *
 * @since      1.0.0
 * @subpackage Certificates
 * @package    SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Certificate HTML Generator Class
 *
 * Generates consistent certificate HTML using builder data and meta settings.
 * Supports multiple contexts: web, print, preview.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Certificate_HTML_Generator {

	/**
	 * Generate certificate HTML
	 *
	 * @param int   $certificate_id  Certificate post ID.
	 * @param array $args            {
	 *                               Optional. Certificate generation arguments.
	 *
	 * @type int    $user_id         User ID for certificate recipient.
	 * @type int    $course_id       Course ID for certificate.
	 * @type string $context         Context: 'web', 'print', 'preview'. Default 'web'.
	 * @type string $completion_date Custom completion date. Default current date.
	 * @type bool   $include_css     Whether to include CSS. Default true.
	 * @type array  $style_overrides Custom style overrides.
	 *                               }
	 * @since 1.0.0
	 *
	 * @return string Generated certificate HTML.
	 */
	public static function generate_certificate_html( $certificate_id, $args = array() ) {
		// Default arguments.
		$defaults = array(
			'user_id'         => get_current_user_id(),
			'course_id'       => 0,
			'context'         => 'web',
			'completion_date' => '',
			'include_css'     => true,
			'style_overrides' => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		// Validate required data.
		if ( ! $certificate_id ) {
			return self::generate_error_html( 'Invalid certificate ID.' );
		}

		$certificate_post = get_post( $certificate_id );
		if ( ! $certificate_post || SPLMS_POST_TYPES['certificate'] !== $certificate_post->post_type ) {
			return self::generate_error_html( 'Certificate template not found.' );
		}

		// Get user and course data.
		$user = get_userdata( $args['user_id'] );
		if ( ! $user ) {
			return self::generate_error_html( 'Invalid user data.' );
		}

		$course = null;
		if ( $args['course_id'] ) {
			$course = get_post( $args['course_id'] );
			if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
				return self::generate_error_html( 'Invalid course data.' );
			}
		}

		// Get certificate builder data and settings.
		$builder_data = get_post_meta( $certificate_id, '_splms_certificate_builder_data', true );
		$settings     = is_array( $builder_data ) && isset( $builder_data['settings'] ) ? $builder_data['settings'] : array();

		// Extract canvas background color if not provided in style overrides.
		if ( ! isset( $args['style_overrides']['background_color'] ) && is_array( $builder_data ) && isset( $builder_data['canvas']['background'] ) ) {
			if ( ! isset( $args['style_overrides'] ) ) {
				$args['style_overrides'] = array();
			}
			$args['style_overrides']['background_color'] = sanitize_hex_color( $builder_data['canvas']['background'] );
		}

		// Generate completion date.
		$completion_date = $args['completion_date'];
		if ( empty( $completion_date ) ) {
			if ( $args['course_id'] ) {
				// Try to get actual completion date from enrollment.
				$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
				$enrollment        = $enrollments_query->get_enrollment( $args['user_id'], $args['course_id'] );
				if ( $enrollment && ! empty( $enrollment->completed_at ) ) {
					$completion_date = date_i18n( get_option( 'date_format' ), strtotime( $enrollment->completed_at ) );
				}
			}

			if ( empty( $completion_date ) ) {
				$completion_date = date_i18n( get_option( 'date_format' ) );
			}
		}

		// Generate certificate HTML.
		$html = '';

		if ( $args['include_css'] ) {
			$html .= self::generate_certificate_styles( $settings, $args['context'], $args['style_overrides'], $certificate_id );
		}

		$html .= self::generate_certificate_content( $certificate_post, $user, $course, $completion_date, $settings, $args );

		return $html;
	}

	/**
	 * Generate certificate styles with canvas dimensions and styling.
	 *
	 * @param array  $settings        Certificate builder settings.
	 * @param string $context         Generation context.
	 * @param array  $style_overrides Custom style overrides.
	 * @param int    $certificate_id  Certificate post ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS styles.
	 */
	private static function generate_certificate_styles( $settings, $context, $style_overrides = array(), $certificate_id = 0 ) {
		// Get canvas dimensions and styling from certificate builder data.
		$canvas_data = self::get_canvas_settings( $certificate_id, $style_overrides );

		// Extract colors from settings.
		$background_color = isset( $settings['backgroundColor'] ) ? $settings['backgroundColor'] : $canvas_data['background'];
		$text_color       = isset( $settings['textColor'] ) ? $settings['textColor'] : '#000000';
		$border_color     = isset( $settings['borderColor'] ) ? $settings['borderColor'] : '#2c5aa0';
		$accent_color     = isset( $settings['accentColor'] ) ? $settings['accentColor'] : '#2c5aa0';

		// Apply style overrides.
		$background_color = isset( $style_overrides['background_color'] ) ? $style_overrides['background_color'] : $background_color;
		$text_color       = isset( $style_overrides['text_color'] ) ? $style_overrides['text_color'] : $text_color;
		$border_color     = isset( $style_overrides['border_color'] ) ? $style_overrides['border_color'] : $border_color;
		$accent_color     = isset( $style_overrides['accent_color'] ) ? $style_overrides['accent_color'] : $accent_color;

		// Note: PDF context no longer supported - certificates are now print/web only.

		ob_start();
		?>
		<style>
			/* Certificate Container with exact canvas dimensions */
			.splms-certificate-container {
				width: <?php echo esc_attr( $canvas_data['width'] ); ?>px !important;
				height: <?php echo esc_attr( $canvas_data['height'] ); ?>px !important;
				max-width: <?php echo esc_attr( $canvas_data['width'] ); ?>px !important;
				max-height: <?php echo esc_attr( $canvas_data['height'] ); ?>px !important;
				border: 1px solid<?php echo esc_attr( $border_color ); ?>;
				padding: 3.5rem;
				background: <?php echo esc_attr( $background_color ); ?>;
				background-color: <?php echo esc_attr( $background_color ); ?> !important;
				text-align: center;
				position: relative;
				border-radius: 12px;
				box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
				overflow: hidden;
				margin: 0 auto !important;
				display: block !important;
				font-family: 'Georgia', serif;
				color: <?php echo esc_attr( $text_color ); ?>;
				line-height: 1.4;
				box-sizing: border-box;
			}

			<?php if ( 'web' === $context ) : ?>
			/* Responsive scaling for smaller screens */
			@media (max-width: <?php echo esc_attr( $canvas_data['width'] + 40 ); ?>px) {
				.splms-certificate-container {
					width: 100% !important;
					max-width: calc(100vw - 40px) !important;
					height: auto !important;
					max-height: none !important;
					transform: scale(<?php echo esc_attr( min( 1, ( 100 / max( $canvas_data['width'], 320 ) ) ) ); ?>);
					transform-origin: top center !important;
				}
			}

			<?php endif; ?>


			/* Logo placeholder */
			.splms-certificate-logo {
				text-align: center;
				margin-bottom: 2rem;
			}

			.splms-logo-placeholder {
				display: inline-block;
				width: 80px;
				height: 60px;
				border: 2px dashed #ccc;
				border-radius: 4px;
				text-align: center;
				padding: 8px;
				background: #f9f9f9;
			}

			.splms-logo-icon {
				font-size: 24px;
				color: #999;
				line-height: 1;
			}

			.splms-logo-text {
				font-size: 10px;
				color: #999;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				margin-top: 2px;
			}

			/* Header */
			.splms-certificate-header {
				margin-bottom: 3rem;
				text-align: center;
			}

			.splms-certificate-main-title {
				font-size: 2.5rem;
				color: <?php echo esc_attr( $accent_color ); ?>;
				margin-bottom: 0.5rem;
				font-weight: 700;
				font-family: 'Georgia', serif;
				text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			}

			.splms-certificate-subtitle {
				font-size: 1.2rem;
				color: #666;
				font-style: italic;
				font-family: 'Georgia', serif;
				margin-bottom: 0;
			}

			/* Body */
			.splms-certificate-body {
				margin: 2rem 0;
			}

			/* This is to certify that section */
			.splms-certificate-certify-section {
				text-align: center;
				margin-bottom: 2rem;
			}

			.splms-certificate-certify-text {
				font-size: 1.4rem;
				color: #333;
				margin-bottom: 1rem;
				font-family: 'Georgia', serif;
				text-decoration: underline;
			}

			.splms-certificate-recipient {
				font-size: 2.2rem;
				color: #333;
				margin: 0;
				font-weight: 700;
				font-family: 'Georgia', serif;
				letter-spacing: 1px;
			}

			/* Description */
			.splms-certificate-description {
				font-size: 1.1rem;
				margin: 2rem 0;
				color: #555;
				text-align: center;
				font-style: italic;
				line-height: 1.6;
				padding: 0 3rem;
			}

			/* Course information */
			.splms-certificate-course-section {
				text-align: center;
				margin: 2rem 0;
			}

			.splms-certificate-course-label {
				font-size: 1.3rem;
				color: #333;
				font-weight: 600;
				margin-bottom: 0.5rem;
				font-family: 'Georgia', serif;
			}

			.splms-certificate-completion-date {
				font-size: 1.1rem;
				color: #666;
				margin-bottom: 0.5rem;
				font-family: 'Georgia', serif;
			}

			.splms-certificate-id-display {
				font-size: 1.1rem;
				color: #666;
				margin-bottom: 0;
				font-family: 'Georgia', serif;
			}

			/* Details Section */
			.splms-certificate-details {
				margin: 3.5rem 0;
				display: flex;
				justify-content: space-around;
				flex-wrap: wrap;
				background: linear-gradient(135deg, rgba(44, 90, 160, 0.06) 0%, rgba(44, 90, 160, 0.12) 100%);
				padding: 2.5rem;
				border-radius: 15px;
				border: 2px solid rgba(44, 90, 160, 0.2);
				box-shadow: 0 6px 20px rgba(44, 90, 160, 0.1);
				position: relative;
			}

			.splms-certificate-details::before {
				content: '';
				position: absolute;
				top: -1px;
				left: 30px;
				right: 30px;
				height: 3px;
				background: linear-gradient(90deg, <?php echo esc_attr( $accent_color ); ?> 0%, rgba(44, 90, 160, 0.3) 50%, <?php echo esc_attr( $accent_color ); ?> 100%);
				border-radius: 2px;
			}

			.splms-detail-item {
				margin: 1rem;
				text-align: center;
				padding: 1.5rem;
				background: linear-gradient(145deg, rgba(255, 255, 255, 0.95) 0%, rgba(248, 249, 250, 0.95) 100%);
				border-radius: 12px;
				box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
				border: 1px solid rgba(44, 90, 160, 0.1);
				min-width: 180px;
			}

			.splms-detail-item strong {
				display: block;
				color: #333;
				font-size: 1.1rem;
				margin-bottom: 0.8rem;
				font-weight: 700;
				font-family: 'Georgia', serif;
				text-transform: uppercase;
				letter-spacing: 1px;
			}

			.splms-detail-item span {
				color: <?php echo esc_attr( $accent_color ); ?>;
				font-weight: 600;
				font-size: 1.3rem;
				font-family: 'Georgia', serif;
				display: block;
				padding-top: 0.5rem;
				border-top: 2px solid rgba(44, 90, 160, 0.1);
			}

			/* Seal */
			.splms-certificate-seal {
				text-align: center;
				margin: 2.5rem 0;
			}

			.splms-seal-circle {
				width: 120px;
				height: 120px;
				border: 3px solid<?php echo esc_attr( $accent_color ); ?>;
				border-radius: 50%;
				margin: 0 auto;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				background: linear-gradient(135deg, rgba(44, 90, 160, 0.05) 0%, rgba(44, 90, 160, 0.15) 100%);
				box-shadow: 0 4px 15px rgba(44, 90, 160, 0.2);
			}

			.splms-trophy-icon {
				font-size: 2.5rem;
				margin-bottom: 0.5rem;
				filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
			}

			.splms-seal-text {
				font-size: 0.9rem;
				font-weight: 700;
				color: <?php echo esc_attr( $accent_color ); ?>;
				text-transform: uppercase;
				letter-spacing: 1px;
				font-family: 'Georgia', serif;
			}

			/* Signatures */
			.splms-certificate-signatures {
				margin-top: 3rem;
				display: flex;
				justify-content: space-around;
				flex-wrap: wrap;
				padding: 0 2rem;
			}

			.splms-signature-block {
				margin: 0 1rem;
				text-align: center;
				min-width: 200px;
				flex: 1;
				max-width: 300px;
			}

			.splms-signature-line {
				width: 180px;
				height: 1px;
				background: #333;
				margin: 0 auto 0.5rem;
				border-radius: 1px;
			}

			.splms-signature-text {
				font-weight: 400;
				color: #666;
				font-size: 0.9rem;
				font-family: 'Georgia', serif;
				letter-spacing: 0.5px;
				font-style: italic;
				margin: 0;
			}

			<?php if ( 'print' === $context ) : ?>
			/* Print Styles with canvas background preservation */
			@media print {
				@page {
					size: <?php echo $canvas_data['width'] > $canvas_data['height'] ? 'A4 landscape' : 'A4 portrait'; ?>;
					margin: 1mm;
				}

				* {
					-webkit-print-color-adjust: exact !important;
					color-adjust: exact !important;
				}

				.splms-certificate-container {
					width: <?php echo esc_attr( $canvas_data['width'] ); ?>px !important;
					height: <?php echo esc_attr( $canvas_data['height'] ); ?>px !important;
					max-width: <?php echo esc_attr( $canvas_data['width'] ); ?>px !important;
					max-height: <?php echo esc_attr( $canvas_data['height'] ); ?>px !important;
					margin: 0 auto !important;
					padding: 20mm !important;
					border: 3px solid <?php echo esc_attr( $border_color ); ?> !important;
					border-radius: 5px !important;
					background-color: <?php echo esc_attr( $background_color ); ?> !important;
					box-shadow: none !important;
					page-break-inside: avoid;
				<?php
				// Calculate scale factor for print.
				$print_width  = $canvas_data['width'] > $canvas_data['height'] ? 1008 : 720;
				$print_height = $canvas_data['width'] > $canvas_data['height'] ? 720 : 1008;
				$scale_x      = $print_width / $canvas_data['width'];
				$scale_y      = $print_height / $canvas_data['height'];
				$scale        = min( $scale_x, $scale_y );
				echo 'transform: scale(' . esc_attr( $scale ) . ') !important;';
				echo 'transform-origin: center center !important;';
				?>
				}

				.splms-certificate-logo {
					margin-bottom: 6mm !important;
				}

				.splms-certificate-main-title {
					font-size: 28pt !important;
					margin-bottom: 2mm !important;
					color: <?php echo esc_attr( $accent_color ); ?> !important;
				}

				.splms-certificate-subtitle {
					font-size: 12pt !important;
					margin-bottom: 8mm !important;
				}

				.splms-certificate-certify-section {
					margin-bottom: 6mm !important;
				}

				.splms-certificate-certify-text {
					font-size: 14pt !important;
					margin-bottom: 3mm !important;
				}

				.splms-certificate-recipient {
					font-size: 20pt !important;
					margin: 0 !important;
				}

				.splms-certificate-description {
					font-size: 12pt !important;
					margin: 6mm 0 8mm 0 !important;
					padding: 0 15mm !important;
				}

				.splms-certificate-course-section {
					margin: 6mm 0 !important;
				}

				.splms-certificate-course-label {
					font-size: 14pt !important;
					margin-bottom: 2mm !important;
				}

				.splms-certificate-completion-date {
					font-size: 12pt !important;
					margin-bottom: 2mm !important;
				}

				.splms-certificate-id-display {
					font-size: 12pt !important;
					margin-bottom: 0 !important;
				}


				.splms-certificate-seal {
					margin: 8mm 0 !important;
				}

				.splms-seal-circle {
					width: 80px !important;
					height: 80px !important;
					border: 2px solid <?php echo esc_attr( $accent_color ); ?> !important;
				}

				.splms-trophy-icon {
					font-size: 1.8rem !important;
				}

				.splms-seal-text {
					font-size: 8pt !important;
				}

				.splms-certificate-signatures {
					margin-top: 15mm !important;
				}

				.splms-signature-block {
					margin: 0 10mm !important;
				}

				.splms-signature-line {
					width: 60mm !important;
					height: 1pt !important;
					background-color: #333 !important;
					margin: 0 auto 3mm !important;
				}

				.splms-signature-text {
					font-size: 10pt !important;
				}

				/* Ensure colors are preserved */
				* {
					-webkit-print-color-adjust: exact !important;
					color-adjust: exact !important;
				}
			}

			<?php endif; ?>

			/* Certificate Element Styles */
			.certificate-element {
				display: block;
			}

			.text-content, .recipient-content, .course-details-content {
				width: 100%;
				height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				word-wrap: break-word;
				overflow-wrap: break-word;
				hyphens: auto;
				line-height: 1.2;
				padding: 4px;
			}

			/* Specific handling for course details to prevent long text overflow */
			.course-details-content {
				flex-direction: column;
				text-align: center;
				line-height: 1.4;
				gap: 2px;
			}

			/* Ensure certificate IDs don't break layout */
			.course-details-content br + * {
				word-break: break-all;
				max-width: 100%;
			}

			/* Header element with title and subtitle */
			.certificate-header {
				width: 100%;
				height: 100%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				gap: 4px;
			}

			.header-title {
				font-weight: bold;
				line-height: 1.2;
				text-align: center;
			}

			.header-subtitle {
				font-size: 0.7em;
				opacity: 0.8;
				font-style: italic;
				text-align: center;
			}

			/* Signature element */
			.signature-placeholder {
				width: 100%;
				height: 100%;
				display: flex;
				flex-direction: column;
				justify-content: flex-end;
				align-items: center;
			}

			.signature-line {
				width: 80%;
				height: 2px;
				background: #333;
				margin-bottom: 8px;
			}

			.signature-name {
				font-weight: bold;
				margin-bottom: 2px;
				font-size: 0.9em;
			}

			.signature-title {
				font-size: 0.8em;
				opacity: 0.8;
				font-style: italic;
			}

			/* Text alignment variations */
			.element-header .text-content {
				justify-content: center;
				align-items: center;
				font-weight: bold;
			}

			.element-recipient .text-content {
				justify-content: center;
				align-items: center;
				font-weight: bold;
			}

			.element-description .text-content {
				justify-content: center;
				align-items: center;
				text-align: center;
				padding: 4px;
				word-wrap: break-word;
				overflow-wrap: break-word;
				hyphens: auto;
				-webkit-hyphens: auto;
				-moz-hyphens: auto;
				line-height: 1.4;
				box-sizing: border-box;
			}

			.element-course_details .text-content {
				justify-content: center;
				align-items: center;
				text-align: center;
			}

			.image-placeholder,
			.logo-placeholder {
				width: 100%;
				height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				background: #f0f0f0;
				border: 2px dashed #ccc;
				border-radius: 4px;
				font-size: 24px;
				color: #999;
			}

			.signature-placeholder {
				width: 100%;
				height: 100%;
				display: flex;
				flex-direction: column;
				justify-content: flex-end;
			}

			.signature-line {
				width: 100%;
				height: 2px;
				background: #333;
				margin-bottom: 5px;
			}

			.signature-label {
				text-align: center;
				font-size: 12px;
				color: #666;
			}

			.qr-placeholder {
				width: 100%;
				height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				background: #f8f9fa;
				border: 1px solid #dee2e6;
				border-radius: 4px;
				font-size: 16px;
				color: #495057;
			}

			.shape-element {
				width: 100%;
				height: 100%;
			}

			.shape-rectangle {
				background: currentColor;
			}

			.shape-circle {
				background: currentColor;
				border-radius: 50%;
			}

			.shape-triangle {
				width: 0;
				height: 0;
				border-left: 50% solid transparent;
				border-right: 50% solid transparent;
				border-bottom: 100% solid currentColor;
			}

			.seal-element {
				width: 100%;
				height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			.seal-circle {
				width: 100px;
				height: 100px;
				max-width: 100%;
				max-height: 100%;
				border: 3px solid #007cba;
				border-radius: 50%;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				background: linear-gradient(135deg, rgba(0, 124, 186, 0.05) 0%, rgba(0, 124, 186, 0.15) 100%);
				box-shadow: 0 4px 15px rgba(0, 124, 186, 0.2);
			}

			.seal-icon {
				font-size: 2rem;
				margin-bottom: 0.25rem;
				filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
			}

			.seal-text {
				font-size: 0.8rem;
				font-weight: 700;
				color: #007cba;
				text-transform: uppercase;
				letter-spacing: 1px;
				font-family: 'Georgia', serif;
			}

			.generic-element {
				width: 100%;
				height: 100%;
				display: flex;
				align-items: center;
				justify-content: center;
				background: #e9ecef;
				border: 1px solid #adb5bd;
				border-radius: 4px;
				font-size: 14px;
				color: #495057;
			}

		</style>
		<?php
		return ob_get_clean();
	}


	/**
	 * Generate certificate content
	 *
	 * @param WP_Post      $certificate_post Certificate post object.
	 * @param WP_User      $user             User object.
	 * @param WP_Post|null $course           Course post object.
	 * @param string       $completion_date  Completion date string.
	 * @param array        $settings         Certificate settings.
	 * @param array        $args             Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Certificate content HTML.
	 */
	private static function generate_certificate_content( $certificate_post, $user, $course, $completion_date, $settings, $args ) {
		// Get canvas settings for positioning calculations.
		$canvas_data = self::get_canvas_settings( $certificate_post->ID, $args['style_overrides'] ?? array() );

		// Get certificate builder elements.
		$builder_data = get_post_meta( $certificate_post->ID, '_splms_certificate_builder_data', true );
		$elements     = isset( $builder_data['elements'] ) && is_array( $builder_data['elements'] ) ? $builder_data['elements'] : array();

		ob_start();
		?>
		<div class="splms-certificate-container">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Internal certificate HTML generation with controlled content.
			echo self::render_certificate_elements( $elements, $canvas_data, $user, $course, $completion_date, $args );
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render certificate elements from builder data.
	 *
	 * @param array   $elements        Certificate elements.
	 * @param array   $canvas_data     Canvas settings.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Rendered elements HTML.
	 */
	private static function render_certificate_elements( $elements, $canvas_data, $user, $course, $completion_date, $args ) {
		if ( empty( $elements ) ) {
			return '';
		}

		$html = '';

		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) || empty( $element['type'] ) ) {
				continue;
			}

			$element_html = '';

			switch ( $element['type'] ) {
				case 'text':
				case 'description':
					$element_html = self::render_text_element( $element, $user, $course, $completion_date, $args );
					break;
				case 'header':
					$element_html = self::render_header_element( $element, $user, $course, $completion_date, $args );
					break;
				case 'recipient':
					$element_html = self::render_recipient_element( $element, $user, $course, $completion_date, $args );
					break;
				case 'course_details':
					$element_html = self::render_course_details_element( $element, $user, $course, $completion_date, $args );
					break;
				case 'image':
					$element_html = self::render_image_element( $element );
					break;
				case 'signature':
					$element_html = self::render_signature_element( $element, $user, $course, $completion_date, $args );
					break;
				case 'logo':
					$element_html = self::render_logo_element( $element );
					break;
				case 'qr-code':
				case 'qr':
					$element_html = self::render_qr_element( $element, $user, $course, $args );
					break;
				case 'shape':
				case 'decorative':
				case 'seal':
					$element_html = self::render_shape_element( $element );
					break;
				default:
					// Handle unknown element types gracefully.
					$element_html = self::render_generic_element( $element );
					break;
			}

			if ( $element_html ) {
				// Wrap element with positioning styles.
				$element_styles = self::generate_element_styles( $element, $canvas_data );
				$html          .= sprintf(
					'<div class="certificate-element element-%s" style="%s">%s</div>',
					esc_attr( $element['type'] ),
					esc_attr( $element_styles ),
					$element_html
				);
			}
		}

		return $html;
	}

	/**
	 * Generate simple certificate ID to match backend format
	 *
	 * @param int $course_id Course ID.
	 *
	 * @since 1.0.0
	 *
	 * @return string Simple certificate ID string.
	 */
	private static function generate_simple_certificate_id( $course_id ) {
		// Generate simple format like SPLMS-PREVIEW-001 to match backend.
		if ( $course_id ) {
			$course = get_post( $course_id );
			if ( $course ) {
				// Create abbreviation from course title.
				$course_title = $course->post_title;
				$words        = explode( ' ', $course_title );
				$abbreviation = '';

				// Take first letter of each word, max 2-3 letters.
				foreach ( $words as $word ) {
					if ( ! empty( $word ) ) {
						$abbreviation .= strtoupper( substr( $word, 0, 1 ) );
						if ( strlen( $abbreviation ) >= 3 ) {
							break;
						}
					}
				}

				// If abbreviation is too short, pad with course name.
				if ( strlen( $abbreviation ) < 2 ) {
					$abbreviation = strtoupper( substr( preg_replace( '/[^a-zA-Z]/', '', $course_title ), 0, 3 ) );
				}

				// If still too short, use default.
				if ( strlen( $abbreviation ) < 2 ) {
					$abbreviation = 'GEN';
				}

				// Generate sequential number (simplified for demo).
				$sequence = str_pad( $course_id % 1000, 3, '0', STR_PAD_LEFT );

				return 'SPLMS-' . $abbreviation . '-' . $sequence;
			}
		}

		return 'SPLMS-PREVIEW-001';
	}

	/**
	 * Get canvas settings from certificate builder data.
	 *
	 * @param int   $certificate_id  Certificate post ID.
	 * @param array $style_overrides Style overrides.
	 *
	 * @since 1.0.0
	 *
	 * @return array Canvas settings with width, height, and background.
	 */
	private static function get_canvas_settings( $certificate_id, $style_overrides = array() ) {
		// Default canvas settings.
		$defaults = array(
			'width'      => 800,
			'height'     => 600,
			'background' => '#ffffff',
		);

		// Get builder data.
		if ( $certificate_id ) {
			$builder_data = get_post_meta( $certificate_id, '_splms_certificate_builder_data', true );
			if ( is_array( $builder_data ) && isset( $builder_data['canvas'] ) ) {
				$canvas                 = $builder_data['canvas'];
				$defaults['width']      = isset( $canvas['width'] ) ? absint( $canvas['width'] ) : $defaults['width'];
				$defaults['height']     = isset( $canvas['height'] ) ? absint( $canvas['height'] ) : $defaults['height'];
				$defaults['background'] = isset( $canvas['background'] ) ? sanitize_hex_color( $canvas['background'] ) : $defaults['background'];
			}
		}

		// Apply style overrides.
		if ( ! empty( $style_overrides['canvas_width'] ) ) {
			$defaults['width'] = absint( $style_overrides['canvas_width'] );
		}
		if ( ! empty( $style_overrides['canvas_height'] ) ) {
			$defaults['height'] = absint( $style_overrides['canvas_height'] );
		}
		if ( ! empty( $style_overrides['background_color'] ) ) {
			$bg_color = sanitize_hex_color( $style_overrides['background_color'] );
			if ( $bg_color ) {
				$defaults['background'] = $bg_color;
			}
		}

		// Ensure valid dimensions.
		$defaults['width']  = max( 200, $defaults['width'] );
		$defaults['height'] = max( 200, $defaults['height'] );

		// Ensure valid background color.
		if ( ! $defaults['background'] || ! preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $defaults['background'] ) ) {
			$defaults['background'] = '#ffffff';
		}

		return $defaults;
	}

	/**
	 * Generate element positioning and styling CSS.
	 *
	 * @param array $element     Element data.
	 * @param array $canvas_data Canvas settings.
	 *
	 * @since 1.0.0
	 *
	 * @return string CSS styles for element.
	 */
	private static function generate_element_styles( $element, $canvas_data ) {
		$styles = array();

		// Positioning - use absolute positioning within container.
		$x      = isset( $element['x'] ) ? floatval( $element['x'] ) : 0;
		$y      = isset( $element['y'] ) ? floatval( $element['y'] ) : 0;
		$width  = isset( $element['width'] ) ? floatval( $element['width'] ) : 100;
		$height = isset( $element['height'] ) ? floatval( $element['height'] ) : 40;

		$styles[] = 'position: absolute';
		$styles[] = "left: {$x}px";
		$styles[] = "top: {$y}px";
		$styles[] = "width: {$width}px";
		$styles[] = "height: {$height}px";

		// Element styling from builder data.
		if ( isset( $element['style'] ) && is_array( $element['style'] ) ) {
			$style = $element['style'];

			// Typography.
			if ( ! empty( $style['fontSize'] ) ) {
				$styles[] = 'font-size: ' . absint( $style['fontSize'] ) . 'px';
			}
			if ( ! empty( $style['fontFamily'] ) ) {
				$styles[] = 'font-family: ' . sanitize_text_field( $style['fontFamily'] );
			}
			if ( ! empty( $style['fontWeight'] ) ) {
				$styles[] = 'font-weight: ' . sanitize_text_field( $style['fontWeight'] );
			}
			if ( ! empty( $style['fontStyle'] ) ) {
				$styles[] = 'font-style: ' . sanitize_text_field( $style['fontStyle'] );
			}
			if ( ! empty( $style['textDecoration'] ) ) {
				$styles[] = 'text-decoration: ' . sanitize_text_field( $style['textDecoration'] );
			}
			if ( ! empty( $style['lineHeight'] ) ) {
				$styles[] = 'line-height: ' . floatval( $style['lineHeight'] );
			}
			if ( ! empty( $style['letterSpacing'] ) ) {
				$styles[] = 'letter-spacing: ' . floatval( $style['letterSpacing'] ) . 'px';
			}

			// Colors.
			if ( ! empty( $style['color'] ) ) {
				$styles[] = 'color: ' . sanitize_hex_color( $style['color'] );
			}
			if ( ! empty( $style['backgroundColor'] ) ) {
				$styles[] = 'background-color: ' . sanitize_hex_color( $style['backgroundColor'] );
			}

			// Text alignment.
			if ( ! empty( $style['textAlign'] ) ) {
				$styles[] = 'text-align: ' . sanitize_text_field( $style['textAlign'] );
			}

			// Borders.
			if ( ! empty( $style['borderColor'] ) ) {
				$styles[] = 'border-color: ' . sanitize_hex_color( $style['borderColor'] );
			}
			if ( ! empty( $style['borderWidth'] ) ) {
				$styles[] = 'border-width: ' . absint( $style['borderWidth'] ) . 'px';
				$styles[] = 'border-style: solid';
			}
			if ( ! empty( $style['borderStyle'] ) ) {
				$styles[] = 'border-style: ' . sanitize_text_field( $style['borderStyle'] );
			}
			if ( ! empty( $style['borderRadius'] ) ) {
				$styles[] = 'border-radius: ' . absint( $style['borderRadius'] ) . 'px';
			}

			// Spacing.
			if ( ! empty( $style['padding'] ) ) {
				$styles[] = 'padding: ' . sanitize_text_field( $style['padding'] );
			}
			if ( ! empty( $style['margin'] ) ) {
				$styles[] = 'margin: ' . sanitize_text_field( $style['margin'] );
			}

			// Layout.
			if ( ! empty( $style['display'] ) ) {
				$styles[] = 'display: ' . sanitize_text_field( $style['display'] );
			}
			if ( ! empty( $style['zIndex'] ) ) {
				$styles[] = 'z-index: ' . absint( $style['zIndex'] );
			}
		}

		// Rotation and opacity.
		if ( isset( $element['rotation'] ) && 0 !== $element['rotation'] ) {
			$rotation = floatval( $element['rotation'] );
			$styles[] = "transform: rotate({$rotation}deg)";
		}
		if ( isset( $element['opacity'] ) && 1 !== $element['opacity'] ) {
			$opacity  = max( 0, min( 1, floatval( $element['opacity'] ) ) );
			$styles[] = "opacity: {$opacity}";
		}

		// Box model.
		$styles[] = 'box-sizing: border-box';
		$styles[] = 'overflow: hidden';

		return implode( '; ', $styles );
	}

	/**
	 * Render text element.
	 *
	 * @param array   $element         Element data.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_text_element( $element, $user, $course, $completion_date, $args ) {
		// Get content from various possible properties.
		$content = '';
		if ( isset( $element['content'] ) ) {
			$content = $element['content'];
		} elseif ( isset( $element['text'] ) ) {
			$content = $element['text'];
		} elseif ( isset( $element['value'] ) ) {
			$content = $element['value'];
		}

		// If no content found, provide default content based on element type.
		if ( empty( $content ) && isset( $element['type'] ) ) {
			$content = self::get_default_content_for_type( $element['type'] );
		}

		// Replace placeholder tokens.
		$content = self::replace_content_tokens( $content, $user, $course, $completion_date, $args );

		// Sanitize content but allow basic HTML.
		$content = wp_kses(
			$content,
			array(
				'br'     => array(),
				'strong' => array(),
				'b'      => array(),
				'em'     => array(),
				'i'      => array(),
				'u'      => array(),
				'span'   => array( 'style' => array() ),
			)
		);

		// Apply element-specific inline styles for proper text layout.
		$inline_styles = array();
		if ( isset( $element['style'] ) && is_array( $element['style'] ) ) {
			$style = $element['style'];

			// Apply text alignment specifically for content wrapper.
			if ( ! empty( $style['textAlign'] ) ) {
				$text_align      = sanitize_text_field( $style['textAlign'] );
				$inline_styles[] = "text-align: {$text_align}";

				// Adjust flex justification based on text alignment.
				$justify_content = 'center';
				if ( 'left' === $text_align ) {
					$justify_content = 'flex-start';
				} elseif ( 'right' === $text_align ) {
					$justify_content = 'flex-end';
				}
				$inline_styles[] = "justify-content: {$justify_content}";
			}
		}

		$style_attr = ! empty( $inline_styles ) ? ' style="' . implode( '; ', $inline_styles ) . '"' : '';

		return sprintf( '<div class="text-content"%s>%s</div>', $style_attr, $content );
	}

	/**
	 * Render image element.
	 *
	 * @param array $element Element data.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_image_element( $element ) {
		$src = isset( $element['src'] ) ? esc_url( $element['src'] ) : '';
		$alt = isset( $element['alt'] ) ? esc_attr( $element['alt'] ) : '';

		if ( empty( $src ) ) {
			return '<div class="image-placeholder">📸</div>';
		}

		return sprintf(
			'<img src="%s" alt="%s" style="width: 100%%; height: 100%%; object-fit: contain;" />',
			$src,
			$alt
		);
	}

	/**
	 * Render header element with title and subtitle support.
	 *
	 * @param array   $element         Element data.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_header_element( $element, $user, $course, $completion_date, $args ) {
		$main_content = isset( $element['content'] ) ? $element['content'] : 'Certificate of Achievement';
		$subtitle     = isset( $element['subtitle'] ) ? $element['subtitle'] : '';

		// Replace tokens. in main content.
		$main_content = self::replace_content_tokens( $main_content, $user, $course, $completion_date, $args );

		$html = '<div class="certificate-header">';

		// Apply main title styling.
		$title_style = '';
		if ( isset( $element['style'] ) ) {
			$style = $element['style'];
			if ( isset( $style['fontSize'] ) ) {
				$title_style .= 'font-size: ' . intval( $style['fontSize'] ) . 'px; ';
			}
			if ( isset( $style['fontFamily'] ) ) {
				$title_style .= 'font-family: "' . esc_attr( $style['fontFamily'] ) . '"; ';
			}
			if ( isset( $style['fontWeight'] ) ) {
				$title_style .= 'font-weight: ' . esc_attr( $style['fontWeight'] ) . '; ';
			}
			if ( isset( $style['color'] ) ) {
				$title_style .= 'color: ' . esc_attr( $style['color'] ) . '; ';
			}
			if ( isset( $style['letterSpacing'] ) ) {
				$title_style .= 'letter-spacing: ' . floatval( $style['letterSpacing'] ) . 'px; ';
			}
		}

		$html .= '<div class="header-title"' . ( $title_style ? ' style="' . esc_attr( $title_style ) . '"' : '' ) . '>' . esc_html( $main_content ) . '</div>';

		if ( ! empty( $subtitle ) ) {
			$processed_subtitle = self::replace_content_tokens( $subtitle, $user, $course, $completion_date, $args );

			// Apply subtitle styling.
			$subtitle_style = '';
			if ( isset( $element['subtitleStyle'] ) ) {
				$style = $element['subtitleStyle'];
				if ( isset( $style['fontSize'] ) ) {
					$subtitle_style .= 'font-size: ' . intval( $style['fontSize'] ) . 'px; ';
				}
				if ( isset( $style['fontFamily'] ) ) {
					$subtitle_style .= 'font-family: "' . esc_attr( $style['fontFamily'] ) . '"; ';
				}
				if ( isset( $style['fontWeight'] ) ) {
					$subtitle_style .= 'font-weight: ' . esc_attr( $style['fontWeight'] ) . '; ';
				}
				if ( isset( $style['color'] ) ) {
					$subtitle_style .= 'color: ' . esc_attr( $style['color'] ) . '; ';
				}
				if ( isset( $style['fontStyle'] ) ) {
					$subtitle_style .= 'font-style: ' . esc_attr( $style['fontStyle'] ) . '; ';
				}
			}

			$html .= '<div class="header-subtitle"' . ( $subtitle_style ? ' style="' . esc_attr( $subtitle_style ) . '"' : '' ) . '>' . esc_html( $processed_subtitle ) . '</div>';
		}

		$html .= '</div>'; // Close certificate-header.

		return $html;
	}

	/**
	 * Render recipient element with placeholder support.
	 *
	 * @param array   $element         Element data.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_recipient_element( $element, $user, $course, $completion_date, $args ) {
		$placeholder = isset( $element['placeholder'] ) ? $element['placeholder'] : 'student_name';
		$prefix      = isset( $element['prefix'] ) ? $element['prefix'] : '';
		$suffix      = isset( $element['suffix'] ) ? $element['suffix'] : '';

		// Get the actual student name value.
		$student_name_token = '{' . $placeholder . '}';
		$student_name       = self::replace_content_tokens( $student_name_token, $user, $course, $completion_date, $args );

		// Build content with bold student name.
		$content_parts = array();

		if ( ! empty( $prefix ) ) {
			$content_parts[] = esc_html( $prefix );
		}

		$content_parts[] = '&nbsp;<strong>' . esc_html( $student_name ) . '</strong>';

		if ( ! empty( $suffix ) ) {
			$content_parts[] = esc_html( $suffix );
		}

		$content = implode( ' ', $content_parts );

		return '<div class="recipient-content">' . $content . '</div>';
	}

	/**
	 * Render course details element with field configuration.
	 *
	 * @param array   $element         Element data.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_course_details_element( $element, $user, $course, $completion_date, $args ) {
		$show_fields   = isset( $element['showFields'] ) ? $element['showFields'] : array( 'courseTitle', 'completionDate', 'certificateId' );
		$fields_config = isset( $element['fields'] ) ? $element['fields'] : array();

		// Define default field configurations to match React ElementRenderer.
		$default_fields = array(
			'courseTitle'    => array(
				'placeholder' => 'course_title',
				'prefix'      => 'has successfully completed',
				'suffix'      => '',
			),
			'completionDate' => array(
				'placeholder' => 'completion_date',
				'prefix'      => 'on',
				'suffix'      => '',
			),
			'certificateId'  => array(
				'placeholder' => 'certificate_id',
				'prefix'      => 'Certificate ID:',
				'suffix'      => '',
			),
			'duration'       => array(
				'placeholder' => 'duration',
				'prefix'      => 'completing',
				'suffix'      => 'of instruction',
			),
			'siteName'       => array(
				'placeholder' => 'site_name',
				'prefix'      => 'from',
				'suffix'      => '',
			),
		);

		$details = array();
		foreach ( $show_fields as $field ) {
			// Merge element config with defaults.
			$default_config = isset( $default_fields[ $field ] ) ? $default_fields[ $field ] : array();
			$field_config   = isset( $fields_config[ $field ] ) ? $fields_config[ $field ] : array();
			$merged_config  = array_merge( $default_config, $field_config );

			$prefix      = isset( $merged_config['prefix'] ) ? $merged_config['prefix'] : '';
			$suffix      = isset( $merged_config['suffix'] ) ? $merged_config['suffix'] : '';
			$placeholder = isset( $merged_config['placeholder'] ) ? $merged_config['placeholder'] : $field;

			$field_content = '';
			if ( ! empty( $prefix ) ) {
				$field_content .= $prefix . ' ';
			}
			$field_content .= '{' . $placeholder . '}';
			if ( ! empty( $suffix ) ) {
				$field_content .= ' ' . $suffix;
			}

			// Replace tokens. for this field.
			$field_content = self::replace_content_tokens( $field_content, $user, $course, $completion_date, $args );
			$details[]     = $field_content;
		}

		$content = implode( '<br>', $details );

		return '<div class="course-details-content">' . $content . '</div>';
	}

	/**
	 * Render signature element.
	 *
	 * @param array   $element         Element data.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_signature_element( $element, $user, $course, $completion_date, $args ) {
		// Get signature properties, checking various possible keys.
		$signature_name = '';
		if ( isset( $element['signatureName'] ) ) {
			$signature_name = $element['signatureName'];
		} elseif ( isset( $element['content'] ) ) {
			$signature_name = $element['content'];
		} elseif ( isset( $element['text'] ) ) {
			$signature_name = $element['text'];
		}

		// If still empty, use simple default to match backend.
		if ( empty( $signature_name ) ) {
			$signature_name = 'Signature';
		}

		$signature_title = isset( $element['signatureTitle'] ) ? $element['signatureTitle'] : '';
		$show_line       = isset( $element['showLine'] ) ? $element['showLine'] : true;

		// Replace tokens. in signature name and title.
		$signature_name  = self::replace_content_tokens( $signature_name, $user, $course, $completion_date, $args );
		$signature_title = self::replace_content_tokens( $signature_title, $user, $course, $completion_date, $args );

		$html = '<div class="signature-placeholder">';

		if ( $show_line ) {
			$html .= '<div class="signature-line"></div>';
		}

		if ( ! empty( $signature_name ) ) {
			$html .= '<div class="signature-name">' . esc_html( $signature_name ) . '</div>';
		}

		if ( ! empty( $signature_title ) ) {
			$html .= '<div class="signature-title">' . esc_html( $signature_title ) . '</div>';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Render logo element.
	 *
	 * @param array $element Element data.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_logo_element( $element ) {
		$src = isset( $element['src'] ) ? esc_url( $element['src'] ) : '';
		$alt = isset( $element['alt'] ) ? esc_attr( $element['alt'] ) : __( 'Logo', 'skillpulse-lms' );

		if ( empty( $src ) ) {
			return '<div class="logo-placeholder">🏢</div>';
		}

		return sprintf(
			'<img src="%s" alt="%s" style="width: 100%%; height: 100%%; object-fit: contain;" />',
			$src,
			$alt
		);
	}

	/**
	 * Render QR code element.
	 *
	 * @param array   $element Element data.
	 * @param WP_User $user    User object.
	 * @param WP_Post $course  Course object.
	 * @param array   $args    Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_qr_element( $element, $user, $course, $args ) {
		// Generate verification URL for QR code.
		$verification_url = home_url( '/certificate-verify/' );
		if ( ! empty( $args['certificate_key'] ) ) {
			$verification_url .= '?key=' . rawurlencode( $args['certificate_key'] );
		}

		return sprintf(
			'<div class="qr-placeholder" title="%s">📱 QR</div>',
			esc_attr( $verification_url )
		);
	}

	/**
	 * Render shape element.
	 *
	 * @param array $element Element data.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_shape_element( $element ) {
		$shape_type   = isset( $element['shapeType'] ) ? $element['shapeType'] : 'line';
		$element_type = isset( $element['type'] ) ? $element['type'] : '';

		// Special handling for seal elements.
		if ( 'seal' === $element_type ) {
			$src = isset( $element['src'] ) ? $element['src'] : '';
			$alt = isset( $element['alt'] ) ? $element['alt'] : 'Official Seal';

			if ( ! empty( $src ) ) {
				// Render actual seal image.
				return sprintf(
					'<img src="%s" alt="%s" style="width: 100%%; height: 100%%; object-fit: contain; border-radius: 50%%; border: 2px solid #2c5aa0;" />',
					esc_url( $src ),
					esc_attr( $alt )
				);
			} else {
				// Show placeholder matching React ElementRenderer.
				return '<div style="width: 100%; height: 100%; background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; border: 2px solid #2c5aa0; border-radius: 50%; font-size: 12px; color: #6c757d; flex-direction: column; gap: 4px;">
					<div style="font-size: 18px;">🏆</div>
					<div>Seal</div>
				</div>';
			}
		}

		// Handle decorative line elements.
		if ( 'line' === $shape_type ) {
			$stroke_width = isset( $element['style']['strokeWidth'] ) ? intval( $element['style']['strokeWidth'] ) : 2;
			$stroke_style = isset( $element['style']['strokeStyle'] ) ? $element['style']['strokeStyle'] : 'solid';
			$stroke_color = isset( $element['style']['color'] ) ? $element['style']['color'] : '#000000';
			$width        = isset( $element['width'] ) ? floatval( $element['width'] ) : 200;
			$height       = isset( $element['height'] ) ? floatval( $element['height'] ) : 2;

			$is_horizontal = $width > $height;

			$line_style = '';
			if ( $is_horizontal ) {
				$line_style = sprintf(
					'width: 100%%; height: %dpx; background-color: %s;',
					$stroke_width,
					esc_attr( $stroke_color )
				);
				if ( 'solid' !== $stroke_style ) {
					$line_style = sprintf(
						'width: 100%%; height: 0; border-top: %dpx %s %s;',
						$stroke_width,
						esc_attr( $stroke_style ),
						esc_attr( $stroke_color )
					);
				}
			} else {
				$line_style = sprintf(
					'width: %dpx; height: 100%%; background-color: %s;',
					$stroke_width,
					esc_attr( $stroke_color )
				);
				if ( 'solid' !== $stroke_style ) {
					$line_style = sprintf(
						'width: 0; height: 100%%; border-left: %dpx %s %s;',
						$stroke_width,
						esc_attr( $stroke_style ),
						esc_attr( $stroke_color )
					);
				}
			}

			return sprintf(
				'<div class="decorative-line-container" style="width: 100%%; height: 100%%; display: flex; align-items: center; justify-content: center;">
					<div class="decorative-line" style="%s"></div>
				</div>',
				esc_attr( $line_style )
			);
		}

		// Handle other shape types (rectangle, etc.).
		$border_width  = isset( $element['style']['strokeWidth'] ) ? intval( $element['style']['strokeWidth'] ) : 2;
		$border_style  = isset( $element['style']['strokeStyle'] ) ? $element['style']['strokeStyle'] : 'solid';
		$border_color  = isset( $element['style']['color'] ) ? $element['style']['color'] : '#000000';
		$fill_color    = isset( $element['style']['fillColor'] ) ? $element['style']['fillColor'] : 'transparent';
		$border_radius = ( 'rounded-rectangle' === $shape_type ) ? '8px' : '0';

		$shape_style = sprintf(
			'width: 100%%; height: 100%%; border: %dpx %s %s; border-radius: %s; background-color: %s;',
			$border_width,
			esc_attr( $border_style ),
			esc_attr( $border_color ),
			esc_attr( $border_radius ),
			esc_attr( $fill_color )
		);

		return sprintf( '<div class="shape-element" style="%s"></div>', esc_attr( $shape_style ) );
	}

	/**
	 * Render generic element (fallback).
	 *
	 * @param array $element Element data.
	 *
	 * @since 1.0.0
	 *
	 * @return string Element HTML.
	 */
	private static function render_generic_element( $element ) {
		// Try to get actual content first.
		$content = '';

		// Check for various content property names that might be used.
		if ( isset( $element['content'] ) ) {
			$content = $element['content'];
		} elseif ( isset( $element['text'] ) ) {
			$content = $element['text'];
		} elseif ( isset( $element['value'] ) ) {
			$content = $element['value'];
		} elseif ( isset( $element['label'] ) ) {
			$content = $element['label'];
		}

		// If no content found, show element type for debugging.
		if ( empty( $content ) ) {
			$type    = isset( $element['type'] ) ? esc_html( $element['type'] ) : 'element';
			$content = sprintf( '[%s - no content]', $type );
		}

		return sprintf( '<div class="generic-element">%s</div>', esc_html( $content ) );
	}

	/**
	 * Get default content for element type.
	 *
	 * @param string $type Element type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Default content.
	 */
	private static function get_default_content_for_type( $type ) {
		$defaults = array(
			'header'         => __( 'Certificate of Completion', 'skillpulse-lms' ),
			'recipient'      => '{user_name}',
			'description'    => __(
				'This certificate recognizes the successful completion of comprehensive training and demonstrates mastery of essential skills and knowledge.',
				'skillpulse-lms'
			),
			'course_details' => __( 'Course: {course_title}<br>Completed on {completion_date}<br>Certificate ID: {certificate_id}', 'skillpulse-lms' ),
			'text'           => __( 'Sample Text', 'skillpulse-lms' ),
		);

		return isset( $defaults[ $type ] ) ? $defaults[ $type ] : ucfirst( str_replace( '_', ' ', $type ) );
	}

	/**
	 * Replace content tokens with dynamic values.
	 *
	 * @param string  $content         Content with tokens.
	 * @param WP_User $user            User object.
	 * @param WP_Post $course          Course object.
	 * @param string  $completion_date Completion date.
	 * @param array   $args            Generation arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Content with tokens replaced.
	 */
	private static function replace_content_tokens( $content, $user, $course, $completion_date, $args ) {
		if ( empty( $content ) || ! is_string( $content ) ) {
			return $content;
		}

		// Generate user display name with proper fallback.
		$user_name = 'Student Name';
		if ( $user ) {
			if ( ! empty( $user->display_name ) && 'admin' !== $user->display_name ) {
				$user_name = $user->display_name;
			} else {
				// Construct from first/last name or use user_login.
				$first_name = ! empty( $user->first_name ) ? $user->first_name : '';
				$last_name  = ! empty( $user->last_name ) ? $user->last_name : '';

				if ( $first_name || $last_name ) {
					$user_name = trim( $first_name . ' ' . $last_name );
				} else {
					$user_name = $user->user_login;
				}
			}
		}

		// Token replacements.
		$tokens = array(
			'{user_name}'       => $user_name,
			'{student_name}'    => $user_name,
			'{user_first_name}' => $user && $user->first_name ? $user->first_name : 'First',
			'{user_firstname}'  => $user && $user->first_name ? $user->first_name : 'First',
			'{user_last_name}'  => $user && $user->last_name ? $user->last_name : 'Last',
			'{user_lastname}'   => $user && $user->last_name ? $user->last_name : 'Last',
			'{course_title}'    => $course ? $course->post_title : 'Course Title',
			'{courseTitle}'     => $course ? $course->post_title : 'Course Title',
			'{completion_date}' => $completion_date ? wp_date( 'F j, Y', strtotime( $completion_date ) ) : wp_date( 'F j, Y' ),
			'{completionDate}'  => $completion_date ? wp_date( 'F j, Y', strtotime( $completion_date ) ) : wp_date( 'F j, Y' ),
			'{certificate_id}'  => self::generate_simple_certificate_id( $args['course_id'] ?? 0 ),
			'{certificateId}'   => self::generate_simple_certificate_id( $args['course_id'] ?? 0 ),
			'{site_name}'       => get_bloginfo( 'name' ),
			'{siteName}'        => get_bloginfo( 'name' ),
			'{site_url}'        => home_url(),
			'{instructor_name}' => 'Course Instructor',
			'{grade}'           => '95%',
			'{duration}'        => '40 hours',
		);

		// Apply filters to allow customization.
		$tokens = apply_filters( 'splms_certificate_tokens', $tokens, $user, $course, $completion_date, $args );

		// Replace tokens.
		return str_replace( array_keys( $tokens ), array_values( $tokens ), $content );
	}

	/**
	 * Generate error HTML
	 *
	 * @param string $message Error message.
	 *
	 * @since 1.0.0
	 *
	 * @return string Error HTML.
	 */
	private static function generate_error_html( $message ) {
		return '<div class="splms-certificate-error" style="text-align: center; padding: 2rem; color: #dc3545; border: 1px solid #dc3545; background: #f8d7da; border-radius: 5px;"><p>' . esc_html( $message ) . '</p></div>';
	}
}