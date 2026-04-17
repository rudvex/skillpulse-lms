<?php
/**
 * Settings Configuration
 *
 * Replaces settings-config.json with PHP configuration for better performance,
 * translation support, and dynamic capabilities.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_settings = get_option( 'splms_settings' );

/**
 * Get published WordPress pages as dropdown options.
 *
 * @return array Array of page options.
 */
if ( ! function_exists( 'splms_get_page_options' ) ) {
	/**
	 * Get published WordPress pages as dropdown options.
	 *
	 * @return array Array of page options.
	 */
	function splms_get_page_options() {
		$pages = get_pages(
			array(
				'post_status' => 'publish',
				'sort_column' => 'post_title',
				'sort_order'  => 'ASC',
			)
		);

		$options = array();
		if ( $pages && is_array( $pages ) ) {
			foreach ( $pages as $page ) {
				$options[] = array(
					'value' => $page->ID,
					'label' => $page->post_title,
				);
			}
		}

		return $options;
	}
}

/**
 * Get user roles as dropdown options.
 *
 * @return array Array of user role options.
 */
if ( ! function_exists( 'splms_get_user_role_options' ) ) {
	/**
	 * Get user roles as dropdown options.
	 *
	 * @return array Array of user role options.
	 */
	function splms_get_user_role_options() {
		$roles   = wp_roles()->roles;
		$options = array();

		foreach ( $roles as $role_key => $role ) {
			$options[] = array(
				'value' => $role_key,
				'label' => $role['name'],
			);
		}

		return $options;
	}
}

