<?php
/**
 * Signup Admin
 *
 * Manages the admin interface for pending user signups.
 * Handles signup approval, activation emails, and signup management.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * SkillPulse LMS Signup Admin Class
 *
 * Manages the admin interface for pending user signups.
 * Handles signup approval, activation emails, and signup management.
 *
 * @since 1.0.0
 */
class SPLMS_Signup_Admin {

	/**
	 * Class instance.
	 *
	 * @since 1.0.0
	 * @var SPLMS_Signup_Admin|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Signup_Admin The singleton instance.
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
			self::$instance->setup_filters();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * Setup action hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		// Hook into the main Users admin page.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Integrate with main Users page.
		add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

		// Add modal templates and scripts for signup admin page.
		add_action( 'admin_footer', array( $this, 'render_signup_modal_templates' ) );

		// AJAX handlers for signup modal.
		add_action( 'wp_ajax_splms_get_signup_details', array( $this, 'ajax_get_signup_details' ) );
		add_action( 'wp_ajax_splms_process_signup', array( $this, 'ajax_process_signup' ) );
	}

	/**
	 * Setup filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function setup_filters() {
		add_filter( 'views_users', array( $this, 'add_pending_signups_tab' ) );
	}

	/**
	 * Add admin menu page for pending signups.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		// Add the signups page to Users menu.
		$hook = add_submenu_page(
			'users.php',
			__( 'Pending Signups', 'skillpulse-lms' ),
			__( 'Pending Signups', 'skillpulse-lms' ),
			'manage_options',
			'splms-signups',
			array( $this, 'signups_admin' )
		);

		// Setup the signups page when it loads.
		add_action( "load-{$hook}", array( $this, 'signups_admin_load' ) );
	}

	/**
	 * Set up the signups admin page.
	 *
	 * @since 1.0.0
	 */
	public function signups_admin_load() {
		global $role, $splms_signup_list_table;

		// Set role to 'registered'.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required for WP_Users_List_Table compatibility.
		$role = 'registered';

		// Handle bulk actions first.
		$this->handle_signups_bulk_actions();

		// Load the list table.
		require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
		require_once SPLMS_DIR_PATH . 'includes/admin/signup/class-signup-list-table.php';

		$splms_signup_list_table = new SPLMS_Signup_List_Table();

		// Prepare the list table.
		$splms_signup_list_table->prepare_items();

		// Add screen options.
		add_screen_option( 'per_page', array( 'label' => __( 'Pending Accounts', 'skillpulse-lms' ) ) );

		// Add help tabs.
		$this->add_signups_help_tabs();

		add_filter( 'splms_admin_localize_data', array( $this, 'admin_localize_data' ) );
	}

