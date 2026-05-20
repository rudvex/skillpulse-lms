<?php
/**
 * Quizzes REST API Controller
 *
 * Handles REST API endpoints for quiz management and quiz attempts.
 * Provides endpoints for CRUD operations, quiz attempts, submissions, and results.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/quizzes                - List quizzes
 * POST   /splms/v1/quizzes                - Create quiz
 * GET    /splms/v1/quizzes/{id}           - Get single quiz
 * PUT    /splms/v1/quizzes/{id}           - Update quiz
 * DELETE /splms/v1/quizzes/{id}           - Delete quiz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quizzes REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_REST_Quiz_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'quizzes';
	}

	/**
	 * Register the quiz routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get quizzes.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quizzes' ),
				'permission_callback' => array( $this, 'get_quizzes_permissions_check' ),
				'args'                => $this->get_collection_params(),
				'schema'              => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get single quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quiz' ),
				'permission_callback' => array( $this, 'get_quiz_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
				'schema'              => array( $this, 'get_public_item_schema' ),
			)
		);

		// Create quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_quiz' ),
				'permission_callback' => array( $this, 'create_quiz_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			)
		);

		// Update quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_quiz' ),
				'permission_callback' => array( $this, 'update_quiz_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			)
		);

		// Delete quiz.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_quiz' ),
				'permission_callback' => array( $this, 'delete_quiz_permissions_check' ),
				'args'                => array(
					'id' => array(
						'description' => __( 'Quiz ID', 'skillpulse-lms' ),
						'type'        => 'integer',
						'required'    => true,
					),
				),
			)
		);
	}

	/**
	 * Get quizzes.
	 *
	 * Retrieves a collection of quizzes with optional filtering by course,
	 * search, and pagination support.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/quizzes List Quizzes
	 * @apiName GetQuizzes
	 * @apiGroup Quizzes
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve a collection of quizzes. Supports filtering by course,
	 * search, and pagination. Requires edit_posts capability.
	 *
	 * @apiParam {Number} [page=1] Current page of the collection.
	 * @apiParam {Number} [per_page=10] Maximum number of items to be returned.
	 * @apiParam {String} [search] Limit results to those matching a string.
	 * @apiParam {Number} [course_id] Filter quizzes by course ID.
	 * @apiParam {String} [orderby=date] Sort collection by attribute.
	 * @apiParam {String} [order=desc] Order sort attribute ascending or descending.
	 *
	 * @apiError (Error 403) rest_forbidden Insufficient permissions to access quizzes.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quizzes( $request ) {
		$args = array(
			'post_type'      => SPLMS_POST_TYPES['quiz'],
			'post_status'    => 'any',
			'posts_per_page' => $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10,
			'paged'          => $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1,
		);

		// Filter by course.
		if ( $request->get_param( 'course_id' ) ) {
			$args['post_parent'] = $request->get_param( 'course_id' );
		}

		// Search functionality.
		if ( $request->get_param( 'search' ) ) {
			$args['s'] = $request->get_param( 'search' );
		}

		// Order by.
		if ( $request->get_param( 'orderby' ) ) {
			$args['orderby'] = $request->get_param( 'orderby' );
		}

		// Order direction.
		if ( $request->get_param( 'order' ) ) {
			$args['order'] = $request->get_param( 'order' );
		}

		$quizzes_query = new WP_Query( $args );
		$quizzes       = array();

		if ( $quizzes_query->have_posts() ) {
			while ( $quizzes_query->have_posts() ) {
				$quizzes_query->the_post();
				$quiz = get_post();

				$quizzes[] = $this->prepare_response_for_collection(
					$this->prepare_quiz_for_response( $quiz, $request )
				);
			}
		}

		wp_reset_postdata();

		$response = rest_ensure_response( $quizzes );
		$response->header( 'X-WP-Total', (int) $quizzes_query->found_posts );
		$response->header( 'X-WP-TotalPages', (int) $quizzes_query->max_num_pages );

		/**
		 * Fires after a list of quizzes response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_quiz_items_response', $response, $request );

		return $response;
	}

	/**
	 * Get single quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_quiz( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_quiz_for_response( $quiz, $request );

		/**
		 * Fires after a quiz response is prepared via the REST API.
		 *
		 * @param WP_REST_Response $response The response data.
		 * @param WP_Post          $quiz     Quiz post object.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( 'splms_rest_quiz_item_response', $response, $quiz, $request );

		return $response;
	}

	/**
	 * Create quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_quiz( $request ) {
		$quiz_data = array(
			'post_type'    => SPLMS_POST_TYPES['quiz'],
			'post_title'   => $request->get_param( 'title' ),
			'post_content' => $request->get_param( 'content' ),
			'post_status'  => $request->get_param( 'status' ) ? $request->get_param( 'status' ) : 'draft',
			'post_parent'  => $request->get_param( 'course_id' ) ? $request->get_param( 'course_id' ) : 0,
		);

		$quiz_id = wp_insert_post( $quiz_data );

		if ( is_wp_error( $quiz_id ) ) {
			return new WP_Error( 'quiz_creation_failed', __( 'Failed to create quiz.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Save quiz meta.
		$this->save_quiz_meta( $quiz_id, $request );

		$quiz     = get_post( $quiz_id );
		$response = $this->prepare_quiz_for_response( $quiz, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Update quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_quiz( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$quiz_data = array(
			'ID' => $quiz_id,
		);

		if ( $request->get_param( 'title' ) ) {
			$quiz_data['post_title'] = $request->get_param( 'title' );
		}

		if ( $request->get_param( 'content' ) ) {
			$quiz_data['post_content'] = $request->get_param( 'content' );
		}

		if ( $request->get_param( 'status' ) ) {
			$quiz_data['post_status'] = $request->get_param( 'status' );
		}

		if ( $request->get_param( 'course_id' ) ) {
			$quiz_data['post_parent'] = $request->get_param( 'course_id' );
		}

		$result = wp_update_post( $quiz_data );

		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'quiz_update_failed', __( 'Failed to update quiz.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		// Save quiz meta.
		$this->save_quiz_meta( $quiz_id, $request );

		$updated_quiz = get_post( $quiz_id );
		$response     = $this->prepare_quiz_for_response( $updated_quiz, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Delete quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function delete_quiz( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$quiz    = get_post( $quiz_id );

		if ( ! $quiz || SPLMS_POST_TYPES['quiz'] !== $quiz->post_type ) {
			return new WP_Error( 'quiz_not_found', __( 'Quiz not found.', 'skillpulse-lms' ), array( 'status' => 404 ) );
		}

		$result = wp_delete_post( $quiz_id, true );

		if ( ! $result ) {
			return new WP_Error( 'quiz_deletion_failed', __( 'Failed to delete quiz.', 'skillpulse-lms' ), array( 'status' => 500 ) );
		}

		return rest_ensure_response( array( 'success' => true ) );
	}


	/**
	 * Save quiz meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int             $quiz_id Quiz ID.
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return void
	 */
	private function save_quiz_meta( $quiz_id, $request ) {
		$meta_fields = array(
			'quiz_questions',
			'quiz_settings',
			'quiz_type',
			'time_limit',
			'passing_grade',
			'max_attempts',
		);

		foreach ( $meta_fields as $field ) {
			if ( $request->get_param( $field ) !== null ) {
				update_post_meta( $quiz_id, '_splms_' . $field, $request->get_param( $field ) );
			}
		}
	}

	/**
	 * Prepare quiz for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $quiz    Quiz post object.
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_quiz_for_response( $quiz, $request ) {
		$GLOBALS['post'] = $quiz; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		setup_postdata( $quiz );

		$is_single_request = ! empty( $request->get_param( 'id' ) ) && absint( $request->get_param( 'id' ) ) === $quiz->ID;
		$user_id           = is_user_logged_in() ? get_current_user_id() : 0;
		$quiz_id           = $quiz->ID;

		// Get quiz settings (always needed for basic quiz info).
		$quiz_settings = splms_get_quiz_settings( $quiz_id );

		// Base fields for every quiz.
		$data = array(
			'id'             => $quiz_id,
			'title'          => array(
				'raw'      => $quiz->post_title,
				'rendered' => get_the_title( $quiz_id ),
			),
			'slug'           => $quiz->post_name,
			'link'           => get_permalink( $quiz_id ),
			'date'           => mysql2date( 'c', $quiz->post_date, false ),
			'date_gmt'       => mysql2date( 'c', $quiz->post_date_gmt, false ),
			'modified'       => mysql2date( 'c', $quiz->post_modified, false ),
			'modified_gmt'   => mysql2date( 'c', $quiz->post_modified_gmt, false ),
			'status'         => $quiz->post_status,
			'type'           => $quiz->post_type,
			'featured_media' => (int) get_post_thumbnail_id( $quiz_id ),
			'excerpt'        => array(
				'raw'      => $quiz->post_excerpt,
				'rendered' => apply_filters( 'the_excerpt', $quiz->post_excerpt ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
			),
			'course_id'      => (int) $quiz->post_parent,
		);

		// Add content (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			$data['content'] = array(
				'raw'      => $quiz->post_content,
				'rendered' => apply_filters( 'the_content', $quiz->post_content ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core filter.
			);
			// Add block_version if function exists (WordPress core function).
			if ( function_exists( 'block_version' ) ) {
				$data['content']['block_version'] = block_version( $quiz->post_content );
			}
		}

		// Quiz type and basic settings (always include).
		$data['quiz_type']          = isset( $quiz_settings['quiz_type'] ) ? $quiz_settings['quiz_type'] : 'graded';
		$data['time_limit']         = isset( $quiz_settings['time_limit'] ) ? $quiz_settings['time_limit'] : 0;
		$data['time_limit_enabled'] = isset( $quiz_settings['time_limit_enabled'] ) ? $quiz_settings['time_limit_enabled'] : false;
		$data['passing_grade']      = isset( $quiz_settings['passing_grade'] ) ? $quiz_settings['passing_grade'] : 70;
		$data['max_attempts']       = isset( $quiz_settings['max_attempts'] ) ? $quiz_settings['max_attempts'] : 0;

		// Question count (always include).
		$questions_query        = SPLMS_Quiz_Questions_Query::get_instance();
		$question_count         = $questions_query->get_questions_count( $quiz_id );
		$data['question_count'] = false !== $question_count ? $question_count : 0;

		// User-specific fields.
		if ( $user_id > 0 ) {
			// Get user's best score if available.
			if ( function_exists( 'splms_get_user_best_quiz_score' ) ) {
				$best_score         = splms_get_user_best_quiz_score( $user_id, $quiz_id );
				$data['best_score'] = $best_score ? array(
					'score'        => floatval( isset( $best_score['score'] ) ? $best_score['score'] : 0 ),
					'total_points' => floatval( isset( $best_score['total_points'] ) ? $best_score['total_points'] : 0 ),
					'percentage'   => floatval( isset( $best_score['percentage'] ) ? $best_score['percentage'] : 0 ),
					'passed'       => (bool) ( isset( $best_score['passed'] ) ? $best_score['passed'] : false ),
				) : null;
			} else {
				$data['best_score'] = null;
			}
		} else {
			$data['best_score'] = null;
		}

		// Additional detailed fields (only for single requests - will be filtered by context in schema).
		if ( $is_single_request ) {
			// Author/Instructor information.
			$author         = get_userdata( $quiz->post_author );
			$data['author'] = array(
				'id'         => (int) $quiz->post_author,
				'name'       => $author ? $author->display_name : '',
				'slug'       => $author ? $author->user_nicename : '',
				'avatar_url' => $author ? get_avatar_url( $quiz->post_author, array( 'size' => 96 ) ) : '',
			);

			// Additional quiz settings.
			$data['show_correct_answers'] = isset( $quiz_settings['show_correct_answers'] ) ? $quiz_settings['show_correct_answers'] : false;
			$data['randomize_questions']  = isset( $quiz_settings['randomize_questions'] ) ? $quiz_settings['randomize_questions'] : false;
			$data['randomize_answers']    = isset( $quiz_settings['randomize_answers'] ) ? $quiz_settings['randomize_answers'] : false;
			$data['questions_per_page']   = isset( $quiz_settings['questions_per_page'] ) ? $quiz_settings['questions_per_page'] : 0;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		// Add links.
		$response->add_links( $this->prepare_links( $quiz ) );

		/**
		 * Filters quiz response.
		 *
		 * @param WP_REST_Response $response Rest response.
		 * @param WP_Post          $quiz     Quiz post object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'splms_rest_prepare_quiz', $response, $quiz, $request );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $quiz Quiz post object.
	 * @return array Links for the given quiz.
	 */
	protected function prepare_links( $quiz ) {
		$links = array(
			'self'       => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $quiz->ID ),
				),
			),
			'collection' => array(
				array(
					'href' => rest_url( $this->namespace . '/' . $this->rest_base ),
				),
			),
		);

		// Add course link if quiz has a parent course.
		if ( $quiz->post_parent > 0 ) {
			$links['course'] = array(
				array(
					'href'       => rest_url( splms_rest_namespace() . '/' . splms_rest_version() . '/courses/' . $quiz->post_parent ),
					'embeddable' => true,
				),
			);
		}

		// Add action links.
		$links['attempts'] = array(
			array(
				'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $quiz->ID . '/attempts' ),
			),
		);
		$links['results']  = array(
			array(
				'href' => rest_url( $this->namespace . '/' . $this->rest_base . '/' . $quiz->ID . '/results' ),
			),
		);

		return $links;
	}

	/**
	 * Get the query params for collections.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'page'      => array(
				'description' => __( 'Current page of the collection.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 1,
				'minimum'     => 1,
			),
			'per_page'  => array(
				'description' => __( 'Maximum number of items to be returned in result set.', 'skillpulse-lms' ),
				'type'        => 'integer',
				'default'     => 10,
				'minimum'     => 1,
				'maximum'     => 100,
			),
			'search'    => array(
				'description' => __( 'Limit results to those matching a string.', 'skillpulse-lms' ),
				'type'        => 'string',
			),
			'course_id' => array(
				'description' => __( 'Limit results to quizzes belonging to a specific course.', 'skillpulse-lms' ),
				'type'        => 'integer',
			),
			'orderby'   => array(
				'description' => __( 'Sort collection by object attribute.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'date',
				'enum'        => array( 'date', 'title', 'menu_order' ),
			),
			'order'     => array(
				'description' => __( 'Order sort attribute ascending or descending.', 'skillpulse-lms' ),
				'type'        => 'string',
				'default'     => 'desc',
				'enum'        => array( 'asc', 'desc' ),
			),
		);
	}

	/**
	 * Retrieves the quiz schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return $this->get_public_item_schema();
	}

	/**
	 * Retrieves the public quiz schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'quiz',
			'type'       => 'object',
			'properties' => array(
				'id'                   => array(
					'description' => __( 'Unique identifier for the quiz.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'title'                => array(
					'description' => __( 'The title for the quiz.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Title for the quiz, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML title for the quiz, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'slug'                 => array(
					'description' => __( 'An alphanumeric identifier for the quiz unique to its type.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'link'                 => array(
					'description' => __( 'URL to the quiz.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'date'                 => array(
					'description' => __( "The date the quiz was published, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'date_gmt'             => array(
					'description' => __( 'The date the quiz was published, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
				),
				'modified'             => array(
					'description' => __( "The date the quiz was last modified, in the site's timezone.", 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'modified_gmt'         => array(
					'description' => __( 'The date the quiz was last modified, as GMT.', 'skillpulse-lms' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'status'               => array(
					'description' => __( 'A named status for the quiz.', 'skillpulse-lms' ),
					'type'        => 'string',
					'enum'        => array( 'publish', 'future', 'draft', 'pending', 'private' ),
					'context'     => array( 'view', 'edit' ),
				),
				'type'                 => array(
					'description' => __( 'Type of post for the quiz.', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'featured_media'       => array(
					'description' => __( 'The ID of the featured media for the quiz.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'excerpt'              => array(
					'description' => __( 'The excerpt for the quiz.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view', 'edit' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Excerpt for the quiz, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'edit' ),
						),
						'rendered' => array(
							'description' => __( 'HTML excerpt for the quiz, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
				'content'              => array(
					'description' => __( 'The content for the quiz.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'raw'      => array(
							'description' => __( 'Content for the quiz, as it exists in the database.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
						'rendered' => array(
							'description' => __( 'HTML content for the quiz, transformed for display.', 'skillpulse-lms' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
						),
					),
					'readonly'    => true,
				),
				'course_id'            => array(
					'description' => __( 'The ID of the parent course.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
				),
				'quiz_type'            => array(
					'description' => __( 'The type of quiz (graded, practice, survey).', 'skillpulse-lms' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'time_limit'           => array(
					'description' => __( 'Time limit for the quiz in minutes.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'time_limit_enabled'   => array(
					'description' => __( 'Whether time limit is enabled for the quiz.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'passing_grade'        => array(
					'description' => __( 'Passing grade percentage for the quiz.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'max_attempts'         => array(
					'description' => __( 'Maximum number of attempts allowed.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'question_count'       => array(
					'description' => __( 'Number of questions in the quiz.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'best_score'           => array(
					'description' => __( 'User\'s best score for this quiz (only for authenticated users).', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'score'        => array(
							'type'        => 'number',
							'description' => __( 'Score achieved.', 'skillpulse-lms' ),
						),
						'total_points' => array(
							'type'        => 'number',
							'description' => __( 'Total points possible.', 'skillpulse-lms' ),
						),
						'percentage'   => array(
							'type'        => 'number',
							'description' => __( 'Score percentage.', 'skillpulse-lms' ),
						),
						'passed'       => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the quiz was passed.', 'skillpulse-lms' ),
						),
					),
					'readonly'    => true,
				),
				'author'               => array(
					'description' => __( 'Quiz author/instructor information.', 'skillpulse-lms' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'id'         => array(
							'type'        => 'integer',
							'description' => __( 'Author user ID.', 'skillpulse-lms' ),
						),
						'name'       => array(
							'type'        => 'string',
							'description' => __( 'Author display name.', 'skillpulse-lms' ),
						),
						'slug'       => array(
							'type'        => 'string',
							'description' => __( 'Author user slug.', 'skillpulse-lms' ),
						),
						'avatar_url' => array(
							'type'        => 'string',
							'format'      => 'uri',
							'description' => __( 'Author avatar URL.', 'skillpulse-lms' ),
						),
					),
					'readonly'    => true,
				),
				'show_correct_answers' => array(
					'description' => __( 'Whether to show correct answers after quiz completion.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'randomize_questions'  => array(
					'description' => __( 'Whether to randomize question order.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'randomize_answers'    => array(
					'description' => __( 'Whether to randomize answer order.', 'skillpulse-lms' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'questions_per_page'   => array(
					'description' => __( 'Number of questions to show per page.', 'skillpulse-lms' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Check permissions for getting quizzes.
	 *
	 * Allows open access by default. Use the 'splms_rest_quizzes_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_quizzes_permissions_check( $request ) {
		$user_id = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter quizzes list access permission check.
		 *
		 * Allows developers to add custom access restrictions for the quizzes list endpoint.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_quizzes_permissions_check', $has_access, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Check permissions for getting single quiz.
	 *
	 * Allows open access by default. Use the 'splms_rest_quiz_permissions_check' filter
	 * to add custom access restrictions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has access, WP_Error object otherwise.
	 */
	public function get_quiz_permissions_check( $request ) {
		$quiz_id = $request->get_param( 'id' );
		$user_id = get_current_user_id();

		// Default: allow open access.
		$has_access = true;

		/**
		 * Filter quiz access permission check.
		 *
		 * Allows developers to add custom access restrictions for quizzes.
		 * Return true to allow access, false or WP_Error to deny access.
		 *
		 * @since 1.0.0
		 *
		 * @param bool|WP_Error $has_access Whether the user has access. Default true (open access).
		 * @param int            $quiz_id    Quiz ID.
		 * @param int            $user_id    User ID (0 for non-logged-in users).
		 * @param WP_REST_Request $request   Request object.
		 */
		$has_access = apply_filters( 'splms_rest_quiz_permissions_check', $has_access, $quiz_id, $user_id, $request );

		// If filter returns WP_Error, return it directly.
		if ( is_wp_error( $has_access ) ) {
			return $has_access;
		}

		// If filter returns false, deny access.
		if ( false === $has_access ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		// Allow access (default behavior).
		return true;
	}

	/**
	 * Check if a given request has access to create a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function create_quiz_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to update a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has edit_posts capability, false otherwise.
	 */
	public function update_quiz_permissions_check( $request ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to perform this action.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check if a given request has access to delete a quiz.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool True if user has delete_posts capability, false otherwise.
	 */
	public function delete_quiz_permissions_check( $request ) {
		if ( ! current_user_can( 'delete_posts' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to delete this resource.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}
}
