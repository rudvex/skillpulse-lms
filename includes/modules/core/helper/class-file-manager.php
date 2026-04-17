<?php
/**
 * SkillPulse LMS File Manager
 *
 * Centralized file management system for SkillPulse LMS plugin.
 * Handles all file operations with organized directory structure.
 *
 * @since      1.0.0
 * @subpackage Core
 * @package    SkillPulse_LMS
 */

defined( 'ABSPATH' ) || exit;

/**
 * SkillPulse LMS File Manager Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_File_Manager {

	/**
	 * Base directory name for SkillPulse LMS files
	 *
	 * @since 1.0.0
	 * @var string
	 */
	const BASE_DIR = 'splms';

	/**
	 * Default directories to create
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $default_directories = array(
		'reports',
		'logs',
		'exports',
		'imports',
		'temp',
		'media',
		'certificates',
		'backups',
	);

	/**
	 * Get the base upload directory for SkillPulse LMS
	 *
	 * @since 1.0.0
	 * @return array Upload directory information
	 */
	public static function get_base_dir() {
		$wp_upload_dir = wp_upload_dir();

		$base_path = trailingslashit( $wp_upload_dir['basedir'] ) . self::BASE_DIR;
		$base_url  = trailingslashit( $wp_upload_dir['baseurl'] ) . self::BASE_DIR;

		return array(
			'path'    => $base_path,
			'url'     => $base_url,
			'basedir' => $wp_upload_dir['basedir'],
			'baseurl' => $wp_upload_dir['baseurl'],
			'error'   => $wp_upload_dir['error'],
		);
	}

	/**
	 * Get directory path for a specific feature.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name         Feature directory name (e.g., 'reports', 'logs').
	 * @param bool   $create_if_not_exists Whether to create the directory if it doesn't exist.
	 * @return array|WP_Error Directory information or error.
	 */
	public static function get_feature_dir( $feature_name, $create_if_not_exists = true ) {
		$base_dir = self::get_base_dir();

		if ( $base_dir['error'] ) {
			return new WP_Error( 'upload_dir_error', $base_dir['error'] );
		}

		$feature_path = trailingslashit( $base_dir['path'] ) . sanitize_file_name( $feature_name );
		$feature_url  = trailingslashit( $base_dir['url'] ) . sanitize_file_name( $feature_name );

		// Create directory if requested and doesn't exist.
		if ( $create_if_not_exists && ! file_exists( $feature_path ) ) {
			$created = self::create_directory( $feature_name );
			if ( is_wp_error( $created ) ) {
				return $created;
			}
		}

		return array(
			'path'      => $feature_path,
			'url'       => $feature_url,
			'feature'   => $feature_name,
			'exists'    => file_exists( $feature_path ),
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Local file check is intentional.
			'writable'  => is_writable( $feature_path ),
			'base_path' => $base_dir['path'],
			'base_url'  => $base_dir['url'],
		);
	}

	/**
	 * Initialize SkillPulse LMS directory structure
	 *
	 * @since 1.0.0
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public static function init_directories() {
		$base_dir = self::get_base_dir();

		// Create base directory.
		if ( ! file_exists( $base_dir['path'] ) ) {
			if ( ! wp_mkdir_p( $base_dir['path'] ) ) {
				return new WP_Error(
					'dir_creation_failed',
					__( 'Failed to create SkillPulse LMS base directory.', 'skillpulse-lms' ),
					array( 'path' => $base_dir['path'] )
				);
			}
		}

		// Create default feature directories.
		foreach ( self::$default_directories as $dir_name ) {
			$result = self::create_directory( $dir_name );
			if ( is_wp_error( $result ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Debug logging is intentional.
				error_log( 'SkillPulse LMS: Failed to create directory: ' . $dir_name . ' - ' . $result->get_error_message() );
			}
		}

		// Create .htaccess for security.
		self::create_htaccess_protection();

		// Create index.php files for directory protection.
		self::create_index_files();

		return true;
	}

	/**
	 * Create a directory within the SkillPulse LMS structure.
	 *
	 * @since 1.0.0
	 *
	 * @param string $directory_name Directory name to create.
	 * @param string $parent_dir     Parent directory (optional).
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function create_directory( $directory_name, $parent_dir = '' ) {
		$base_dir = self::get_base_dir();

		if ( $base_dir['error'] ) {
			return new WP_Error( 'upload_dir_error', $base_dir['error'] );
		}

		$sanitized_name = sanitize_file_name( $directory_name );
		if ( empty( $sanitized_name ) ) {
			return new WP_Error( 'invalid_directory_name', __( 'Invalid directory name.', 'skillpulse-lms' ) );
		}

		// Build full path.
		$full_path = $base_dir['path'];
		if ( ! empty( $parent_dir ) ) {
			$full_path = trailingslashit( $full_path ) . sanitize_file_name( $parent_dir );
		}
		$full_path = trailingslashit( $full_path ) . $sanitized_name;

		// Create directory if it doesn't exist.
		if ( ! file_exists( $full_path ) ) {
			if ( ! wp_mkdir_p( $full_path ) ) {
				return new WP_Error(
					'dir_creation_failed',
					/* translators: %s: Directory path. */
					sprintf( __( 'Failed to create directory: %s', 'skillpulse-lms' ), $full_path ),
					array( 'path' => $full_path )
				);
			}
		}

		return true;
	}

	/**
	 * Write content to a file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name Feature directory name.
	 * @param string $filename     File name.
	 * @param string $content      File content.
	 * @param bool   $append       Whether to append to existing file.
	 * @return array|WP_Error File information on success, WP_Error on failure.
	 */
	public static function write_file( $feature_name, $filename, $content, $append = false ) {
		$dir_info = self::get_feature_dir( $feature_name, true );

		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		if ( ! $dir_info['writable'] ) {
			return new WP_Error( 'directory_not_writable', __( 'Directory is not writable.', 'skillpulse-lms' ) );
		}

		$filename = sanitize_file_name( $filename );
		$filepath = trailingslashit( $dir_info['path'] ) . $filename;

		// Write file.
		$flags = $append ? FILE_APPEND | LOCK_EX : LOCK_EX;
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local file write is intentional.
		$result = file_put_contents( $filepath, $content, $flags );

		if ( false === $result ) {
			return new WP_Error(
				'file_write_failed',
				/* translators: %s: File name. */
				sprintf( __( 'Failed to write file: %s', 'skillpulse-lms' ), $filename ),
				array( 'filepath' => $filepath )
			);
		}

		return array(
			'filepath'       => $filepath,
			'fileurl'        => trailingslashit( $dir_info['url'] ) . $filename,
			'filename'       => $filename,
			'size'           => $result,
			'size_formatted' => size_format( $result ),
			'feature'        => $feature_name,
		);
	}

	/**
	 * Read file content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name Feature directory name.
	 * @param string $filename     File name.
	 * @return string|WP_Error File content on success, WP_Error on failure.
	 */
	public static function read_file( $feature_name, $filename ) {
		$dir_info = self::get_feature_dir( $feature_name, false );

		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		$filename = sanitize_file_name( $filename );
		$filepath = trailingslashit( $dir_info['path'] ) . $filename;

		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_not_found', __( 'File not found.', 'skillpulse-lms' ) );
		}

		if ( ! is_readable( $filepath ) ) {
			return new WP_Error( 'file_not_readable', __( 'File is not readable.', 'skillpulse-lms' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local file read is intentional.
		$content = file_get_contents( $filepath );

		if ( false === $content ) {
			return new WP_Error( 'file_read_failed', __( 'Failed to read file.', 'skillpulse-lms' ) );
		}

		return $content;
	}

	/**
	 * Delete a file.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name Feature directory name.
	 * @param string $filename     File name.
	 * @return bool|WP_Error True on success, WP_Error on failure.
	 */
	public static function delete_file( $feature_name, $filename ) {
		$dir_info = self::get_feature_dir( $feature_name, false );

		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		$filename = sanitize_file_name( $filename );
		$filepath = trailingslashit( $dir_info['path'] ) . $filename;

		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_not_found', __( 'File not found.', 'skillpulse-lms' ) );
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Local file deletion is intentional.
		if ( ! unlink( $filepath ) ) {
			return new WP_Error( 'file_delete_failed', __( 'Failed to delete file.', 'skillpulse-lms' ) );
		}

		return true;
	}

	/**
	 * Get file information.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name Feature directory name.
	 * @param string $filename     File name.
	 * @return array|WP_Error File information on success, WP_Error on failure.
	 */
	public static function get_file_info( $feature_name, $filename ) {
		$dir_info = self::get_feature_dir( $feature_name, false );

		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		$filename = sanitize_file_name( $filename );
		$filepath = trailingslashit( $dir_info['path'] ) . $filename;

		if ( ! file_exists( $filepath ) ) {
			return new WP_Error( 'file_not_found', __( 'File not found.', 'skillpulse-lms' ) );
		}

		$file_size = filesize( $filepath );
		$file_time = filemtime( $filepath );

		return array(
			'filepath'       => $filepath,
			'fileurl'        => trailingslashit( $dir_info['url'] ) . $filename,
			'filename'       => $filename,
			'feature'        => $feature_name,
			'size'           => $file_size,
			'size_formatted' => size_format( $file_size ),
			'modified'       => $file_time,
			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- Date formatting for file info display.
			'modified_date'  => date( 'Y-m-d H:i:s', $file_time ),
			'is_readable'    => is_readable( $filepath ),
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_is_writable -- Local file check is intentional.
			'is_writable'    => is_writable( $filepath ),
		);
	}

	/**
	 * List files in a feature directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name   Feature directory name.
	 * @param string $file_extension File extension filter (optional).
	 * @return array|WP_Error Array of files on success, WP_Error on failure.
	 */
	public static function list_files( $feature_name, $file_extension = '' ) {
		$dir_info = self::get_feature_dir( $feature_name, false );

		if ( is_wp_error( $dir_info ) ) {
			return $dir_info;
		}

		if ( ! $dir_info['exists'] ) {
			return array();
		}

		$files   = array();
		$pattern = trailingslashit( $dir_info['path'] ) . '*';

		if ( ! empty( $file_extension ) ) {
			$pattern .= '.' . ltrim( $file_extension, '.' );
		}

		$found_files = glob( $pattern );

		if ( false === $found_files ) {
			return new WP_Error( 'directory_read_failed', __( 'Failed to read directory.', 'skillpulse-lms' ) );
		}

		foreach ( $found_files as $filepath ) {
			if ( is_file( $filepath ) ) {
				$filename  = basename( $filepath );
				$file_info = self::get_file_info( $feature_name, $filename );

				if ( ! is_wp_error( $file_info ) ) {
					$files[] = $file_info;
				}
			}
		}

		// Sort by modified date (newest first).
		usort(
			$files,
			function ( $a, $b ) {
				return $b['modified'] - $a['modified'];
			}
		);

		return $files;
	}

	/**
	 * Clean up old files in a directory.
	 *
	 * @since 1.0.0
	 *
	 * @param string $feature_name Feature directory name.
	 * @param int    $days_old     Delete files older than this many days.
	 * @param int    $max_files    Maximum number of files to keep (0 = no limit).
	 * @return array Cleanup results.
	 */
	public static function cleanup_old_files( $feature_name, $days_old = 30, $max_files = 0 ) {
		$files = self::list_files( $feature_name );

		if ( is_wp_error( $files ) ) {
			return array(
				'success' => false,
				'error'   => $files->get_error_message(),
				'deleted' => 0,
				'total'   => 0,
			);
		}

		$deleted_count = 0;
		$total_files   = count( $files );
		$cutoff_time   = time() - ( $days_old * DAY_IN_SECONDS );

		// Sort files by date (oldest first for deletion).
		usort(
			$files,
			function ( $a, $b ) {
				return $a['modified'] - $b['modified'];
			}
		);

		foreach ( $files as $index => $file ) {
			$should_delete = false;

			// Delete if older than specified days.
			if ( $file['modified'] < $cutoff_time ) {
				$should_delete = true;
			}

			// Delete if exceeding max files limit (keep newest).
			if ( $max_files > 0 && ( $total_files - $deleted_count ) > $max_files ) {
				$should_delete = true;
			}

			if ( $should_delete ) {
				$result = self::delete_file( $feature_name, $file['filename'] );
				if ( ! is_wp_error( $result ) ) {
					++$deleted_count;
				}
			}
		}

		return array(
			'success'   => true,
			'deleted'   => $deleted_count,
			'total'     => $total_files,
			'remaining' => $total_files - $deleted_count,
			'days_old'  => $days_old,
			'max_files' => $max_files,
		);
	}

	/**
	 * Create .htaccess file for directory protection.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True on success, false on failure.
	 */
	private static function create_htaccess_protection() {
		$base_dir      = self::get_base_dir();
		$htaccess_file = trailingslashit( $base_dir['path'] ) . '.htaccess';

		$htaccess_content  = "# SkillPulse LMS Directory Protection\n";
		$htaccess_content .= "# Prevent direct access to files\n";
		$htaccess_content .= "Options -Indexes\n";
		$htaccess_content .= "<Files ~ \"\\.(txt|log|json|csv|xml)$\">\n";
		$htaccess_content .= "    Order allow,deny\n";
		$htaccess_content .= "    Deny from all\n";
		$htaccess_content .= "</Files>\n";

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local file write is intentional.
		return file_put_contents( $htaccess_file, $htaccess_content ) !== false;
	}

	/**
	 * Create index.php files in directories for protection.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	private static function create_index_files() {
		$base_dir      = self::get_base_dir();
		$index_content = "<?php\n// Silence is golden\n";

		// Create index.php in base directory.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local file write is intentional.
		file_put_contents( trailingslashit( $base_dir['path'] ) . 'index.php', $index_content );

		// Create index.php in each feature directory.
		foreach ( self::$default_directories as $dir_name ) {
			$dir_path = trailingslashit( $base_dir['path'] ) . $dir_name;
			if ( file_exists( $dir_path ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Local file write is intentional.
				file_put_contents( trailingslashit( $dir_path ) . 'index.php', $index_content );
			}
		}
	}
}