	/**
	 * Render the signups admin page.
	 *
	 * @since 1.0.0
	 */
	public function signups_admin() {
		global $splms_signup_list_table, $splms_usersearch; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required for WP_Users_List_Table compatibility.

		$request_s        = isset( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$splms_usersearch = sanitize_text_field( trim( $request_s ) );
		$plugin_page      = 'splms-signups';
		$search_form_url  = add_query_arg( 'page', $plugin_page, admin_url( 'users.php' ) );
		$form_url         = add_query_arg( 'page', $plugin_page, admin_url( 'users.php' ) );
		?>
		<div class="wrap">
			<?php if ( version_compare( $GLOBALS['wp_version'], '4.8', '>=' ) ) { ?>
				<h1 class="wp-heading-inline"><?php esc_html_e( 'Users', 'skillpulse-lms' ); ?></h1>
				<?php
				if ( $splms_usersearch ) {
					/* translators: %s: Search query. */
					printf( '<span class="subtitle">' . esc_html__( 'Search results for "%s"', 'skillpulse-lms' ) . '</span>', esc_html( $splms_usersearch ) );
				}
				?>
				<hr class="wp-header-end">
			<?php } else { ?>
				<h1><?php esc_html_e( 'Users', 'skillpulse-lms' ); ?>
					<?php
					if ( $splms_usersearch ) {
						/* translators: %s: Search query. */
						printf( '<span class="subtitle">' . esc_html__( 'Search results for "%s"', 'skillpulse-lms' ) . '</span>', esc_html( $splms_usersearch ) );
					}
					?>
				</h1>
			<?php } ?>

			<?php settings_errors( 'splms-signup' ); ?>

			<?php // Display the views (tabs). ?>
			<?php $splms_signup_list_table->views(); ?>

			<form id="splms-signups-search-form" action="<?php echo esc_url( $search_form_url ); ?>">
				<input type="hidden" name="page" value="<?php echo esc_attr( $plugin_page ); ?>"/>
				<?php $splms_signup_list_table->search_box( __( 'Search Pending Users', 'skillpulse-lms' ), 'splms-signups' ); ?>
			</form>

			<form id="splms-signups-form" action="<?php echo esc_url( $form_url ); ?>" method="post">
				<?php $splms_signup_list_table->display(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add pending signups tab to users page.
	 *
	 * @param array $views Array of view links.
	 *
	 * @since 1.0.0
	 *
	 * @return array Modified views array.
	 */
	public function add_pending_signups_tab( $views ) {
		global $role;

		// Get pending signups count.
		$signup_count = SPLMS_Signup_Query::get_instance()->get_pending_signups_count();

		// Remove 'current' class from All if we're on pending signups.
		if ( 'registered' === $role ) {
			$views['all'] = str_replace( 'class="current"', '', $views['all'] );
			$class        = 'current';
		} else {
			$class = '';
		}

		// Add pending signups tab.
		$url = add_query_arg( 'page', 'splms-signups', admin_url( 'users.php' ) );
		/* translators: %s: Number of pending signups. */
		$text = sprintf(
		/* translators: %s: Number of pending signups. */
			__( 'Pending %s', 'skillpulse-lms' ),
			'<span class="count">(' . number_format_i18n( $signup_count ) . ')</span>'
		);

		$views['registered'] = sprintf(
			'<a href="%1$s" class="%2$s">%3$s</a>',
			esc_url( $url ),
			$class,
			$text
		);

		return $views;
	}


	/**
	 * Display admin notices for signup actions.
	 *
	 * @since 1.0.0
	 */
	public function display_admin_notices() {
		// Individual action notices.
		if ( isset( $_GET['activated'] ) && '1' === $_GET['activated'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'Signup activated successfully.', 'skillpulse-lms' )
			);
		}

		if ( isset( $_GET['resent'] ) && '1' === $_GET['resent'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'Activation email sent successfully.', 'skillpulse-lms' )
			);
		}

		if ( isset( $_GET['deleted'] ) && '1' === $_GET['deleted'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html__( 'Signup deleted successfully.', 'skillpulse-lms' )
			);
		}

		// Error notices.
		if ( isset( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$error_messages = array(
				'activation_failed'        => __( 'Failed to activate signup.', 'skillpulse-lms' ),
				'resend_failed'            => __( 'Failed to send activation email.', 'skillpulse-lms' ),
				'delete_failed'            => __( 'Failed to delete signup.', 'skillpulse-lms' ),
				'signup_not_found'         => __( 'Signup not found.', 'skillpulse-lms' ),
				'insufficient_permissions' => __( 'Insufficient permissions.', 'skillpulse-lms' ),
			);

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for displaying error messages.
			$error = sanitize_text_field( wp_unslash( $_GET['error'] ) );
			if ( isset( $error_messages[ $error ] ) ) {
				printf(
					'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
					esc_html( $error_messages[ $error ] )
				);
			}
		}

		// Bulk action notices.
		if ( isset( $_GET['bulk_activated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_GET['bulk_activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
					/* translators: %d: Number of signups. */
						_n( '%d signup activated successfully.', '%d signups activated successfully.', $count, 'skillpulse-lms' ),
						$count
					)
				)
			);
		}

		if ( isset( $_GET['bulk_deleted'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_GET['bulk_deleted'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
					/* translators: %d: Number of signups. */
						_n( '%d signup deleted successfully.', '%d signups deleted successfully.', $count, 'skillpulse-lms' ),
						$count
					)
				)
			);
		}

		if ( isset( $_GET['bulk_resent'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$count = intval( $_GET['bulk_resent'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
					/* translators: %d: Number of emails. */
						_n( '%d activation email sent successfully.', '%d activation emails sent successfully.', $count, 'skillpulse-lms' ),
						$count
					)
				)
			);
		}
	}

	/**
	 * Handle signups bulk actions.
	 *
	 * @since 1.0.0
	 */
	private function handle_signups_bulk_actions() {
		$action = $this->current_action();

		if ( ! $action ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'skillpulse-lms' ) );
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bulk-signups' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$signup_ids = isset( $_POST['signup_ids'] ) ? array_map( 'absint', $_POST['signup_ids'] ) : array();

		if ( empty( $signup_ids ) ) {
			return;
		}

		$signup = SPLMS_Signup::get_instance();

		switch ( $action ) {
			case 'activate':
				$activated = 0;
				foreach ( $signup_ids as $signup_id ) {
					$signup_item = $signup->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = $signup->activate_signup( $signup_item->activation_key );
						if ( ! is_wp_error( $result ) ) {
							++$activated;
						}
					}
				}
				wp_safe_redirect( add_query_arg( 'activated', '1', wp_get_referer() ) );
				exit;

			case 'delete':
				$deleted = 0;
				foreach ( $signup_ids as $signup_id ) {
					$result = $signup->delete_signup( $signup_id );
					if ( $result ) {
						++$deleted;
					}
				}
				wp_safe_redirect( add_query_arg( 'deleted', '1', wp_get_referer() ) );
				exit;

			case 'resend':
				$resent = 0;
				foreach ( $signup_ids as $signup_id ) {
					$signup_item = $signup->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = SPLMS_Email_Module::get_instance()->send_activation_email( $signup_item->id );
						if ( $result ) {
							++$resent;
						}
					}
				}
				wp_safe_redirect( add_query_arg( 'resent', '1', wp_get_referer() ) );
				exit;
		}
	}

	/**
	 * Get current action.
	 *
	 * @since 1.0.0
	 *
	 * @return string|false Action name or false.
	 */
	private function current_action() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification handled in process_bulk_action method.
		if ( isset( $_REQUEST['action'] ) && '-1' !== $_REQUEST['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Already verified in condition above.
			return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification handled in process_bulk_action method.
		if ( isset( $_REQUEST['action2'] ) && '-1' !== $_REQUEST['action2'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Already verified in condition above.
			return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
		}

		return false;
	}

	/**
	 * Add help tabs for signups page.
	 *
	 * @since 1.0.0
	 */
	private function add_signups_help_tabs() {
		$screen = get_current_screen();

		$screen->add_help_tab(
			array(
				'id'      => 'splms-signups-overview',
				'title'   => __( 'Overview', 'skillpulse-lms' ),
				'content' => '<p>' . __( 'This page shows all pending user signups that require email verification before they can log in.', 'skillpulse-lms' ) . '</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'splms-signups-actions',
				'title'   => __( 'Actions', 'skillpulse-lms' ),
				'content' => '<p>' . __(
					'You can activate, delete, or resend activation emails for individual signups or use bulk actions for multiple signups.',
					'skillpulse-lms'
				) . '</p>',
			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __(
				'For more information:',
				'skillpulse-lms'
			) . '</strong></p><p>' . __(
				'Visit the plugin documentation for detailed instructions.',
				'skillpulse-lms'
			) . '</p>'
		);
	}

	/**
	 * Render modal templates and scripts for signup review.
	 * Only renders on the signup admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_signup_modal_templates() {
		// Only render on signup admin page.
		if ( ! isset( $_GET['page'] ) || 'splms-signups' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		?>
		<!-- WordPress Template for Admin Signup Review Modal -->
		<script type="text/html" id="tmpl-splms-admin-signup-review">
			<div class="splms-admin-modal splms-signup-review-modal" style="display: none;">
				<div class="modal-overlay"></div>
				<div class="modal-content">
					<# if ( data.loading ) { #>
					<div class="modal-header">
						<h2><?php esc_html_e( 'Review Signup', 'skillpulse-lms' ); ?></h2>
						<button class="modal-close splms-modal-close" aria-label="<?php esc_attr_e( 'Close', 'skillpulse-lms' ); ?>">&times;</button>
					</div>
					<div class="modal-body">
						<div class="loading-content">
							<div class="spinner is-active"></div>
							<p><?php esc_html_e( 'Loading signup details...', 'skillpulse-lms' ); ?></p>
						</div>
					</div>
					<# } else { #>
					<div class="modal-header">
						<h2><?php esc_html_e( 'Review Signup', 'skillpulse-lms' ); ?></h2>
						<button class="modal-close splms-modal-close" aria-label="<?php esc_attr_e( 'Close', 'skillpulse-lms' ); ?>">&times;</button>
					</div>
					<div class="modal-body">
						<div class="signup-details">
							<div class="signup-info">
								<div class="signup-data">
									<h3>{{ data.display_name || data.user_login }}</h3>
									<p class="email">{{ data.user_email }}</p>
									<p class="username">@{{ data.user_login }}</p>
								</div>
							</div>

							<div class="signup-form-data">
								<div class="data-section">
									<h4><?php esc_html_e( 'Signup Date', 'skillpulse-lms' ); ?></h4>
									<p class="date-value">{{ data.registered_formatted }}</p>
								</div>

								<# if ( data.activation_key ) { #>
								<div class="data-section">
									<h4><?php esc_html_e( 'Activation Key', 'skillpulse-lms' ); ?></h4>
									<p class="activation-key"><code>{{ data.activation_key }}</code></p>
								</div>
								<# } #>

								<# if ( data.meta && Object.keys(data.meta).length > 0 ) { #>
								<div class="data-section">
									<h4><?php esc_html_e( 'Additional Information', 'skillpulse-lms' ); ?></h4>
									<div class="meta-content">
										<# for ( var key in data.meta ) { #>
										<p><strong>{{ key }}:</strong> {{ data.meta[key] }}</p>
										<# } #>
									</div>
								</div>
								<# } #>
							</div>
						</div>

						<div class="review-actions">
							<!-- Action Buttons -->
							<div class="action-section">
								<!-- Primary Action -->
								<div class="activate-section">
									<button type="button" class="button activate-signup-btn">
										<span class="dashicons dashicons-yes"></span>
										<?php esc_html_e( 'Activate Signup', 'skillpulse-lms' ); ?>
									</button>
									<p class="action-description"><?php esc_html_e( 'Convert this signup into an active user account.', 'skillpulse-lms' ); ?></p>
								</div>

								<!-- Secondary Actions -->
								<div class="secondary-actions">
									<div class="resend-section">
										<button type="button" class="button resend-activation-btn">
											<span class="dashicons dashicons-email"></span>
											<?php esc_html_e( 'Resend Email', 'skillpulse-lms' ); ?>
										</button>
										<p class="action-description"><?php esc_html_e( 'Send activation email again', 'skillpulse-lms' ); ?></p>
									</div>

									<div class="delete-section">
										<button type="button" class="button delete-signup-btn">
											<span class="dashicons dashicons-trash"></span>
											<?php esc_html_e( 'Delete Signup', 'skillpulse-lms' ); ?>
										</button>
										<p class="action-description"><?php esc_html_e( 'Remove permanently', 'skillpulse-lms' ); ?></p>
									</div>
								</div>
							</div>
						</div>
					</div>
					<# } #>
				</div>
			</div>
		</script>

		<?php
	}


	/**
	 * Add localization data for JavaScript.
	 *
	 * @param array $localize_data The localization data to merge.
	 *
	 * @since 1.0.0
	 *
	 * @return array Modified localization data.
	 */
	public function admin_localize_data( $localize_data ) {
		$signup_admin = array(
			'is_signup_admin_page' => true,
			'nonces'               => array(
				'review'  => wp_create_nonce( 'splms_signup_review' ),
				'process' => wp_create_nonce( 'splms_signup_process' ),
			),
			'strings'              => array(
				'quick_activate_confirm' => __( 'Activate signup for', 'skillpulse-lms' ),
				'quick_delete_confirm'   => __( 'Delete signup for', 'skillpulse-lms' ),
				'quick_resend_confirm'   => __( 'Resend activation email to', 'skillpulse-lms' ),
			),
		);

		$localize_data = array_merge( $localize_data, $signup_admin );

		return $localize_data;
	}

	/**
	 * AJAX handler for getting signup details.
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_signup_details() {
		// Verify nonce (using $_REQUEST to handle both GET and POST).
		$nonce = isset( $_REQUEST['nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! wp_verify_nonce( $nonce, 'splms_signup_review' ) ) {
			wp_die( esc_html__( 'Security check failed', 'skillpulse-lms' ) );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'skillpulse-lms' ) );
		}

		$signup_id = isset( $_REQUEST['signup_id'] ) ? intval( $_REQUEST['signup_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $signup_id ) {
			wp_send_json_error( esc_html__( 'Invalid signup ID', 'skillpulse-lms' ) );
		}

		// Get signup data.
		$signup = SPLMS_Signup::get_instance()->get_signup( $signup_id );
		if ( ! $signup ) {
			wp_send_json_error( esc_html__( 'Signup not found', 'skillpulse-lms' ) );
		}

		// Prepare response data (excluding sensitive information).
		$meta      = maybe_unserialize( $signup->meta );
		$safe_meta = array();

		// Only include non-sensitive meta fields for display.
		if ( is_array( $meta ) ) {
			$allowed_meta_keys = array(
				'first_name',
				'last_name',
				'display_name',
				'description',
				'website',
				'user_url',
				// Add other safe meta keys as needed, but exclude sensitive data.
			);

			foreach ( $allowed_meta_keys as $key ) {
				if ( isset( $meta[ $key ] ) ) {
					$safe_meta[ $key ] = sanitize_text_field( $meta[ $key ] );
				}
			}
		}

		$response_data = array(
			'signup_id'    => $signup->id,
			'user_login'   => $signup->user_login,
			'user_email'   => $signup->user_email,
			'display_name' => ! empty( $signup->user_name ) ? $signup->user_name : $signup->user_login,
			'registered'   => $signup->registered,
			'meta'         => $safe_meta,
		);

		wp_send_json_success( $response_data );
	}

	/**
	 * AJAX handler for processing signup actions.
	 *
	 * @since 1.0.0
	 */
	public function ajax_process_signup() {
		// Verify nonce.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'splms_signup_process' ) ) {
			wp_die( esc_html__( 'Security check failed', 'skillpulse-lms' ) );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'skillpulse-lms' ) );
		}

		$signup_id = isset( $_POST['signup_id'] ) ? intval( $_POST['signup_id'] ) : 0;
		$action    = isset( $_POST['process_action'] ) ? sanitize_text_field( wp_unslash( $_POST['process_action'] ) ) : '';

		if ( ! $signup_id || ! $action ) {
			wp_send_json_error( esc_html__( 'Invalid request parameters', 'skillpulse-lms' ) );
		}

		$signup_instance = SPLMS_Signup::get_instance();
		$signup          = $signup_instance->get_signup( $signup_id );

		if ( ! $signup ) {
			wp_send_json_error( esc_html__( 'Signup not found', 'skillpulse-lms' ) );
		}

		$message = '';
		$success = false;

		switch ( $action ) {
			case 'activate':
				$result = $signup_instance->activate_signup( $signup->activation_key );
				if ( ! is_wp_error( $result ) ) {
					$success = true;
					/* translators: %s: User email address. */
					$message = sprintf( esc_html__( 'Signup for %s has been activated successfully.', 'skillpulse-lms' ), $signup->user_email );
				} else {
					$message = $result->get_error_message();
				}
				break;

			case 'delete':
				$result = $signup_instance->delete_signup( $signup_id );
				if ( $result ) {
					$success = true;
					/* translators: %s: User email address. */
					$message = sprintf( esc_html__( 'Signup for %s has been deleted successfully.', 'skillpulse-lms' ), $signup->user_email );
				} else {
					$message = esc_html__( 'Failed to delete signup.', 'skillpulse-lms' );
				}
				break;

			case 'resend':
				// Assuming you have an email module for sending activation emails.
				if ( class_exists( 'SPLMS_Email_Module' ) ) {
					$result = SPLMS_Email_Module::get_instance()->send_activation_email( $signup_id );
					if ( $result ) {
						$success = true;
						/* translators: %s: User email address. */
						$message = sprintf( esc_html__( 'Activation email has been sent to %s successfully.', 'skillpulse-lms' ), $signup->user_email );
					} else {
						$message = esc_html__( 'Failed to send activation email.', 'skillpulse-lms' );
					}
				} else {
					$message = esc_html__( 'Email functionality not available.', 'skillpulse-lms' );
				}
				break;

			default:
				$message = esc_html__( 'Invalid action.', 'skillpulse-lms' );
		}

		if ( $success ) {
			wp_send_json_success( array( 'message' => $message ) );
		} else {
			wp_send_json_error( $message );
		}
	}
}
