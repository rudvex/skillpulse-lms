<?php
/**
 * Signup List Table
 *
 * Displays and manages pending signups in the WordPress admin area.
 * Extends WP_Users_List_Table to provide a table interface for reviewing,
 * activating, and managing pending user signups.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Include WordPress admin files.
if ( ! class_exists( 'WP_List_Table' ) ) {
	$splms_wp_list_table_file = ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
	if ( file_exists( $splms_wp_list_table_file ) ) {
		require_once $splms_wp_list_table_file;
	}
}

if ( ! class_exists( 'WP_Users_List_Table' ) ) {
	$splms_wp_users_list_table_file = ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
	if ( file_exists( $splms_wp_users_list_table_file ) ) {
		require_once $splms_wp_users_list_table_file;
	}
}

/**
 * SkillPulse LMS Signup List Table
 *
 * Displays and manages pending signups in the WordPress admin area.
 * Extends WP_Users_List_Table to provide a table interface for reviewing,
 * activating, and managing pending user signups.
 *
 * @since 1.0.0
 */
class SPLMS_Signup_List_Table extends WP_Users_List_Table {

	/**
	 * Signup counts.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $signup_counts = 0;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'signup',
				'plural'   => 'signups',
				'ajax'     => false,
				'screen'   => get_current_screen()->id,
			)
		);
	}

	/**
	 * Get columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of column definitions.
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'username'   => __( 'Username', 'skillpulse-lms' ),
			'name'       => __( 'Name', 'skillpulse-lms' ),
			'email'      => __( 'Email', 'skillpulse-lms' ),
			'user_type'  => __( 'User Type', 'skillpulse-lms' ),
			'registered' => __( 'Registered', 'skillpulse-lms' ),
			'status'     => __( 'Status', 'skillpulse-lms' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'name'       => array( 'user_name', false ),
			'username'   => array( 'user_login', false ),
			'email'      => array( 'user_email', false ),
			'user_type'  => array( 'user_type', false ),
			'registered' => array( 'registered', true ),
		);
	}

	/**
	 * Output the username column for a signup.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The signup object.
	 */
	public function column_username( $signup_object ) {
		$avatar = get_avatar( $signup_object->user_email, 32 );

		// Display avatar + username with review link (opens modal).
		echo wp_kses_post( $avatar ) . sprintf( '<strong><a href="#" class="review-signup" data-signup-id="%1$s">%2$s</a></strong><br/>', esc_attr( $signup_object->id ), esc_html( $signup_object->user_login ) );

		// Build row actions array with modal trigger classes.
		$actions             = array();
		$actions['review']   = sprintf( '<a href="#" class="review-signup" data-signup-id="%1$s">%2$s</a>', esc_attr( $signup_object->id ), esc_html( __( 'Review', 'skillpulse-lms' ) ) );
		$actions['activate'] = sprintf( '<a href="#" class="quick-activate" data-signup-id="%1$s" data-user-email="%2$s">%3$s</a>', esc_attr( $signup_object->id ), esc_attr( $signup_object->user_email ), esc_html( __( 'Activate', 'skillpulse-lms' ) ) );
		$actions['resend']   = sprintf( '<a href="#" class="quick-resend" data-signup-id="%1$s" data-user-email="%2$s">%3$s</a>', esc_attr( $signup_object->id ), esc_attr( $signup_object->user_email ), esc_html( __( 'Email', 'skillpulse-lms' ) ) );

		if ( current_user_can( 'delete_users' ) ) {
			$actions['delete'] = sprintf( '<a href="#" class="quick-delete" data-signup-id="%1$s" data-user-email="%2$s">%3$s</a>', esc_attr( $signup_object->id ), esc_attr( $signup_object->user_email ), __( 'Delete', 'skillpulse-lms' ) );
		}

		// Output row actions using WordPress built-in method.
		echo wp_kses_post( $this->row_actions( $actions ) );
	}

	/**
	 * Output the name column for a signup.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The signup object.
	 */
	public function column_name( $signup_object ) {
		echo esc_html( $signup_object->user_name );
	}

	/**
	 * Output the email column for a signup.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The signup object.
	 */
	public function column_email( $signup_object ) {
		echo esc_html( $signup_object->user_email );
	}

