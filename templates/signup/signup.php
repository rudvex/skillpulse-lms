<?php
/**
 * Main Signup Template
 * This is the template that includes the appropriate sub-template
 *
 * @package SPLMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



global $splms_signup;

$splms_step = SPLMS_Signup_Screen_Handler::get_current_signup_step();

wp_enqueue_style( 'splms-frontend-style' );
wp_enqueue_script( 'splms-frontend-script' );
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>
		<?php
		switch ( $splms_step ) {
			case 'signup-success':
				echo esc_html( sprintf( '%s - %s', __( 'Registration Successful', 'skillpulse-lms' ), get_bloginfo( 'name' ) ) );
				break;
			case 'activation':
				echo esc_html( sprintf( '%s - %s', __( 'Activate Your Account', 'skillpulse-lms' ), get_bloginfo( 'name' ) ) );
				break;
			default:
				echo esc_html( sprintf( '%s - %s', __( 'Create an Account', 'skillpulse-lms' ), get_bloginfo( 'name' ) ) );
				break;
		}
		?>
	</title>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'splms-signup-page' ); ?>>
<?php
/**
 * Fires before the signup content.
 */
do_action( 'splms_before_signup_content' );
?>
<div id="splms-signup-page" class="splms-signup-page">
	<?php
	/**
	 * Fires at the start of the signup page.
	 */
	do_action( 'splms_signup_page_start' );

	// Include the appropriate template based on current step.
	switch ( $splms_step ) {
		case 'signup-success':
			// Allow theme override: yourtheme/skillpulse-lms/signup/signup-success.php.
			$splms_registration_success_template = splms_locate_template( 'signup/signup-success.php' );
			if ( $splms_registration_success_template && file_exists( $splms_registration_success_template ) ) {
				include $splms_registration_success_template;
			}
			break;
		case 'activation':
			// Allow theme override: yourtheme/skillpulse-lms/signup/activation-form.php.
			$splms_activation_template = splms_locate_template( 'signup/activation-form.php' );
			if ( $splms_activation_template && file_exists( $splms_activation_template ) ) {
				include $splms_activation_template;
			}
			break;
		default:
			// Allow theme override: yourtheme/skillpulse-lms/signup/register-form.php.
			$splms_register_template = splms_locate_template( 'signup/register-form.php' );
			if ( $splms_register_template && file_exists( $splms_register_template ) ) {
				include $splms_register_template;
			}
			break;
	}

	/**
	 * Fires at the end of the signup page.
	 */
	do_action( 'splms_signup_page_end' );
	?>

</div>

<?php
/**
 * Fires after the signup content.
 */
do_action( 'splms_after_signup_content' );
?>

<?php wp_footer(); ?>
</body>
</html>
