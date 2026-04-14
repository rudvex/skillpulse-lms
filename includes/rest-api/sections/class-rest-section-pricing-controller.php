<?php
/**
 * Section Pricing REST API Controller
 *
 * Handles REST API endpoints for section pricing management.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/sections/{id}/pricing                    - Get section pricing
 * POST   /splms/v1/sections/{id}/pricing                    - Update section pricing
 * GET    /splms/v1/courses/{id}/sections-with-pricing      - Get all sections with pricing
 * POST   /splms/v1/courses/{id}/bulk-section-pricing       - Bulk update section pricing
 * GET    /splms/v1/users/{user_id}/section-access/{section_id}  - Check user section access
 * GET    /splms/v1/courses/{id}/pricing-options            - Get course pricing options
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Section Pricing REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_REST_Section_Pricing_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
	}

	/**
	 * Check if section-based pricing feature is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error True if enabled, WP_Error if disabled.
	 */
	private function check_feature_enabled() {
		$enabled = splms_get_setting( 'enable_section_based_pricing', false );

		if ( ! $enabled ) {
			return new WP_Error(
				'feature_disabled',
				__( 'Section-based pricing feature is currently disabled. Please enable it in SkillPulse LMS Settings.', 'skillpulse-lms' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}

	/**
	 * Register the section pricing routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get section pricing.
		register_rest_route(
			$this->namespace,
			'/sections/(?P<id>[\d]+)/pricing',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_section_pricing' ),
				'permission_callback' => array( $this, 'get_section_pricing_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Section ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Update section pricing.
		register_rest_route(
			$this->namespace,
			'/sections/(?P<id>[\d]+)/pricing',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_section_pricing' ),
				'permission_callback' => array( $this, 'update_section_pricing_permissions_check' ),
				'args'                => $this->get_pricing_schema(),
			)
		);

		// Get course sections with pricing.
		register_rest_route(
			$this->namespace,
			'/courses/(?P<id>[\d]+)/sections-with-pricing',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_course_sections_with_pricing' ),
				'permission_callback' => array( $this, 'get_course_sections_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Course ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Bulk update section pricing.
		register_rest_route(
			$this->namespace,
			'/courses/(?P<id>[\d]+)/bulk-section-pricing',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'bulk_update_section_pricing' ),
				'permission_callback' => array( $this, 'bulk_update_pricing_permissions_check' ),
				'args'                => array(
					'id'                    => array(
						'description' => __( 'Course ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'price'                 => array(
						'description' => __( 'Price to apply to all sections.', 'skillpulse-lms' ),
						'type'        => 'number',
						'required'    => true,
					),
					'exclude_free_sections' => array(
						'description' => __( 'Exclude free sections from bulk update.', 'skillpulse-lms' ),
						'type'        => 'boolean',
						'default'     => true,
					),
				),
			)
		);

		// Check user section access.
		register_rest_route(
			$this->namespace,
			'/users/(?P<user_id>[\d]+)/section-access/(?P<section_id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'check_user_section_access' ),
				'permission_callback' => array( $this, 'check_section_access_permissions_check' ),
				'args'                => array(
					'user_id'    => array(
						'description' => __( 'User ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'section_id' => array(
						'description' => __( 'Section ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);

		// Get course pricing options.
		register_rest_route(
			$this->namespace,
			'/courses/(?P<id>[\d]+)/pricing-options',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_course_pricing_options' ),
				'permission_callback' => array( $this, 'get_pricing_options_permissions_check' ),
				'args'                => array(
					'id'      => array(
						'description' => __( 'Course ID.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
					'user_id' => array(
						'description' => __( 'User ID to check purchased sections.', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => false,
					),
				),
			)
		);
	}

	/**
	 * Get section pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_section_pricing( $request ) {
		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$pricing = $this->get_section_pricing_data( $section_id );

		$response = array(
			'success' => true,
			'data'    => $pricing,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Update section pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_section_pricing( $request ) {
		// Check if feature is enabled.
		$feature_check = $this->check_feature_enabled();
		if ( is_wp_error( $feature_check ) ) {
			return $feature_check;
		}

		$section_id = $request->get_param( 'id' );
		$section    = get_post( $section_id );

		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get pricing data from request.
		$pricing_data = array(
			'is_free'                   => isset( $request['is_free'] ) ? (bool) $request['is_free'] : false,
			'price'                     => isset( $request['price'] ) ? floatval( $request['price'] ) : 0,
			'sale_price'                => isset( $request['sale_price'] ) ? floatval( $request['sale_price'] ) : 0,
			'sale_start_date'           => isset( $request['sale_start_date'] ) ? sanitize_text_field( $request['sale_start_date'] ) : '',
			'sale_end_date'             => isset( $request['sale_end_date'] ) ? sanitize_text_field( $request['sale_end_date'] ) : '',
			'preview_enabled'           => isset( $request['preview_enabled'] ) ? (bool) $request['preview_enabled'] : false,
			'preview_items'             => isset( $request['preview_items'] ) ? $request['preview_items'] : array(
				'lessons'     => array(),
				'quizzes'     => array(),
				'assessments' => array(),
			),
			'requires_previous_section' => isset( $request['requires_previous_section'] ) ? (bool) $request['requires_previous_section'] : false,
		);

		// Validate price.
		if ( ! $pricing_data['is_free'] && $pricing_data['price'] < 0 ) {
			return new WP_Error( 'invalid_price', __( 'Price must be a positive number.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Save pricing data.
		update_post_meta( $section_id, '_splms_section_pricing', $pricing_data );

		// Get updated pricing.
		$updated_pricing = $this->get_section_pricing_data( $section_id );

		$response = array(
			'success' => true,
			'message' => __( 'Section pricing updated successfully.', 'skillpulse-lms' ),
			'data'    => $updated_pricing,
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get course sections with pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_course_sections_with_pricing( $request ) {
		$course_id = $request->get_param( 'id' );
		$course    = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get course sections.
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_items       = $course_items_query->get_items( $course_id );

		$sections            = array();
		$total_price         = 0;
		$total_sale_price    = 0;
		$free_sections_count = 0;
		$paid_sections_count = 0;

		foreach ( $course_items as $course_item ) {
			if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
				continue;
			}

			$section_id   = intval( $course_item->item_id );
			$section_post = get_post( $section_id );

			if ( ! $section_post ) {
				continue;
			}

			$pricing = $this->get_section_pricing_data( $section_id );

			$sections[] = array(
				'id'          => $section_id,
				'title'       => sanitize_text_field( get_the_title( $section_id ) ),
				'order_index' => intval( $course_item->order_index ),
				'pricing'     => $pricing,
			);

			// Calculate totals.
			if ( $pricing['is_free'] ) {
				++$free_sections_count;
			} else {
				++$paid_sections_count;
				$total_price += floatval( $pricing['price'] );
				if ( $pricing['is_on_sale'] ) {
					$total_sale_price += floatval( $pricing['sale_price'] );
				} else {
					$total_sale_price += floatval( $pricing['price'] );
				}
			}
		}

		$response = array(
			'course_id' => $course_id,
			'sections'  => $sections,
			'totals'    => array(
				'total_sections'   => count( $sections ),
				'free_sections'    => $free_sections_count,
				'paid_sections'    => $paid_sections_count,
				'total_price'      => $total_price,
				'total_sale_price' => $total_sale_price,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Bulk update section pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function bulk_update_section_pricing( $request ) {
		// Check if feature is enabled.
		$feature_check = $this->check_feature_enabled();
		if ( is_wp_error( $feature_check ) ) {
			return $feature_check;
		}

		$course_id             = $request->get_param( 'id' );
		$price                 = floatval( $request->get_param( 'price' ) );
		$exclude_free_sections = isset( $request['exclude_free_sections'] ) ? (bool) $request['exclude_free_sections'] : true;

		$course = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		if ( $price < 0 ) {
			return new WP_Error( 'invalid_price', __( 'Price must be a positive number.', 'skillpulse-lms' ), array( 'status' => 400 ) );
		}

		// Get course sections.
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_items       = $course_items_query->get_items( $course_id );

		$updated_count    = 0;
		$skipped_count    = 0;
		$sections_updated = array();
		$sections_skipped = array();

		foreach ( $course_items as $course_item ) {
			if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
				continue;
			}

			$section_id = intval( $course_item->item_id );

			// Get current pricing.
			$current_pricing = get_post_meta( $section_id, '_splms_section_pricing', true );
			$current_pricing = is_array( $current_pricing ) ? $current_pricing : array();

			// Skip free sections if requested.
			if ( $exclude_free_sections && ! empty( $current_pricing['is_free'] ) ) {
				++$skipped_count;
				$sections_skipped[] = $section_id;
				continue;
			}

			// Update pricing.
			$pricing_data = wp_parse_args(
				array( 'price' => $price ),
				$current_pricing
			);

			update_post_meta( $section_id, '_splms_section_pricing', $pricing_data );

			++$updated_count;
			$sections_updated[] = $section_id;
		}

		$response = array(
			'success' => true,
			/* translators: %d: number of sections updated */
			'message' => sprintf( __( 'Bulk pricing applied to %d sections.', 'skillpulse-lms' ), $updated_count ),
			'data'    => array(
				'updated_count'    => $updated_count,
				'skipped_count'    => $skipped_count,
				'sections_updated' => $sections_updated,
				'sections_skipped' => $sections_skipped,
			),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Check user section access.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function check_user_section_access( $request ) {
		$user_id    = $request->get_param( 'user_id' );
		$section_id = $request->get_param( 'section_id' );

		// Check if section exists.
		$section = get_post( $section_id );
		if ( ! $section || SPLMS_POST_TYPES['section'] !== $section->post_type ) {
			return new WP_Error( 'section_not_found', __( 'Section not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Use Section Access Query class for database operations.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();
		$access_record        = $section_access_query->get_user_section_access( $user_id, $section_id );

		if ( ! $access_record ) {
			return rest_ensure_response(
				array(
					'has_access' => false,
					'section_id' => $section_id,
					'user_id'    => $user_id,
					'message'    => __( 'User does not have access to this section.', 'skillpulse-lms' ),
				)
			);
		}

		// Use standardized access check.
		$has_access = $section_access_query->user_has_access( $user_id, $section_id );

		$response = array(
			'has_access'  => $has_access,
			'access_type' => sanitize_text_field( $access_record->access_type ),
			'granted_at'  => $access_record->granted_at,
			'expires_at'  => $access_record->expires_at,
			'order_id'    => $access_record->order_id ? intval( $access_record->order_id ) : null,
			'section_id'  => $section_id,
			'user_id'     => $user_id,
			'course_id'   => intval( $access_record->course_id ),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get course pricing options.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_course_pricing_options( $request ) {
		global $wpdb;

		$course_id = $request->get_param( 'id' );
		$user_id   = $request->get_param( 'user_id' );
		$user_id   = $user_id ? intval( $user_id ) : get_current_user_id();

		$course = get_post( $course_id );

		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return new WP_Error( 'course_not_found', __( 'Course not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		// Get course settings.
		$courses_class   = SkillPulse_LMS_Courses::get_instance();
		$course_settings = $courses_class->get_course_settings( $course_id );

		$course_price        = isset( $course_settings['course_access_pricing']['price'] ) ? floatval( $course_settings['course_access_pricing']['price'] ) : 0;
		$has_section_pricing = splms_course_uses_section_pricing( $course_id );

		// Get course sections with pricing.
		$course_items_query = SkillPulse_LMS_Course_Items_Query::get_instance();
		$course_items       = $course_items_query->get_items( $course_id );

		$sections                = array();
		$sections_total_price    = 0;
		$user_purchased_sections = array();

		// Use Section Access Query class for database operations.
		$section_access_query = SkillPulse_LMS_Section_Access_Query::get_instance();

		foreach ( $course_items as $course_item ) {
			if ( SPLMS_POST_TYPES['section'] !== $course_item->item_type ) {
				continue;
			}

			$section_id   = intval( $course_item->item_id );
			$section_post = get_post( $section_id );

			if ( ! $section_post ) {
				continue;
			}

			$pricing = $this->get_section_pricing_data( $section_id );

			// Check if user has access using standardized method.
			$user_has_access = false;
			if ( $user_id ) {
				$user_has_access = $section_access_query->user_has_access( $user_id, $section_id );

				if ( $user_has_access ) {
					$user_purchased_sections[] = $section_id;
				}
			}

			$section_data = array(
				'id'              => $section_id,
				'name'            => sanitize_text_field( get_the_title( $section_id ) ),
				'is_free'         => $pricing['is_free'],
				'price'           => floatval( $pricing['price'] ),
				'user_has_access' => $user_has_access,
			);

			if ( $pricing['is_on_sale'] ) {
				$section_data['sale_price']               = floatval( $pricing['sale_price'] );
				$section_data['is_on_sale']               = true;
				$section_data['effective_price']          = floatval( $pricing['effective_price'] );
				$section_data['formatted_price']          = '$' . number_format( $pricing['effective_price'], 2 );
				$section_data['formatted_original_price'] = '$' . number_format( $pricing['price'], 2 );
			} else {
				$section_data['formatted_price'] = $pricing['is_free'] ? 'FREE' : '$' . number_format( $pricing['price'], 2 );
			}

			$sections[] = $section_data;

			// Calculate sections total.
			if ( ! $pricing['is_free'] ) {
				$sections_total_price += floatval( $pricing['effective_price'] );
			}
		}

		// Calculate bundle savings.
		$user_savings       = $course_price - $sections_total_price;
		$savings_percentage = ( 0 !== $sections_total_price ) ? ( $user_savings / $sections_total_price ) * 100 : 0;
		$savings_message    = '';

		if ( $user_savings > 0 ) {
			/* translators: %s: savings amount */
			$savings_message = sprintf( __( 'Save $%s by buying the full course!', 'skillpulse-lms' ), number_format( $user_savings, 2 ) );
		} elseif ( $user_savings < 0 ) {
			/* translators: %s: extra cost */
			$savings_message = sprintf( __( 'Buying full course costs $%s more than sections.', 'skillpulse-lms' ), number_format( abs( $user_savings ), 2 ) );
		}

		$response = array(
			'course_id'                     => $course_id,
			'has_section_pricing'           => $has_section_pricing,
			'full_course'                   => array(
				'available' => $course_price > 0,
				'price'     => $course_price,
				'formatted' => '$' . number_format( $course_price, 2 ),
			),
			'sections'                      => $sections,
			'bundle_savings'                => array(
				'sections_total'     => $sections_total_price,
				'bundle_price'       => $course_price,
				'user_savings'       => $user_savings,
				'savings_percentage' => round( $savings_percentage, 2 ),
				'message'            => $savings_message,
			),
			'user_purchased_sections'       => $user_purchased_sections,
			'user_purchased_sections_count' => count( $user_purchased_sections ),
			'remaining_sections_count'      => count( $sections ) - count( $user_purchased_sections ),
		);

		return rest_ensure_response( $response );
	}

	/**
	 * Get section pricing data.
	 *
	 * @since 1.0.0
	 *
	 * @param int $section_id Section ID.
	 * @return array Pricing data.
	 */
	private function get_section_pricing_data( $section_id ) {
		$pricing = get_post_meta( $section_id, '_splms_section_pricing', true );

		$defaults = array(
			'is_free'                   => false,
			'price'                     => 0,
			'sale_price'                => 0,
			'sale_start_date'           => '',
			'sale_end_date'             => '',
			'preview_enabled'           => false,
			'preview_items'             => array(
				'lessons'     => array(),
				'quizzes'     => array(),
				'assessments' => array(),
			),
			'requires_previous_section' => false,
		);

		$pricing = is_array( $pricing ) ? wp_parse_args( $pricing, $defaults ) : $defaults;

		// Calculate effective price and sale status.
		$is_on_sale      = false;
		$effective_price = floatval( $pricing['price'] );

		if ( $pricing['sale_price'] > 0 ) {
			$current_time = time();
			$sale_active  = true;

			// Check sale start date.
			if ( ! empty( $pricing['sale_start_date'] ) ) {
				$sale_start  = strtotime( $pricing['sale_start_date'] );
				$sale_active = $sale_active && ( $current_time >= $sale_start );
			}

			// Check sale end date.
			if ( ! empty( $pricing['sale_end_date'] ) ) {
				$sale_end    = strtotime( $pricing['sale_end_date'] );
				$sale_active = $sale_active && ( $current_time <= $sale_end );
			}

			if ( $sale_active ) {
				$is_on_sale      = true;
				$effective_price = floatval( $pricing['sale_price'] );
			}
		}

		$pricing['is_on_sale']      = $is_on_sale;
		$pricing['effective_price'] = $effective_price;
		$pricing['section_id']      = intval( $section_id );
		$pricing['currency']        = 'USD';
		$pricing['formatted_price'] = '$' . number_format( floatval( $pricing['price'] ), 2 );

		if ( $is_on_sale ) {
			$pricing['formatted_sale_price'] = '$' . number_format( floatval( $pricing['sale_price'] ), 2 );
		}

		return $pricing;
	}

	/**
	 * Get pricing schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Pricing schema.
	 */
	private function get_pricing_schema() {
		return array(
			'id'                        => array(
				'description' => __( 'Section ID.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'required'    => true,
			),
			'is_free'                   => array(
				'description' => __( 'Whether the section is free.', 'skillpulse-lms' ),
				'type'        => 'boolean',
				'default'     => false,
			),
			'price'                     => array(
				'description' => __( 'Section price.', 'skillpulse-lms' ),
				'type'        => 'number',
				'default'     => 0,
			),
			'sale_price'                => array(
				'description' => __( 'Sale price.', 'skillpulse-lms' ),
				'type'        => 'number',
				'default'     => 0,
			),
			'sale_start_date'           => array(
				'description' => __( 'Sale start date.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'sale_end_date'             => array(
				'description' => __( 'Sale end date.', 'skillpulse-lms' ),
				'type'        => 'string',
				'format'      => 'date',
			),
			'preview_enabled'           => array(
				'description' => __( 'Whether preview is enabled.', 'skillpulse-lms' ),
				'type'        => 'boolean',
				'default'     => false,
			),
			'preview_items'             => array(
				'description' => __( 'Preview items.', 'skillpulse-lms' ),
				'type'        => 'object',
				'properties'  => array(
					'lessons'     => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
					'quizzes'     => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
					'assessments' => array(
						'type'  => 'array',
						'items' => array( 'type' => 'integer' ),
					),
				),
			),
			'requires_previous_section' => array(
				'description' => __( 'Whether the previous section must be purchased first.', 'skillpulse-lms' ),
				'type'        => 'boolean',
				'default'     => false,
			),
		);
	}

	/**
	 * Permission check: Get section pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_section_pricing_permissions_check( $request ) {
		return true;
	}

	/**
	 * Permission check: Update section pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function update_section_pricing_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to update section pricing.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Permission check: Get course sections with pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_course_sections_permissions_check( $request ) {
		return true;
	}

	/**
	 * Permission check: Bulk update pricing.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function bulk_update_pricing_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to bulk update pricing.', 'skillpulse-lms' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Permission check: Check section access.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function check_section_access_permissions_check( $request ) {
		return true;
	}

	/**
	 * Permission check: Get pricing options.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_pricing_options_permissions_check( $request ) {
		return true;
	}
}