	/**
	 * Output the registered column for a signup.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The signup object.
	 */
	public function column_registered( $signup_object ) {
		echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $signup_object->registered ) ) );
	}

	/**
	 * Output the default column for a signup.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The signup object.
	 * @param string $column_name   The column name.
	 */
	public function column_default( $signup_object, $column_name ) {
		switch ( $column_name ) {
			case 'user_type':
				echo esc_html( ucfirst( $signup_object->user_type ) );
				break;
			case 'status':
				$status_labels = array(
					'pending'   => __( 'Pending', 'skillpulse-lms' ),
					'activated' => __( 'Activated', 'skillpulse-lms' ),
					'cancelled' => __( 'Cancelled', 'skillpulse-lms' ),
				);
				$status        = $signup_object->status;
				$label         = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;
				echo '<span class="signup-status signup-status-' . esc_attr( $status ) . '">' . esc_html( $label ) . '</span>';
				break;
			default:
				/**
				 * Fires for each custom column in the Users list table.
				 *
				 * @since 1.0.0
				 * @param string $column_name The name of the column to display.
				 * @param object $signup_object The signup object.
				 */
				do_action( 'splms_manage_signups_custom_column', $column_name, $signup_object );
		}
	}



	/**
	 * The text shown when no items are found.
	 *
	 * @since 1.0.0
	 */
	public function no_items() {
		esc_html_e( 'No pending accounts found.', 'skillpulse-lms' );
	}

	/**
	 * Display signups rows.
	 *
	 * @since 1.0.0
	 */
	public function display_rows() {
		$style = '';
		foreach ( $this->items as $userid => $signup_object ) {
			$style = ( ' class="alternate"' === $style ) ? '' : ' class="alternate"';
			$this->single_row( $signup_object, $style );
		}
	}

	/**
	 * Generate HTML for a single row on the users.php admin panel.
	 *
	 * @since 1.0.0
	 * @param object $signup_object The current signup object.
	 * @param string $style         Optional. Style attributes added to the `<tr>` element.
	 * @param string $role          Optional. Key for the role if the user has a role.
	 * @param int    $numposts      Optional. Post count to display for this user.
	 * @return void
	 */
	public function single_row( $signup_object, $style = '', $role = '', $numposts = 0 ) {
		echo '<tr' . esc_attr( $style ) . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $style is already sanitized.
		echo wp_kses_post( $this->single_row_columns( $signup_object ) );
		echo '</tr>';
	}

	/**
	 * Output checkbox column for bulk actions.
	 *
	 * @since 1.0.0
	 *
	 * @param object $item The signup item data.
	 */
	public function column_cb( $item ) {
		?>
		<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $item->id ); ?>">
			<?php
			/* translators: %s: User login name. */
			printf( esc_html__( 'Select %s', 'skillpulse-lms' ), esc_html( $item->user_login ) );
			?>
		</label>
		<input type="checkbox" name="signup_ids[]" id="cb-select-<?php echo esc_attr( $item->id ); ?>" value="<?php echo esc_attr( $item->id ); ?>" />
		<?php
	}

	/**
	 * Get bulk actions.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of bulk action options.
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate' => __( 'Activate', 'skillpulse-lms' ),
			'resend'   => __( 'Email', 'skillpulse-lms' ),
		);

		if ( current_user_can( 'delete_users' ) ) {
			$actions['delete'] = __( 'Delete', 'skillpulse-lms' );
		}

		return $actions;
	}

	/**
	 * Process bulk actions and individual actions.
	 *
	 * @since 1.0.0
	 */
	protected function process_bulk_action() {
		$action = $this->current_action();

		if ( ! $action ) {
			return;
		}

		$signup       = SPLMS_Signup::get_instance();
		$signup_query = SPLMS_Signup_Query::get_instance();

		// Handle individual signup actions.
		if ( isset( $_GET['signup_id'] ) && in_array( $action, array( 'activate', 'resend', 'delete' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$signup_id = intval( $_GET['signup_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			// Verify nonce.
			$nonce_action = 'splms_signup_' . $action . '_' . $signup_id;
			$nonce        = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $nonce_action ) ) {
				wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
			}

			switch ( $action ) {
				case 'activate':
					$signup_item = $signup_query->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = $signup->activate_signup( $signup_item->activation_key );
						if ( ! is_wp_error( $result ) ) {
							$redirect_url = add_query_arg( 'activated', '1', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						} else {
							$redirect_url = add_query_arg( 'error', 'activation_failed', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						}
					} else {
						$redirect_url = add_query_arg( 'error', 'signup_not_found', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
					}
					break;

				case 'resend':
					$signup_item = $signup_query->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = SPLMS_Email_Module::get_instance()->send_activation_email( $signup_id );
						if ( $result ) {
							$redirect_url = add_query_arg( 'resent', '1', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						} else {
							$redirect_url = add_query_arg( 'error', 'resend_failed', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						}
					} else {
						$redirect_url = add_query_arg( 'error', 'signup_not_found', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
					}
					break;

				case 'delete':
					if ( current_user_can( 'delete_users' ) ) {
						$result = $signup_query->delete_signup( $signup_id );
						if ( $result ) {
							$redirect_url = add_query_arg( 'deleted', '1', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						} else {
							$redirect_url = add_query_arg( 'error', 'delete_failed', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
						}
					} else {
						$redirect_url = add_query_arg( 'error', 'insufficient_permissions', remove_query_arg( array( 'action', 'signup_id', '_wpnonce' ) ) );
					}
					break;
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}

		// Handle bulk actions.
		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'bulk-' . $this->_args['plural'] ) ) {
			wp_die( esc_html__( 'Security check failed.', 'skillpulse-lms' ) );
		}

		$signup_ids = isset( $_POST['signup_ids'] ) ? array_map( 'absint', $_POST['signup_ids'] ) : array();

		if ( empty( $signup_ids ) ) {
			return;
		}

		switch ( $action ) {
			case 'activate':
				$activated = 0;
				foreach ( $signup_ids as $signup_id ) {
					$signup_item = $signup_query->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = $signup->activate_signup( $signup_item->activation_key );
						if ( ! is_wp_error( $result ) ) {
							++$activated;
						}
					}
				}
				wp_safe_redirect( add_query_arg( 'bulk_activated', $activated, wp_get_referer() ) );
				exit;

			case 'delete':
				$deleted = 0;
				foreach ( $signup_ids as $signup_id ) {
					$result = $signup_query->delete_signup( $signup_id );
					if ( $result ) {
						++$deleted;
					}
				}
				wp_safe_redirect( add_query_arg( 'bulk_deleted', $deleted, wp_get_referer() ) );
				exit;

			case 'resend':
				$resent = 0;
				foreach ( $signup_ids as $signup_id ) {
					$signup_item = $signup_query->get_signup( $signup_id );
					if ( $signup_item ) {
						$result = SPLMS_Email_Module::get_instance()->send_activation_email( $signup_id );
						if ( $result ) {
							++$resent;
						}
					}
				}
				wp_safe_redirect( add_query_arg( 'bulk_resent', $resent, wp_get_referer() ) );
				exit;
		}
	}

	/**
	 * Prepare items for display.
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$this->process_bulk_action();

		global $usersearch; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WordPress core global for WP_Users_List_Table compatibility.

		$request_s = isset( $_REQUEST['s'] ) ? wp_unslash( $_REQUEST['s'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Required for WP_Users_List_Table compatibility.
		$usersearch       = sanitize_text_field( $request_s );
		$signups_per_page = $this->get_items_per_page( str_replace( '-', '_', "{$this->screen->id}_per_page" ) );
		$paged            = $this->get_pagenum();

		$args = array(
			'offset'     => ( $paged - 1 ) * $signups_per_page,
			'number'     => $signups_per_page,
			'usersearch' => $usersearch,
			'orderby'    => 'id',
			'order'      => 'DESC',
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters for list table sorting.
		if ( isset( $_REQUEST['orderby'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for list table sorting.
			$args['orderby'] = sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameters for list table sorting.
		if ( isset( $_REQUEST['order'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for list table sorting.
			$args['order'] = sanitize_text_field( wp_unslash( $_REQUEST['order'] ) );
		}

		// Handle status filter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for list table filtering.
		if ( isset( $_GET['status'] ) && ! empty( $_GET['status'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for list table filtering.
			$args['status'] = sanitize_text_field( wp_unslash( $_GET['status'] ) );
		}

		$signup_query = SPLMS_Signup_Query::get_instance();
		$signups      = $signup_query->get_signups( $args );

		$this->items         = $signups['signups'];
		$this->signup_counts = $signups['total'];

		$this->set_pagination_args(
			array(
				'total_items' => $this->signup_counts,
				'per_page'    => $signups_per_page,
			)
		);

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Remove extra navigation elements.
	 *
	 * @since 1.0.0
	 *
	 * @param string $which Current table nav item ('top' or 'bottom').
	 */
	public function extra_tablenav( $which ) {
		// No extra tablenav needed.
	}

	/**
	 * Get filter views for the table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of view links.
	 */
	public function get_views() {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required for WP_Users_List_Table compatibility.
		global $role;

		$views = parent::get_views();

		$users_of_blog = count_users();
		unset( $users_of_blog );

		$users_url = admin_url( 'users.php' );

		// Add pending signups view.
		$signup_query  = SPLMS_Signup_Query::get_instance();
		$signup_count  = $signup_query->count_signups( array( 'status' => 'pending' ) );
		$pending_url   = add_query_arg( 'page', 'splms-signups', $users_url );
		$pending_class = ( 'registered' === $role ) ? ' class="current"' : '';
		/* translators: %s: Number of pending signups. */
		$pending_text        = sprintf( __( 'Pending %s', 'skillpulse-lms' ), '<span class="count">(' . number_format_i18n( $signup_count ) . ')</span>' );
		$views['registered'] = "<a href='$pending_url'$pending_class>$pending_text</a>";

		return $views;
	}
}
