<?php
/**
 * WP-CLI Commands for SkillPulse LMS
 *
 * @package SkillPulse_LMS
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Manage SkillPulse LMS.
 */
class SPLMS_CLI_Commands extends WP_CLI_Command {


	/**
	 * Generates the static API documentation JSON file.
	 *
	 * ## EXAMPLES
	 *
	 *     wp splms generate-docs
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 * @when after_wp_load
	 */
	public function generate_docs( $args, $assoc_args ) {
		WP_CLI::log( 'Starting API documentation generation...' );

		$plugin_dir = dirname( __DIR__ ); // Go up from bin/.

		// Load necessary classes.
		$scanner_file   = $plugin_dir . '/api-docs/api-scanner.php';
		$generator_file = $plugin_dir . '/api-docs/code-generator.php';

		if ( ! file_exists( $scanner_file ) || ! file_exists( $generator_file ) ) {
			WP_CLI::error( 'Required API Documentation files not found.' );
		}

		require_once $scanner_file;
		require_once $generator_file;

		WP_CLI::log( 'Scanning for API endpoints...' );

		try {
			$scanner   = new SPLMS_API_Scanner( $plugin_dir );
			$endpoints = $scanner->scan();

			WP_CLI::success( sprintf( 'Found %d endpoints.', count( $endpoints ) ) );
			WP_CLI::log( 'Generating code examples...' );

			$code_generator = new SPLMS_Code_Generator();

			$progress = WP_CLI\Utils\make_progress_bar( 'Processing endpoints', count( $endpoints ) );

			foreach ( $endpoints as &$endpoint ) {
				$endpoint['examples'] = array(
					'curl'       => $code_generator->generate_curl( $endpoint ),
					'javascript' => $code_generator->generate_javascript( $endpoint ),
					'php'        => $code_generator->generate_php( $endpoint ),
					'response'   => $code_generator->generate_response_example( $endpoint ),
				);
				$progress->tick();
			}
			$progress->finish();

			// Prepare data.
			$data = array(
				'success'      => true,
				'baseUrl'      => home_url(),
				'endpoints'    => $endpoints,
				'count'        => count( $endpoints ),
				'generated_at' => current_time( 'mysql' ),
				'version'      => defined( 'SPLMS_VERSION' ) ? SPLMS_VERSION : '1.0.0',
				'api_version'  => splms_rest_version(),
			);

			// Write to file.
			$output_file = $plugin_dir . '/api-docs/data.json';
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- CLI tool, direct file write is acceptable.
			$bytes = file_put_contents( $output_file, wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );

			if ( false !== $bytes ) {
				$size_kb = number_format( $bytes / 1024, 2 );
				WP_CLI::success( "Documentation generated successfully: $output_file ($size_kb KB)" );
			} else {
				WP_CLI::error( "Failed to write to $output_file" );
			}
		} catch ( Exception $e ) {
			WP_CLI::error( 'Error occurred: ' . $e->getMessage() );
		}
	}
}

WP_CLI::add_command( 'splms', 'SPLMS_CLI_Commands' );
