<?php
/**
 * Configuration Loader Class
 *
 * Handles loading and caching of PHP configuration files to replace heavy JSON configs
 * and wp_localize_script performance issues.
 *
 * @package SPLMS
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Class SPLMS_Config_Loader
 *
 * Provides smart configuration loading with caching and on-demand loading capabilities.
 *
 * @since 1.0.0
 */
class SPLMS_Config_Loader {

	/**
	 * Configuration cache array.
	 *
	 * @since 1.0.0
	 * @var array $cache
	 */
	private static $cache = array();

	/**
	 * Available configuration modules.
	 *
	 * @since 1.0.0
	 * @var array $modules
	 */
	private static $modules = array(
		'courses',
		'lessons',
		'sections',
		'quizzes',
		'settings',
	);

	/**
	 * Get configuration for specific module.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module  Module name (courses, lessons, etc.).
	 * @param string $context Context (admin, editor, frontend).
	 * @return array Configuration array.
	 */
	public static function get_config( $module, $context = 'admin' ) {
		$cache_key = $module . '_' . $context;

		if ( ! isset( self::$cache[ $cache_key ] ) ) {
			self::$cache[ $cache_key ] = self::load_module_config( $module, $context );
		}

		return self::$cache[ $cache_key ];
	}


	/**
	 * Get configuration for REST API endpoints.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module     Module name.
	 * @param array  $request_params Request parameters for filtering.
	 * @return array Configuration data for API response.
	 */
	public static function get_api_config( $module, $request_params = array() ) {
		$config = self::get_config( $module, 'api' );

		if ( ! empty( $request_params['section'] ) ) {
			return self::filter_config_by_section( $config, $request_params['section'] );
		}

		return $config;
	}

	/**
	 * Load configuration from PHP file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module  Module name.
	 * @param string $context Context.
	 * @return array Configuration array.
	 */
	private static function load_module_config( $module, $context ) {
		$config_file = self::get_config_file_path( $module );

		if ( ! file_exists( $config_file ) ) {
			return array();
		}

		$config = include $config_file;

		if ( ! is_array( $config ) ) {
			return array();
		}

		// Process config based on context.
		$processed_config = self::process_config_for_context( $config, $context );

		/**
		 * Filters the loaded configuration for a module.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $processed_config The processed configuration.
		 * @param string $module           Module name.
		 * @param string $context          Context.
		 */
		return apply_filters( 'splms_config_loaded', $processed_config, $module, $context );
	}

	/**
	 * Get configuration file path.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module Module name.
	 * @return string Configuration file path.
	 */
	private static function get_config_file_path( $module ) {
		// Map settings to core module.
		$actual_module = 'settings' === $module ? 'core' : $module;

		// Map questions/question-builder to quizzes module's question-builder-config.php.
		if ( 'questions' === $module || 'question-builder' === $module ) {
			$question_config = SPLMS_DIR_PATH . 'includes/modules/quizzes/question-builder-config.php';
			if ( file_exists( $question_config ) ) {
				return $question_config;
			}
		}

		// Check for module-specific config first.
		$module_config = SPLMS_DIR_PATH . 'includes/modules/' . $actual_module . '/config.php';
		if ( file_exists( $module_config ) ) {
			return $module_config;
		}

		return '';
	}

	/**
	 * Process configuration based on context.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $config  Raw configuration.
	 * @param string $context Context (admin, editor, frontend, api).
	 * @return array Processed configuration.
	 */
	private static function process_config_for_context( $config, $context ) {
		switch ( $context ) {
			case 'editor':
				return self::process_editor_config( $config );

			case 'frontend':
				return self::process_frontend_config( $config );

			case 'api':
				return self::process_api_config( $config );

			case 'admin':
			default:
				return $config;
		}
	}

	/**
	 * Process configuration for editor context.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Raw configuration.
	 * @return array Processed configuration for editor.
	 */
	private static function process_editor_config( $config ) {
		// Remove unnecessary data for editor context.
		if ( isset( $config['sections'] ) ) {
			foreach ( $config['sections'] as &$section ) {
				if ( isset( $section['fields'] ) ) {
					foreach ( $section['fields'] as &$field ) {
						// Remove heavy data not needed in editor.
						unset( $field['tooltip'] );
						unset( $field['help'] );
					}
				}
			}
		}

		return $config;
	}

	/**
	 * Process configuration for frontend context.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Raw configuration.
	 * @return array Processed configuration for frontend.
	 */
	private static function process_frontend_config( $config ) {
		// Only return public-facing configuration.
		$frontend_config = array();

		if ( isset( $config['sections'] ) ) {
			foreach ( $config['sections'] as $section ) {
				if ( isset( $section['public'] ) && $section['public'] ) {
					$frontend_config['sections'][] = $section;
				}
			}
		}

		return $frontend_config;
	}

	/**
	 * Process configuration for API context.
	 *
	 * @since 1.0.0
	 *
	 * @param array $config Raw configuration.
	 * @return array Processed configuration for API.
	 */
	private static function process_api_config( $config ) {
		// Add API metadata and sanitize sensitive data.
		if ( isset( $config['sections'] ) ) {
			foreach ( $config['sections'] as &$section ) {
				if ( isset( $section['fields'] ) ) {
					foreach ( $section['fields'] as &$field ) {
						// Remove sensitive default values and current values for API.
						if ( 'password' === $field['type'] ) {
							$field['default'] = '';
							if ( isset( $field['value'] ) ) {
								$field['value'] = '';
							}
						}
					}
				}
			}
		}

		return $config;
	}

	/**
	 * Filter configuration by section.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $config  Full configuration.
	 * @param string $section_id Section ID to filter by.
	 * @return array Filtered configuration.
	 */
	private static function filter_config_by_section( $config, $section_id ) {
		if ( isset( $config['sections'] ) ) {
			foreach ( $config['sections'] as $section ) {
				if ( $section['id'] === $section_id ) {
					return array( 'sections' => array( $section ) );
				}
			}
		}

		return array();
	}


	/**
	 * Clear configuration cache.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module Optional. Specific module to clear. If empty, clears all.
	 */
	public static function clear_cache( $module = '' ) {
		if ( empty( $module ) ) {
			self::$cache = array();
		} else {
			foreach ( self::$cache as $key => $value ) {
				if ( strpos( $key, $module . '_' ) === 0 ) {
					unset( self::$cache[ $key ] );
				}
			}
		}
	}

	/**
	 * Get available configuration modules.
	 *
	 * @since 1.0.0
	 *
	 * @return array Available modules.
	 */
	public static function get_available_modules() {
		return self::$modules;
	}

	/**
	 * Check if module configuration exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $module Module name.
	 * @return bool True if configuration exists.
	 */
	public static function module_exists( $module ) {
		$config_file = self::get_config_file_path( $module );
		return file_exists( $config_file );
	}
}
