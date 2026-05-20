<?php
/**
 * Settings REST API Controller
 *
 * Handles REST API endpoints for plugin settings management.
 * Provides endpoints for getting all settings, updating settings by tab, and retrieving user roles.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 *
 * @api
 * Available endpoints:
 * GET  /splms/v1/settings              - Get all settings
 * PUT  /splms/v1/settings/{tab}        - Update settings for specific tab
 * GET  /splms/v1/settings/user-roles   - Get available user roles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings REST API Controller class.
 *
 * @since 1.0.0
 */
class SPLMS_Rest_Settings_Controller extends WP_REST_Controller {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$this->rest_base = 'settings';
	}

	/**
	 * Register the component settings routes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'get_settings_permissions_check' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<tab>[\w-]+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_setting_by_tab' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
				'schema'              => array( $this, 'get_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/user-roles',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_user_roles' ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
			)
		);
	}

	/**
	 * Retrieve settings.
	 *
	 * Retrieves all plugin settings organized by tabs and sections.
	 * Returns comprehensive settings including general, course, payment, and integration settings.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/settings Get All Settings
	 * @apiName GetSettings
	 * @apiGroup Settings
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve all plugin settings organized by configuration tabs.
	 * Requires manage_options capability.
	 *
	 * @apiError (Error 403) splms_rest_forbidden Insufficient permissions to access settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array|WP_Error Array on success, or WP_Error object on failure.
	 */
	public function get_settings( $request ) {
		// Use centralized settings method.
		$settings = SPLMS_Settings::get_instance()->get_all_settings();

		return rest_ensure_response( $settings );
	}

	/**
	 * Check if a given request has access to list settings.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return bool|WP_Error True if request has access, WP_Error object otherwise.
	 */
	public function get_settings_permissions_check( $request ) {
		// Check if user has the capability to manage options.
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'splms_rest_forbidden',
				__( 'Sorry, you are not allowed to access settings.', 'skillpulse-lms' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$retval = true;

		/**
		 * Filter the settings `get_item` permissions check.
		 *
		 * @param bool|WP_Error   $retval  Returned value.
		 * @param WP_REST_Request $request The request sent to the API.
		 *
		 * @since 1.0.0
		 */
		return apply_filters( 'splms_rest_settings_get_item_permissions_check', $retval, $request );
	}

	/**
	 * Updates settings for the settings object.
	 *
	 * Updates settings for a specific configuration tab. Only provided settings
	 * are updated; existing settings are preserved.
	 *
	 * @since 1.0.0
	 *
	 * @api {put} /splms/v1/settings/:tab Update Settings by Tab
	 * @apiName UpdateSettingsByTab
	 * @apiGroup Settings
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Update settings for a specific configuration tab.
	 * Requires manage_options capability.
	 *
	 * @apiParam {String} tab Tab identifier (general, courses, payments, etc.).
	 * @apiParam {Object} settings Settings object to update.
	 *
	 * @apiError (Error 400) splms_rest_invalid_request Invalid request or missing tab parameter.
	 * @apiError (Error 403) splms_rest_forbidden Insufficient permissions to update settings.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array|WP_Error Array on success, or error object on failure.
	 */
	public function update_setting_by_tab( $request ) {
		$tab = $request->get_param( 'tab' );

		if ( ! isset( $tab ) || empty( $tab ) ) {
			return new WP_Error(
				'splms_rest_invalid_request',
				__( 'Invalid request. Missing tab parameter.', 'skillpulse-lms' ),
				array( 'status' => 400 )
			);
		}

		$settings_data = $request->get_json_params();

		// Use centralized settings update method.
		$result = SPLMS_Settings::get_instance()->update_tab_settings( $tab, $settings_data );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response( $result );
	}

	/**
	 * Get user roles endpoint.
	 *
	 * Retrieves a list of available WordPress user roles formatted for use
	 * in dropdowns and select fields.
	 *
	 * @since 1.0.0
	 *
	 * @api {get} /splms/v1/settings/user-roles Get User Roles
	 * @apiName GetUserRoles
	 * @apiGroup Settings
	 * @apiVersion 1.0.0
	 *
	 * @apiDescription Retrieve available WordPress user roles formatted for UI components.
	 * Requires manage_options capability.
	 *
	 * @apiError (Error 403) splms_rest_forbidden Insufficient permissions to access user roles.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_user_roles( $request ) {
		$roles           = $this->get_available_user_roles();
		$formatted_roles = array();

		// Ensure get_editable_roles() is available for getting role display names.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$editable_roles = get_editable_roles();
		foreach ( $roles as $role_key ) {
			if ( isset( $editable_roles[ $role_key ] ) ) {
				$formatted_roles[] = array(
					'value' => $role_key,
					'label' => translate_user_role( $editable_roles[ $role_key ]['name'] ),
				);
			}
		}

		// Add a default "Select a role..." option at the beginning.
		array_unshift(
			$formatted_roles,
			array(
				'value' => '',
				'label' => __( 'Select a role...', 'skillpulse-lms' ),
			)
		);

		return rest_ensure_response( $formatted_roles );
	}

	/**
	 * Get available user roles safely.
	 *
	 * @since 1.0.0
	 *
	 * @return array Available user role keys.
	 */
	private function get_available_user_roles() {
		// Ensure get_editable_roles() is available.
		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		return array_keys( get_editable_roles() );
	}

	/**
	 * Retrieves the site setting schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		// Generate schema dynamically from JSON config file.
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'splms_settings',
			'type'       => 'object',
			'properties' => $this->generate_schema_from_config(),
		);

		$this->schema = $schema;

		return $this->add_additional_fields_schema( $this->schema );
	}

	/**
	 * Generate REST API schema from JSON configuration file.
	 *
	 * @since 1.0.0
	 *
	 * @return array Schema properties array.
	 */
	private function generate_schema_from_config() {
		// Use PHP configuration loader instead of JSON.
		$config_data = SPLMS_Config_Loader::get_config( 'settings', 'api' );

		if ( ! is_array( $config_data ) || ! isset( $config_data['tabs'] ) ) {
			return array();
		}

		$properties = array();

		foreach ( $config_data['tabs'] as $tab ) {
			if ( ! isset( $tab['id'], $tab['sections'] ) || ! is_array( $tab['sections'] ) ) {
				continue;
			}

			$tab_id         = $tab['id'];
			$tab_properties = array();

			foreach ( $tab['sections'] as $section ) {
				if ( ! isset( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field ) {
					if ( ! isset( $field['id'] ) || ! isset( $field['type'] ) ) {
						continue;
					}

					// Skip info fields as they're not actual settings.
					if ( 'info' === $field['type'] ) {
						continue;
					}

					$field_schema = $this->convert_field_to_schema( $field );
					if ( $field_schema ) {
						$tab_properties[ $field['id'] ] = $field_schema;
					}
				}
			}

			if ( ! empty( $tab_properties ) ) {
				$properties[ $tab_id ] = array(
					'type'        => 'object',
					// translators: %s: The tab title or tab ID.
					'description' => isset( $tab['description'] ) ? $tab['description'] : sprintf( __( '%s settings', 'skillpulse-lms' ), $tab['title'] ?? ucfirst( $tab_id ) ),
					'properties'  => $tab_properties,
				);
			}
		}

		return $properties;
	}

	/**
	 * Convert a field configuration to REST API schema format.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field configuration from JSON.
	 *
	 * @return array|null Schema definition or null if invalid.
	 */
	private function convert_field_to_schema( $field ) {
		$schema = array(
			'description' => $field['description'] ?? $field['label'] ?? '',
		);

		// Set default value if provided.
		if ( array_key_exists( 'default', $field ) ) {
			$schema['default'] = $field['default'];
		}

		// Convert field type to schema type.
		switch ( $field['type'] ) {
			case 'toggle':
			case 'checkbox':
				$schema['type'] = 'boolean';
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = false;
				}
				break;

			case 'number':
			case 'range':
				$schema['type'] = 'integer';
				if ( isset( $field['min'] ) ) {
					$schema['minimum'] = intval( $field['min'] );
				}
				if ( isset( $field['max'] ) ) {
					$schema['maximum'] = intval( $field['max'] );
				}
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = 0;
				}
				break;

			case 'select':
			case 'radio':
				$schema['type'] = 'string';
				if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
					$enum_values = array();
					foreach ( $field['options'] as $option ) {
						if ( is_array( $option ) && isset( $option['value'] ) ) {
							$enum_values[] = $option['value'];
						}
					}
					if ( ! empty( $enum_values ) ) {
						$schema['enum'] = $enum_values;
					}
				}
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = '';
				}
				break;

			case 'multi-select':
			case 'checkbox-group':
				$schema['type'] = 'array';
				if ( isset( $field['options'] ) && is_array( $field['options'] ) ) {
					$enum_values = array();
					foreach ( $field['options'] as $option ) {
						if ( is_array( $option ) && isset( $option['value'] ) ) {
							$enum_values[] = $option['value'];
						}
					}
					if ( ! empty( $enum_values ) ) {
						$schema['items'] = array(
							'type' => 'string',
							'enum' => $enum_values,
						);
					}
				}
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = array();
				}
				break;

			case 'search_select':
			case 'page_select':
				$schema['type']    = 'integer';
				$schema['minimum'] = 0;
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = 0;
				}
				break;

			case 'email':
				$schema['type']   = 'string';
				$schema['format'] = 'email';
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = '';
				}
				break;

			case 'url':
				$schema['type']   = 'string';
				$schema['format'] = 'uri';
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = '';
				}
				break;

			case 'text':
			case 'textarea':
			default:
				$schema['type'] = 'string';
				if ( ! isset( $schema['default'] ) ) {
					$schema['default'] = '';
				}
				break;
		}

		// Add readonly property if field is disabled.
		if ( isset( $field['disabled'] ) && $field['disabled'] ) {
			$schema['readonly'] = true;
		}

		return $schema;
	}
}
