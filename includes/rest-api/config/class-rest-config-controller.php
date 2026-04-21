<?php
/**
 * Configuration REST API Controller
 *
 * Handles REST API endpoints for configuration data management.
 * Provides on-demand configuration loading to replace heavy wp_localize_script usage.
 *
 * @package SkillPulse_LMS
 * @since   1.0.0
 *
 * @api
 * Available endpoints:
 * GET    /splms/v1/config                  - Get all configurations
 * GET    /splms/v1/config/{module}         - Get specific module configuration
 * GET    /splms/v1/config/{module}/{section} - Get specific section configuration
 * DELETE /splms/v1/config/cache           - Clear configuration cache
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configuration REST API Controller class.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Rest_Config_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'config';
	}

	/**
	 * Register the component config routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		// Get all available modules config.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_configs' ),
					'permission_callback' => array( $this, 'get_configs_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);

		// Get specific module config.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<module>[\w-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_module_config' ),
					'permission_callback' => array( $this, 'get_module_config_permissions_check' ),
					'args'                => $this->get_module_config_params(),
				),
			)
		);

		// Get specific section from module.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<module>[\w-]+)/(?P<section>[\w-]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_section_config' ),
					'permission_callback' => array( $this, 'get_section_config_permissions_check' ),
					'args'                => $this->get_section_config_params(),
				),
			)
		);

		// Clear configuration cache.
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/cache',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'clear_config_cache' ),
					'permission_callback' => array( $this, 'clear_cache_permissions_check' ),
					'args'                => array(
						'module' => array(
							'description' => __( 'Specific module to clear cache for', 'skillpulse-lms' ),
							'type'        => 'string',
							'required'    => false,
						),
					),
				),
			)
		);
	}

	/**
	 * Get all configurations or filtered configurations.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_configs( $request ) {
		$modules       = $request->get_param( 'modules' );
		$context_param = $request->get_param( 'context' );
		$context       = ! empty( $context_param ) ? $context_param : 'admin';

		if ( empty( $modules ) ) {
			$modules = SkillPulse_LMS_Config_Loader::get_available_modules();
		} else {
			$modules = explode( ',', $modules );
		}

		$configs = array();

		foreach ( $modules as $module ) {
			$module = trim( $module );

			if ( ! SkillPulse_LMS_Config_Loader::module_exists( $module ) ) {
				continue;
			}

			$configs[ $module ] = SkillPulse_LMS_Config_Loader::get_config( $module, $context );
		}

		$response_data = array(
			'modules' => $configs,
			'context' => $context,
			'cached'  => true,
		);

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Get configuration for specific module.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_module_config( $request ) {
		$module        = $request->get_param( 'module' );
		$context_param = $request->get_param( 'context' );
		$context       = ! empty( $context_param ) ? $context_param : 'admin';
		$item_id       = $request->get_param( 'item_id' );

		if ( ! SkillPulse_LMS_Config_Loader::module_exists( $module ) ) {
			return new WP_Error(
				'module_not_found',
				sprintf(
					/* translators: %s: Module name */
					__( 'Configuration module "%s" not found', 'skillpulse-lms' ),
					$module
				),
				array( 'status' => 404 )
			);
		}

		// Set item_id in $_REQUEST for config file access.
		if ( ! empty( $item_id ) && is_numeric( $item_id ) ) {
			$_REQUEST['item_id'] = intval( $item_id );
		}

		$config = SkillPulse_LMS_Config_Loader::get_config( $module, $context );

		$response_data = array(
			'module'  => $module,
			'config'  => $config,
			'context' => $context,
			'item_id' => $item_id,
		);

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Get configuration for specific section within module.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function get_section_config( $request ) {
		$module        = $request->get_param( 'module' );
		$section       = $request->get_param( 'section' );
		$context_param = $request->get_param( 'context' );
		$context       = ! empty( $context_param ) ? $context_param : 'admin';

		if ( ! SkillPulse_LMS_Config_Loader::module_exists( $module ) ) {
			return new WP_Error(
				'module_not_found',
				sprintf(
					/* translators: %s: Module name */
					__( 'Configuration module "%s" not found', 'skillpulse-lms' ),
					$module
				),
				array( 'status' => 404 )
			);
		}

		$full_config    = SkillPulse_LMS_Config_Loader::get_config( $module, $context );
		$section_config = $this->extract_section_config( $full_config, $section );

		if ( empty( $section_config ) ) {
			return new WP_Error(
				'section_not_found',
				sprintf(
					/* translators: %1$s: Section name, %2$s: Module name */
					__( 'Configuration section "%1$s" not found in module "%2$s"', 'skillpulse-lms' ),
					$section,
					$module
				),
				array( 'status' => 404 )
			);
		}

		$response_data = array(
			'module'  => $module,
			'section' => $section,
			'config'  => $section_config,
			'context' => $context,
		);

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Clear configuration cache.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object.
	 */
	public function clear_config_cache( $request ) {
		$module = $request->get_param( 'module' );

		SkillPulse_LMS_Config_Loader::clear_cache( $module );

		$response_data = array(
			'message' => $module ?
				sprintf(
					/* translators: %s: Module name */
					__( 'Configuration cache cleared for module "%s"', 'skillpulse-lms' ),
					$module
				) :
				__( 'All configuration cache cleared', 'skillpulse-lms' ),
			'module'  => $module,
		);

		return new WP_REST_Response( $response_data, 200 );
	}

	/**
	 * Extract specific section from configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $config     Full configuration.
	 * @param string $section_id Section ID to extract.
	 * @return array Section configuration or empty array.
	 */
	private function extract_section_config( $config, $section_id ) {
		if ( isset( $config['sections'] ) ) {
			foreach ( $config['sections'] as $section ) {
				if ( isset( $section['id'] ) && $section['id'] === $section_id ) {
					return $section;
				}
			}
		}

		return array();
	}

	/**
	 * Check permissions for getting configurations.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if permitted, WP_Error otherwise.
	 */
	public function get_configs_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to access configurations.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Check permissions for getting module configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if permitted, WP_Error otherwise.
	 */
	public function get_module_config_permissions_check( $request ) {
		return $this->get_configs_permissions_check( $request );
	}

	/**
	 * Check permissions for getting section configuration.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if permitted, WP_Error otherwise.
	 */
	public function get_section_config_permissions_check( $request ) {
		return $this->get_configs_permissions_check( $request );
	}

	/**
	 * Check permissions for clearing cache.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool|WP_Error True if permitted, WP_Error otherwise.
	 */
	public function clear_cache_permissions_check( $request ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'Sorry, you are not allowed to clear configuration cache.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Get collection parameters for configurations endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'modules' => array(
				'description' => __( 'Comma-separated list of modules to retrieve', 'skillpulse-lms' ),
				'type'        => 'string',
				'required'    => false,
			),
			'context' => array(
				'description' => __( 'Configuration context (admin, editor, frontend, api)', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'admin', 'editor', 'frontend', 'api' ),
				'default'     => 'admin',
				'required'    => false,
			),
		);
	}

	/**
	 * Get parameters for module config endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return array Module config parameters.
	 */
	public function get_module_config_params() {
		return array(
			'context' => array(
				'description' => __( 'Configuration context (admin, editor, frontend, api)', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'admin', 'editor', 'frontend', 'api' ),
				'default'     => 'admin',
				'required'    => false,
			),
			'item_id' => array(
				'description' => __( 'Post/Item ID for item-specific configuration values', 'skillpulse-lms' ),
				'type'        => 'integer',
				'minimum'     => 1,
				'required'    => false,
			),
		);
	}

	/**
	 * Get parameters for section config endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return array Section config parameters.
	 */
	public function get_section_config_params() {
		return array(
			'context' => array(
				'description' => __( 'Configuration context (admin, editor, frontend, api)', 'skillpulse-lms' ),
				'type'        => 'string',
				'enum'        => array( 'admin', 'editor', 'frontend', 'api' ),
				'default'     => 'admin',
				'required'    => false,
			),
		);
	}
}