$general_tab = array(
	'id'          => 'general',
	'title'       => 'General',
	'icon'        => 'admin-generic',
	'description' => 'Configure basic settings for your SkillPulse LMS system.',
	'sections'    => array(
		array(
			'id'     => 'general_settings',
			'title'  => 'General Settings',
			'fields' => array(
				array(
					'id'          => 'customize_login_screen',
					'type'        => 'toggle',
					'label'       => 'Customize Login Screen',
					'description' => 'Apply custom styling and branding to the WordPress login page.',
					'tooltip'     => 'When enabled, the login page will use SkillPulse LMS branding and modern styling instead of the default WordPress login design.',
					'default'     => true,
					'value'       => isset( $all_settings['general']['general_settings']['customize_login_screen'] ) ? $all_settings['general']['general_settings']['customize_login_screen'] : true,
				),
				array(
					'id'          => 'login_welcome_title',
					'type'        => 'text',
					'label'       => 'Login Welcome Title',
					'description' => 'Title text displayed above the login form.',
					'tooltip'     => 'Customize the welcome message shown to users on the login page. Use {sitename} to include your site name dynamically.',
					'default'     => 'Welcome to {sitename}',
					'value'       => isset( $all_settings['general']['general_settings']['login_welcome_title'] ) ? $all_settings['general']['general_settings']['login_welcome_title'] : 'Welcome to {sitename}',
					'conditional' => array(
						'key'   => 'customize_login_screen',
						'value' => true,
					),
				),
				array(
					'id'          => 'login_welcome_subtitle',
					'type'        => 'text',
					'label'       => 'Login Welcome Subtitle',
					'description' => 'Subtitle text displayed below the welcome title.',
					'tooltip'     => 'Customize the subtitle message shown to users on the login page.',
					'default'     => 'Sign in to continue your learning journey',
					'value'       => isset( $all_settings['general']['general_settings']['login_welcome_subtitle'] ) ? $all_settings['general']['general_settings']['login_welcome_subtitle'] : 'Sign in to continue your learning journey',
					'conditional' => array(
						'key'   => 'customize_login_screen',
						'value' => true,
					),
				),
				array(
					'id'          => 'user_signup_enabled',
					'type'        => 'toggle',
					'label'       => 'Enable User Signup',
					'description' => 'Allow new users to signup on your site.',
					'default'     => false,
					'value'       => isset( $all_settings['general']['general_settings']['user_signup_enabled'] ) ? $all_settings['general']['general_settings']['user_signup_enabled'] : false,
				),
				array(
					'id'          => 'default_user_role',
					'type'        => 'select',
					'label'       => 'Default User Role',
					'description' => 'Select the default role for new users.',
					'default'     => 'student',
					'value'       => isset( $all_settings['general']['general_settings']['default_user_role'] ) ? $all_settings['general']['general_settings']['default_user_role'] : 'student',
					'conditional' => array(
						'key'   => 'user_signup_enabled',
						'value' => true,
					),
					'options'     => splms_get_user_role_options(),
				),
			),
		),
		array(
			'id'     => 'pages_settings',
			'title'  => 'Pages Settings',
			'fields' => array(
				array(
					'id'              => 'signup_page_id',
					'type'            => 'search_select',
					'label'           => 'Signup Page',
					'description'     => 'Select the page for user signup.',
					'tooltip'         => 'This page will be used for user signup. If not set, the default WordPress signup will be used.',
					'value'           => isset( $all_settings['general']['pages_settings']['signup_page_id'] ) ? $all_settings['general']['pages_settings']['signup_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a signup page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Signup Page',
				),
				array(
					'id'              => 'courses_page_id',
					'type'            => 'search_select',
					'label'           => 'Courses Page',
					'description'     => 'Select the page that will display all courses. This works similar to WordPress blog page.',
					'tooltip'         => 'Choose a page that will serve as your main courses listing page. If no page is selected, a default "Courses" page will be created automatically.',
					'default'         => '',
					'value'           => isset( $all_settings['general']['pages_settings']['courses_page_id'] ) ? $all_settings['general']['pages_settings']['courses_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a courses page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Courses Page',
				),
				array(
					'id'              => 'dashboard_page_id',
					'type'            => 'search_select',
					'label'           => 'Dashboard Page',
					'description'     => 'Select the page that will display the user dashboard. This page will automatically use a minimal template with just the dashboard shortcode.',
					'tooltip'         => 'Choose a page that will serve as your user dashboard. The selected page will automatically be configured to show only the dashboard content without standard WordPress title and content areas.',
					'default'         => '',
					'value'           => isset( $all_settings['general']['pages_settings']['dashboard_page_id'] ) ? $all_settings['general']['pages_settings']['dashboard_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a dashboard page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Dashboard Page',
				),
				array(
					'id'              => 'logout_redirect_page_id',
					'type'            => 'search_select',
					'label'           => 'Logout Redirect Page',
					'description'     => 'Select the page users are redirected to after logging out.',
					'tooltip'         => 'This page will be shown to users after they log out',
					'value'           => isset( $all_settings['general']['pages_settings']['logout_redirect_page_id'] ) ? $all_settings['general']['pages_settings']['logout_redirect_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a logout redirect page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Logout Redirect Page',
				),
				array(
					'id'              => 'terms_conditions_page_id',
					'type'            => 'search_select',
					'label'           => 'Terms and Conditions Page',
					'description'     => 'Choose the page for terms and conditions.',
					'tooltip'         => 'This page will show the terms and conditions for visitors to browse',
					'value'           => isset( $all_settings['general']['pages_settings']['terms_conditions_page_id'] ) ? $all_settings['general']['pages_settings']['terms_conditions_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a terms and conditions page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Page',
				),
				array(
					'id'              => 'privacy_policy_page_id',
					'type'            => 'search_select',
					'label'           => 'Privacy Policy Page',
					'description'     => 'Choose the page for the privacy policy.',
					'tooltip'         => 'This page will show the privacy policy for visitors to browse',
					'value'           => isset( $all_settings['general']['pages_settings']['privacy_policy_page_id'] ) ? $all_settings['general']['pages_settings']['privacy_policy_page_id'] : '',
					'options'         => splms_get_page_options(),
					'searchParam'     => 'search',
					'placeholder'     => 'Search and select a privacy policy page...',
					'minSearchLength' => 2,
					'maxResults'      => 20,
					'showViewButton'  => true,
					'viewButtonText'  => 'View Page',
				),
			),
		),
		array(
			'id'     => 'maintenance_settings',
			'title'  => 'Maintenance Mode',
			'fields' => array(
				array(
					'id'          => 'maintenance_mode',
					'type'        => 'toggle',
					'label'       => 'Enable Maintenance Mode',
					'description' => 'Enable maintenance mode to temporarily disable course access for users while making updates.',
					'default'     => false,
					'value'       => isset( $all_settings['general']['maintenance_settings']['maintenance_mode'] ) ? $all_settings['general']['maintenance_settings']['maintenance_mode'] : false,
				),
				array(
					'id'          => 'maintenance_title',
					'type'        => 'text',
					'label'       => 'Maintenance Title',
					'description' => 'Custom title displayed on the maintenance page.',
					'tooltip'     => 'This title appears at the top of the maintenance page. Make it relevant to what you\'re actually working on.',
					'default'     => 'Learning Platform Upgrade in Progress',
					'value'       => isset( $all_settings['general']['maintenance_settings']['maintenance_title'] ) ? $all_settings['general']['maintenance_settings']['maintenance_title'] : 'Learning Platform Upgrade in Progress',
					'conditional' => array(
						'key'   => 'maintenance_mode',
						'value' => true,
					),
				),
				array(
					'id'          => 'maintenance_message',
					'type'        => 'textarea',
					'label'       => 'Maintenance Message',
					'description' => 'Main message explaining the maintenance to students.',
					'tooltip'     => 'Provide clear information about what\'s happening and reassure students about their data.',
					'default'     => 'We\'re enhancing your learning experience! Our platform is temporarily offline while we upgrade our systems to serve you better.',
					'value'       => isset( $all_settings['general']['maintenance_settings']['maintenance_message'] ) ? $all_settings['general']['maintenance_settings']['maintenance_message'] : 'We\'re enhancing your learning experience! Our platform is temporarily offline while we upgrade our systems to serve you better.',
					'conditional' => array(
						'key'   => 'maintenance_mode',
						'value' => true,
					),
				),
				array(
					'id'          => 'maintenance_show_features',
					'type'        => 'toggle',
					'label'       => 'Show Features Section',
					'description' => 'Display "What We\'re Working On" section with feature list.',
					'tooltip'     => 'Enable this to show students what improvements are being made during maintenance.',
					'default'     => true,
					'value'       => isset( $all_settings['general']['maintenance_settings']['maintenance_show_features'] ) ? $all_settings['general']['maintenance_settings']['maintenance_show_features'] : true,
					'conditional' => array(
						'key'   => 'maintenance_mode',
						'value' => true,
					),
				),
				array(
					'id'              => 'maintenance_features',
					'type'            => 'editor',
					'label'           => 'Maintenance Features',
					'description'     => 'List of features or improvements being worked on (use bullets or list format).',
					'tooltip'         => 'Add specific items you\'re working on during this maintenance window. Use bullet points or numbered lists for better formatting.',
					'default'         => '• Enhanced course performance & loading<br>• New interactive learning features<br>• Improved mobile learning experience<br>• Advanced progress tracking system<br>• Updated certificate generation<br>• Enhanced quiz and assessment tools',
					'value'           => isset( $all_settings['general']['maintenance_settings']['maintenance_features'] ) ? $all_settings['general']['maintenance_settings']['maintenance_features'] : '• Enhanced course performance & loading<br>• New interactive learning features<br>• Improved mobile learning experience<br>• Advanced progress tracking system<br>• Updated certificate generation<br>• Enhanced quiz and assessment tools',
					'conditional'     => array(
						'operator'   => 'AND',
						'conditions' => array(
							array(
								'key'   => 'maintenance_mode',
								'value' => true,
							),
							array(
								'key'   => 'maintenance_show_features',
								'value' => true,
							),
						),
					),
					'editor_settings' => array(
						'textarea_rows' => 8,
						'media_buttons' => false,
						'tinymce'       => array(
							'toolbar1' => 'bold italic | bullist numlist | link unlink',
							'toolbar2' => '',
							'toolbar3' => '',
							'height'   => 200,
						),
						'quicktags'     => array(
							'buttons' => 'strong,em,ul,ol,li',
						),
					),
				),
			),
		),
	),
);
$courses_tab = array(
	'id'          => 'courses',
	'title'       => 'Courses',
	'icon'        => 'admin-post',
	'description' => 'Configure course-related settings and features.',
	'sections'    => array(
		array(
			'id'          => 'course_discovery',
			'title'       => 'Course Discovery & Organization',
			'description' => 'Configure how courses are discovered and organized in your catalog.',
			'fields'      => array(
				array(
					'id'          => 'course_search_enabled',
					'type'        => 'toggle',
					'label'       => 'Enable Course Search',
					'description' => 'Enable search functionality in the course catalog.',
					'tooltip'     => 'When enabled, students can search for courses by title, description, or keywords.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_discovery']['course_search_enabled'] ) ? $all_settings['courses']['course_discovery']['course_search_enabled'] : true,
				),
				array(
					'id'          => 'course_categories_enabled',
					'type'        => 'toggle',
					'label'       => 'Enable Course Categories',
					'description' => 'Allow courses to be organized by categories.',
					'tooltip'     => 'Enable category taxonomy for organizing courses into hierarchical groups.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_discovery']['course_categories_enabled'] ) ? $all_settings['courses']['course_discovery']['course_categories_enabled'] : true,
				),
				array(
					'id'          => 'course_tags_enabled',
					'type'        => 'toggle',
					'label'       => 'Enable Course Tags',
					'description' => 'Allow courses to be tagged for better organization.',
					'tooltip'     => 'Enable tagging system for adding flexible metadata to courses.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_discovery']['course_tags_enabled'] ) ? $all_settings['courses']['course_discovery']['course_tags_enabled'] : true,
				),
			),
		),
		array(
			'id'          => 'course_display',
			'title'       => 'Course Display Settings',
			'description' => 'Control how courses are displayed to students.',
			'fields'      => array(
				array(
					'id'          => 'course_item_per_page',
					'type'        => 'number',
					'label'       => 'Courses Per Page',
					'description' => 'Set the number of courses to display per page in course catalog.',
					'tooltip'     => 'This affects pagination in course listing pages.',
					'default'     => 12,
					'value'       => isset( $all_settings['courses']['course_display']['course_item_per_page'] ) ? $all_settings['courses']['course_display']['course_item_per_page'] : 12,
					'min'         => 1,
					'max'         => 50,
				),
				array(
					'id'          => 'enable_related_courses',
					'type'        => 'toggle',
					'label'       => 'Enable Related Courses',
					'description' => 'Display related courses section on single course pages.',
					'tooltip'     => 'Shows similar courses based on categories and tags to encourage exploration.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_display']['enable_related_courses'] ) ? $all_settings['courses']['course_display']['enable_related_courses'] : true,
				),
			),
		),
		array(
			'id'          => 'student_features',
			'title'       => 'Student Features',
			'description' => 'Enable or disable features available to students.',
			'fields'      => array(
				array(
					'id'          => 'enable_bookmarks',
					'type'        => 'toggle',
					'label'       => 'Enable Bookmarks',
					'description' => 'Allow students to bookmark lessons and quizzes for later access.',
					'tooltip'     => 'Students can mark specific lessons or quizzes to quickly return to them later.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['student_features']['enable_bookmarks'] ) ? $all_settings['courses']['student_features']['enable_bookmarks'] : true,
				),
				array(
					'id'          => 'enable_course_wishlist',
					'type'        => 'toggle',
					'label'       => 'Enable Course Wishlist',
					'description' => 'Allow students to save courses to a wishlist.',
					'tooltip'     => 'Students can create a wishlist of courses they are interested in taking.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['student_features']['enable_course_wishlist'] ) ? $all_settings['courses']['student_features']['enable_course_wishlist'] : true,
				),
				array(
					'id'          => 'enable_course_share',
					'type'        => 'toggle',
					'label'       => 'Enable Course Sharing',
					'description' => 'Allow students to share courses with friends via social media or email.',
					'tooltip'     => 'Adds share buttons for social media platforms and email sharing.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['student_features']['enable_course_share'] ) ? $all_settings['courses']['student_features']['enable_course_share'] : true,
				),
			),
		),
		array(
			'id'          => 'course_reviews',
			'title'       => 'Reviews & Ratings',
			'description' => 'Configure course review and rating system.',
			'fields'      => array(
				array(
					'id'          => 'course_reviews_enabled',
					'type'        => 'toggle',
					'label'       => 'Enable Course Reviews',
					'description' => 'Allow students to leave reviews and ratings for courses.',
					'tooltip'     => 'Students can rate courses and write reviews after enrollment.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_reviews']['course_reviews_enabled'] ) ? $all_settings['courses']['course_reviews']['course_reviews_enabled'] : true,
				),
				array(
					'id'          => 'reviews_require_completion',
					'type'        => 'toggle',
					'label'       => 'Require Course Completion',
					'description' => 'Only students who completed the course can leave reviews.',
					'tooltip'     => 'When enabled, students must complete all course requirements before they can submit a review.',
					'default'     => false,
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_require_completion'] ) ? $all_settings['courses']['course_reviews']['reviews_require_completion'] : false,
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
				array(
					'id'          => 'reviews_require_moderation',
					'type'        => 'toggle',
					'label'       => 'Enable Review Moderation',
					'description' => 'Reviews must be approved before appearing publicly.',
					'tooltip'     => 'When enabled, all reviews will be held for moderation until approved by an instructor or admin.',
					'default'     => false,
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_require_moderation'] ) ? $all_settings['courses']['course_reviews']['reviews_require_moderation'] : false,
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
				array(
					'id'          => 'reviews_allow_admin',
					'type'        => 'toggle',
					'label'       => 'Allow Admin Reviews',
					'description' => 'Allow administrators to review any course.',
					'tooltip'     => 'When enabled, admins can leave reviews on courses regardless of enrollment status.',
					'default'     => true,
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_allow_admin'] ) ? $all_settings['courses']['course_reviews']['reviews_allow_admin'] : true,
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
				array(
					'id'          => 'reviews_edit_window',
					'type'        => 'select',
					'label'       => 'Review Edit Window',
					'description' => 'How long users can edit their reviews after posting.',
					'tooltip'     => 'This controls the time period during which users can edit or update their submitted reviews.',
					'options'     => array(
						array(
							'value' => '0',
							'label' => 'Never - No editing allowed',
						),
						array(
							'value' => '1',
							'label' => '1 Hour',
						),
						array(
							'value' => '24',
							'label' => '24 Hours',
						),
						array(
							'value' => '168',
							'label' => '1 Week',
						),
						array(
							'value' => '-1',
							'label' => 'Always - Unlimited editing',
						),
					),
					'default'     => '24',
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_edit_window'] ) ? $all_settings['courses']['course_reviews']['reviews_edit_window'] : '24',
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
				array(
					'id'          => 'reviews_min_length',
					'type'        => 'number',
					'label'       => 'Minimum Review Length',
					'description' => 'Minimum characters required for a review (0 = no minimum).',
					'tooltip'     => 'Set the minimum number of characters required for review text. Set to 0 for no minimum.',
					'default'     => 10,
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_min_length'] ) ? $all_settings['courses']['course_reviews']['reviews_min_length'] : 10,
					'min'         => 0,
					'max'         => 100,
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
				array(
					'id'          => 'reviews_max_length',
					'type'        => 'number',
					'label'       => 'Maximum Review Length',
					'description' => 'Maximum characters allowed for a review.',
					'tooltip'     => 'Set the maximum number of characters allowed for review text.',
					'default'     => 500,
					'value'       => isset( $all_settings['courses']['course_reviews']['reviews_max_length'] ) ? $all_settings['courses']['course_reviews']['reviews_max_length'] : 500,
					'min'         => 100,
					'max'         => 2000,
					'conditional' => array(
						'key'   => 'course_reviews_enabled',
						'value' => true,
					),
				),
			),
		),
		array(
			'id'          => 'enrollment_settings',
			'title'       => 'Enrollment & Access Control',
			'description' => 'Manage student enrollment and access policies.',
			'fields'      => array(
				array(
					'id'          => 'allow_student_unenrollment',
					'type'        => 'toggle',
					'label'       => 'Allow Student Self-Unenrollment',
					'description' => 'Allow students to unenroll themselves from courses.',
					'tooltip'     => 'When disabled, only administrators can unenroll students. This prevents accidental unenrollment from paid courses.',
					'default'     => false,
					'value'       => isset( $all_settings['courses']['enrollment_settings']['allow_student_unenrollment'] ) ? $all_settings['courses']['enrollment_settings']['allow_student_unenrollment'] : false,
				),
			),
		),

	),
);



return array(
	'tabs'     => array(
		$general_tab,
		$courses_tab,
	),
	'metadata' => array(
		'version'      => '1.0.0',
		'last_updated' => time(),
		'supports'     => array( 'admin', 'api', 'editor' ),
	),
);
