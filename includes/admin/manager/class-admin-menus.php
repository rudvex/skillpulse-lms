<?php
/**
 * Admin Menus
 *
 * Manages the main SkillPulse LMS admin menu and submenus.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SkillPulse_LMS_Admin_Menus
 *
 * Manages the main SkillPulse LMS admin menu and submenus.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Admin_Menus {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Admin_Menus|null $instance
	 */
	private static $instance;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Admin_Menus
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
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
	private function __construct() {
	}


	/**
	 * Action hooks
	 *
	 * @since 1.0.0
	 */
	protected function setup_actions() {
		add_action( 'admin_menu', array( $this, 'add_admin_menus' ) );
		add_action( 'admin_head', array( $this, 'admin_menu_separator_css' ) );
	}

	/**
	 * Define all filter hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_filters() {
		add_filter( 'parent_file', array( $this, 'set_current_menu' ) );
		add_filter( 'submenu_file', array( $this, 'set_current_submenu' ), 10, 2 );
	}

	/**
	 * Add admin menus and submenus.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menus() {
		$admin_instance = class_exists( 'SkillPulse_LMS_Admin' ) ? SkillPulse_LMS_Admin::get_instance() : null;
		$icon           = $admin_instance ? $admin_instance->get_plugin_icon() : 'dashicons-graduation-cap';

		// Main menu.
		add_menu_page(
			__( 'SkillPulse LMS', 'skillpulse-lms' ),
			__( 'SkillPulse LMS', 'skillpulse-lms' ),
			'manage_options',
			'skillpulse-lms',
			array( $this, 'overview_page' ),
			$icon,
			30
		);

		// Overview submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Overview', 'skillpulse-lms' ),
			__( 'Overview', 'skillpulse-lms' ),
			'manage_options',
			'skillpulse-lms',
			array( $this, 'overview_page' )
		);

		// Setup Wizard submenu (hidden from menu, only accessible by direct URL).
		if ( ! $this->is_wizard_completed() ) {
			add_submenu_page(
				'skillpulse-lms',
				__( 'Setup Wizard', 'skillpulse-lms' ),
				__( 'Setup Wizard', 'skillpulse-lms' ),
				'manage_options',
				'splms-setup-wizard',
				array( $this, 'setup_wizard_page' )
			);
		}

		// Separator to organize content vs management menus.
		add_submenu_page(
			'skillpulse-lms',
			'',
			'',
			'read',
			'splms-separator',
			'__return_null'
		);

		// Courses submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Courses', 'skillpulse-lms' ),
			__( 'Courses', 'skillpulse-lms' ),
			'edit_posts',
			'edit.php?post_type=' . SPLMS_POST_TYPES['course']
		);

		// Sections submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Sections', 'skillpulse-lms' ),
			__( 'Sections', 'skillpulse-lms' ),
			'edit_posts',
			'edit.php?post_type=' . SPLMS_POST_TYPES['section']
		);

		// Lessons submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Lessons', 'skillpulse-lms' ),
			__( 'Lessons', 'skillpulse-lms' ),
			'edit_posts',
			'edit.php?post_type=' . SPLMS_POST_TYPES['lesson']
		);

		// Quizzes submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Quizzes', 'skillpulse-lms' ),
			__( 'Quizzes', 'skillpulse-lms' ),
			'edit_posts',
			'edit.php?post_type=' . SPLMS_POST_TYPES['quiz']
		);

		// Separator to organize content vs management menus.
		add_submenu_page(
			'skillpulse-lms',
			'',
			'',
			'read',
			'splms-separator',
			'__return_null'
		);
		// Enrollments submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Enrollments', 'skillpulse-lms' ),
			__( 'Enrollments', 'skillpulse-lms' ),
			'manage_options',
			'splms-enrollments',
			array( $this, 'enrollments_page' )
		);
		// Notification Management submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Notifications', 'skillpulse-lms' ),
			__( 'Notifications', 'skillpulse-lms' ),
			'manage_options',
			'splms-notifications',
			array( $this, 'notifications_page' )
		);

		// Management/Settings separator.
		add_submenu_page(
			'skillpulse-lms',
			'',
			'',
			'read',
			'splms-separator',
			'__return_null'
		);
		// Settings submenu.
		add_submenu_page(
			'skillpulse-lms',
			__( 'Settings', 'skillpulse-lms' ),
			__( 'Settings', 'skillpulse-lms' ),
			'manage_options',
			'splms-settings',
			array( $this, 'settings_page' )
		);
	}

	/**
	 * Output CSS to render our submenu separators as horizontal rules.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu_separator_css() {
		$css = 'body.skillpulse-lms-user-admin #adminmenumain .toplevel_page_skillpulse-lms .wp-submenu a[href$="page=splms-separator"]{pointer-events:none;cursor:default;padding-bottom:0;margin-right:16px;margin-left:14px;margin-bottom:5px;border-bottom:1px solid #555}';
		wp_add_inline_style( 'wp-admin', $css );
	}

	/**
	 * Set the current menu parent for our custom post types.
	 *
	 * @since 1.0.0
	 *
	 * @param string $parent_file The parent file.
	 *
	 * @return string Modified parent file.
	 */
	public function set_current_menu( $parent_file ) {
		global $current_screen;

		if ( ! empty( $current_screen ) ) {
			$post_type = $current_screen->post_type;

			// Check if current screen is one of our custom post types.
			$our_post_types = array(
				SPLMS_POST_TYPES['course'],
				SPLMS_POST_TYPES['section'],
				SPLMS_POST_TYPES['lesson'],
				SPLMS_POST_TYPES['quiz'],
			);

			// Only include certificate post type if certificates are enabled.
			if ( splms_get_setting( 'enable_certificates', false ) ) {
				$our_post_types[] = SPLMS_POST_TYPES['certificate'];
			}

			if ( in_array( $post_type, $our_post_types, true ) ) {
				$parent_file = 'skillpulse-lms';
			}
		}

		return $parent_file;
	}

	/**
	 * Set the current submenu for our custom post types.
	 *
	 * @since 1.0.0
	 *
	 * @param string $submenu_file The submenu file.
	 * @param string $parent_file  The parent file.
	 *
	 * @return string Modified submenu file.
	 */
	public function set_current_submenu( $submenu_file, $parent_file ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by WordPress hook signature.
		global $current_screen;

		if ( ! empty( $current_screen ) ) {
			$post_type = $current_screen->post_type;

			// Set the submenu file based on post type.
			if ( SPLMS_POST_TYPES['course'] === $post_type ) {
				$submenu_file = 'edit.php?post_type=' . SPLMS_POST_TYPES['course'];
			} elseif ( SPLMS_POST_TYPES['section'] === $post_type ) {
				$submenu_file = 'edit.php?post_type=' . SPLMS_POST_TYPES['section'];
			} elseif ( SPLMS_POST_TYPES['lesson'] === $post_type ) {
				$submenu_file = 'edit.php?post_type=' . SPLMS_POST_TYPES['lesson'];
			} elseif ( SPLMS_POST_TYPES['quiz'] === $post_type ) {
				$submenu_file = 'edit.php?post_type=' . SPLMS_POST_TYPES['quiz'];
			} elseif ( SPLMS_POST_TYPES['certificate'] === $post_type && splms_get_setting( 'enable_certificates', false ) ) {
				$submenu_file = 'edit.php?post_type=' . SPLMS_POST_TYPES['certificate'];
			}
		}

		return $submenu_file;
	}

	/**
	 * Render overview page.
	 *
	 * @since 1.0.0
	 */
	public function overview_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-overview-page"></div>
		</div>
		<?php
	}

	/**
	 * Render enrollments page.
	 *
	 * @since 1.0.0
	 */
	public function enrollments_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-enrollments-page"></div>
		</div>
		<?php
	}

	/**
	 * Render orders page.
	 *
	 * @since 1.0.0
	 */
	public function orders_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-orders-page"></div>
		</div>
		<?php
	}

	/**
	 * Render reports page.
	 *
	 * @since 1.0.0
	 */
	public function reports_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-reports-page"></div>
		</div>
		<?php
	}

	/**
	 * Render license page.
	 *
	 * @since 1.0.0
	 */
	public function license_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-license"></div>
		</div>
		<?php
	}

	/**
	 * Render settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-settings"></div>
		</div>
		<?php
	}

	/**
	 * Render tools page.
	 *
	 * @since 1.0.0
	 */
	public function tools_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-tools-page"></div>
		</div>
		<?php
	}

	/**
	 * Render notification management page.
	 *
	 * @since 1.0.0
	 */
	public function notifications_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-notifications"></div>
		</div>
		<?php
	}

	/**
	 * Render quiz attempts page.
	 *
	 * @since 1.0.0
	 */
	public function quiz_attempts_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-quiz-attempts-page"></div>
		</div>
		<?php
	}

	/**
	 * Render certificate manager page.
	 *
	 * @since 1.0.0
	 */
	public function certificate_manager_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );

			return;
		}

		?>
		<div class="wrap">
			<div id="splms-certificate-manager"></div>
		</div>
		<?php
	}



	/**
	 * Check if setup wizard is completed.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if wizard completed, false otherwise.
	 */
	private function is_wizard_completed() {
		return (bool) get_option( 'splms_wizard_completed', false );
	}

	/**
	 * Render setup wizard page.
	 *
	 * @since 1.0.0
	 */
	public function setup_wizard_page() {
		// Delegate to the wizard class if it exists.
		if ( class_exists( 'SkillPulse_LMS_Setup_Wizard' ) ) {
			$wizard = SkillPulse_LMS_Setup_Wizard::get_instance();
			if ( method_exists( $wizard, 'render_wizard_page' ) ) {
				$wizard->render_wizard_page();
				return;
			}
		}

		// Fallback if wizard class not available.
		if ( ! current_user_can( 'manage_options' ) ) {
			printf( '<p>%1$s</p>', esc_html__( 'You don\'t have permission to access this page.', 'skillpulse-lms' ) );
			return;
		}

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Setup Wizard', 'skillpulse-lms' ); ?></h1>
			<p><?php esc_html_e( 'Setup wizard is loading...', 'skillpulse-lms' ); ?></p>
		</div>
		<?php
	}
}