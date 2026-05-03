<?php
/**
 * Course Frontend Class
 *
 * Handles course-specific frontend functionality including archive pages,
 * pagination, template hooks, and query modifications.
 *
 * @package SkillPulse_LMS
 * @subpackage Courses
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Course Frontend Class
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Course_Frontend {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Course_Frontend|null $instance
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Course_Frontend
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup action hooks for course frontend functionality.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function setup_actions() {
		// Course query modification.
		add_action( 'pre_get_posts', array( $this, 'modify_course_archive_query' ), 5 );

		// Override WordPress reading settings for course archives.
		add_filter( 'option_posts_per_page', array( $this, 'override_posts_per_page_option' ) );

		// Setup course archive template hooks.
		add_action( 'init', array( $this, 'setup_course_template_hooks' ) );

		// Auto-populate loop props after query is ready.
		add_action( 'wp', array( $this, 'auto_populate_loop_props_on_wp' ), 20 );

		// Add SEO and accessibility improvements.
		add_action( 'wp_head', array( $this, 'add_course_archive_seo_meta' ) );
	}

	/**
	 * Setup course archive template hooks.
	 * Similar to WooCommerce's template hook system.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function setup_course_template_hooks() {
		// Course loop start/end.
		add_action( 'splms_before_course_loop', 'splms_course_loop_start', 10 );
		add_action( 'splms_after_course_loop', 'splms_course_loop_end', 10 );

		// Pagination and result count.
		add_action( 'splms_after_course_loop', 'splms_output_pagination', 20 );
		add_action( 'splms_before_course_loop_item', 'splms_output_course_count', 5 );
	}

	/**
	 * Modify course archive query to handle search and filters.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return void
	 */
	public function modify_course_archive_query( $query ) {
		// Only affect main query on frontend.
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// WooCommerce-style: Check for course archives, taxonomy pages, and custom course pages.
		$is_course_archive = false;

		// Native course archive.
		if ( $query->is_post_type_archive( SPLMS_POST_TYPES['course'] ) ) {
			$is_course_archive = true;
		}

		// Course taxonomy pages.
		if ( $query->is_tax( array_values( SPLMS_TAXONOMIES ) ) ) {
			$is_course_archive = true;
		}

		// Custom course page (WooCommerce shop page style).
		if ( splms_is_course_page() ) {
			$is_course_archive = true;

			// Transform page query into course archive query.
			$query->set( 'post_type', SPLMS_POST_TYPES['course'] );
			$query->set( 'page_id', '' );
			$query->set( 'pagename', '' );
			$query->is_page              = false;
			$query->is_singular          = false;
			$query->is_archive           = true;
			$query->is_post_type_archive = true;
		}

		// Exit if this isn't a course-related page.
		if ( ! $is_course_archive ) {
			return;
		}

		// Mark this query as a SkillPulse LMS course query for loop identification.
		$query->set( 'splms_query', 'course_query' );

		// Set posts per page - always override with our settings.
		$posts_per_page = $this->get_courses_per_page( $query );
		$query->set( 'posts_per_page', $posts_per_page );

		// Handle search filter.
		$this->handle_course_search_filter( $query );

		// Handle meta-based filters.
		$this->handle_course_meta_filters( $query );

		// Handle taxonomy-based filters.
		$this->handle_course_taxonomy_filters( $query );

		// Handle sorting.
		$this->handle_course_sorting( $query );
	}

	/**
	 * Auto-populate loop props when WordPress is fully loaded.
	 * Called on 'wp' action to ensure main query is ready.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function auto_populate_loop_props_on_wp() {
		global $wp_query;

		if ( function_exists( 'splms_auto_populate_loop_props' ) ) {
			splms_auto_populate_loop_props( $wp_query );
		}
	}

	/**
	 * Override WordPress reading settings for course archives.
	 * This intercepts the 'posts_per_page' option when WordPress reads it.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value The current option value.
	 * @return mixed Modified value for course archives, original value otherwise.
	 */
	public function override_posts_per_page_option( $value ) {
		// Only modify during course archive pages.
		if ( is_admin() ) {
			return $value;
		}

		// Check current request directly since $wp_query isn't ready yet.
		$is_course_request = $this->is_course_archive_request();

		if ( $is_course_request ) {
			$courses_per_page = splms_get_setting( 'course_item_per_page', 12 );
			$courses_per_page = absint( $courses_per_page );
			if ( $courses_per_page < 1 ) {
				$courses_per_page = 12;
			}

			return $courses_per_page;
		}

		return $value;
	}

	/**
	 * Check if current request is for course archive.
	 * This works before $wp_query is fully initialized.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_course_archive_request() {
		$request_uri = ! empty( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// Early check for native course archive URL pattern.
		if ( strpos( $request_uri, '/courses' ) !== false ) {
			return true;
		}

		// Check query vars for course post type.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.PHP.YodaConditions.NotYoda -- URL parameter check, Yoda condition correctly applied.
		if ( isset( $_GET['post_type'] ) && SPLMS_POST_TYPES['course'] === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
			return true;
		}

		// Check for course taxonomy pages.
		$course_taxonomies = array_values( SPLMS_TAXONOMIES );
		foreach ( $course_taxonomies as $taxonomy ) {
			if ( strpos( $request_uri, '/' . $taxonomy ) !== false ) {
				return true;
			}
		}

		// For custom course pages, we need to check if this is a page request.
		// that matches our course page setting. We can't use splms_is_course_page()
		// here because the query isn't fully initialized yet.
		$course_page_id = splms_get_course_page_id();
		if ( $course_page_id > 0 ) {
			// Check if this looks like a page request and matches our course page.
			$page_id  = get_query_var( 'page_id' );
			$pagename = get_query_var( 'pagename' );

			// Direct page ID match.
			if ( $page_id && absint( $page_id ) === $course_page_id ) {
				return true;
			}

			// Page name/slug match.
			if ( $pagename ) {
				$course_page = get_post( $course_page_id );
				if ( $course_page && $course_page->post_name === $pagename ) {
					return true;
				}
			}

			// URL-based detection for custom course page.
			$course_page_url = get_permalink( $course_page_id );
			if ( $course_page_url ) {
				$course_page_path = wp_parse_url( $course_page_url, PHP_URL_PATH );
				if ( $course_page_path && strpos( $request_uri, $course_page_path ) === 0 ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get courses per page setting using WooCommerce-style approach.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return int Number of courses per page.
	 */
	private function get_courses_per_page( $query ) {
		// Get setting with proper validation.
		$setting_value    = splms_get_setting( 'course_item_per_page', 12 );
		$default_per_page = absint( $setting_value );

		// Ensure we have a valid value, fallback if setting is invalid.
		if ( $default_per_page < 1 ) {
			$default_per_page = 12;
		}

		// Apply filter for third-party modifications (like WooCommerce's loop_shop_per_page).
		$filtered_per_page = apply_filters( 'splms_courses_per_page', $default_per_page, $query );
		$filtered_per_page = absint( $filtered_per_page );

		// Ensure filtered value is valid.
		if ( $filtered_per_page < 1 ) {
			$filtered_per_page = $default_per_page;
		}

		// Allow URL parameter to override (for per-page selectors).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for display only.
		if ( ! empty( $_GET['per_page'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for display only.
			$per_page = absint( $_GET['per_page'] );
			// Limit to reasonable range.
			if ( $per_page >= 1 && $per_page <= 100 ) {
				$filtered_per_page = $per_page;
			}
		}

		return $filtered_per_page;
	}

	/**
	 * Handle course search filter.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return void
	 */
	private function handle_course_search_filter( $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for search only.
		if ( ! empty( $_GET['course_search'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for search only.
			$search_term = sanitize_text_field( wp_unslash( $_GET['course_search'] ) );
			$query->set( 's', $search_term );
		}
	}

	/**
	 * Handle course meta-based filters.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return void
	 */
	private function handle_course_meta_filters( $query ) {
		$meta_query = array();

		// Filter by difficulty level.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
		if ( ! empty( $_GET['difficulty'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
			$difficulty   = sanitize_text_field( wp_unslash( $_GET['difficulty'] ) );
			$meta_query[] = array(
				'key'     => '_splms_course_content_settings',
				'value'   => '"difficulty_level";s:' . strlen( $difficulty ) . ':"' . $difficulty . '"',
				'compare' => 'LIKE',
			);
		}

		// Filter by learning method.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
		if ( ! empty( $_GET['learning_method'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
			$learning_method = sanitize_text_field( wp_unslash( $_GET['learning_method'] ) );
			$meta_query[]    = array(
				'key'     => '_splms_course_content_settings',
				'value'   => '"learning_method";s:' . strlen( $learning_method ) . ':"' . $learning_method . '"',
				'compare' => 'LIKE',
			);
		}

		// Filter by course type (free/paid).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
		if ( ! empty( $_GET['course_type'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
			$type = sanitize_text_field( wp_unslash( $_GET['course_type'] ) );
			if ( 'free' === $type ) {
				$meta_query[] = array(
					'key'     => '_splms_course_content_settings',
					'value'   => 'is_free";b:1',
					'compare' => 'LIKE',
				);
			}
		}

		if ( ! empty( $meta_query ) ) {
			$meta_query['relation'] = 'AND';
			$query->set( 'meta_query', $meta_query );
		}
	}

	/**
	 * Handle course taxonomy-based filters.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return void
	 */
	private function handle_course_taxonomy_filters( $query ) {
		$tax_query   = array();
		$has_filters = false;

		// Filter by categories.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
		if ( ! empty( $_GET['course_category'] ) && splms_get_setting( 'course_categories_enabled', true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- GET parameter for filtering, sanitized on next line.
			$raw_categories = isset( $_GET['course_category'] ) ? (array) wp_unslash( $_GET['course_category'] ) : array();
			$categories     = array_map( 'sanitize_text_field', $raw_categories );
			if ( ! empty( $categories ) ) {
				$tax_query[] = array(
					'taxonomy' => SPLMS_TAXONOMIES['course_category'],
					'field'    => 'slug',
					'terms'    => $categories,
					'operator' => 'IN',
				);
				$has_filters = true;
			}
		}

		// Filter by tags.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for filtering only.
		if ( ! empty( $_GET['course_tag'] ) && splms_get_setting( 'course_tags_enabled', true ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- GET parameter for filtering, sanitized on next line.
			$raw_tags = isset( $_GET['course_tag'] ) ? (array) wp_unslash( $_GET['course_tag'] ) : array();
			$tags     = array_map( 'sanitize_text_field', $raw_tags );
			if ( ! empty( $tags ) ) {
				$tax_query[] = array(
					'taxonomy' => SPLMS_TAXONOMIES['course_tag'],
					'field'    => 'slug',
					'terms'    => $tags,
					'operator' => 'IN',
				);
				$has_filters = true;
			}
		}

		// If we have filter parameters, override any URL-based taxonomy query.
		if ( $has_filters ) {
			// Clear URL-based taxonomy queries to prioritize filter parameters.
			foreach ( array_values( SPLMS_TAXONOMIES ) as $taxonomy ) {
				$query->set( $taxonomy, '' );
			}

			// Set our custom tax_query.
			if ( ! empty( $tax_query ) ) {
				$tax_query['relation'] = 'AND';
				$query->set( 'tax_query', $tax_query );
			}
		}
	}

	/**
	 * Handle course sorting.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 * @return void
	 */
	private function handle_course_sorting( $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter for sorting only.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';

		if ( ! empty( $orderby ) ) {
			switch ( $orderby ) {
				case 'date':
					$query->set( 'orderby', 'date' );
					$query->set( 'order', 'DESC' );
					break;
				case 'title':
					$query->set( 'orderby', 'title' );
					$query->set( 'order', 'ASC' );
					break;
				case 'popularity':
					$query->set( 'orderby', 'meta_value_num' );
					$query->set( 'meta_key', '_splms_course_students_count' );
					$query->set( 'order', 'DESC' );
					break;
				default:
					$custom_orderby = apply_filters( 'splms_course_archive_custom_orderby', null, $orderby, $query );
					if ( $custom_orderby ) {
						// Allow custom sorting via filter.
						break;
					}
					// Default to date DESC if orderby is not recognized.
					$query->set( 'orderby', 'date' );
					$query->set( 'order', 'DESC' );
					break;
			}
		}
	}

	/**
	 * Add SEO and accessibility improvements for course archives.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_course_archive_seo_meta() {
		// Only apply to course archives and taxonomy pages.
		if ( ! $this->is_course_archive_page() ) {
			return;
		}

		// Add meta description.
		$meta_description = $this->get_archive_meta_description();
		if ( $meta_description ) {
			echo '<meta name="description" content="' . esc_attr( $meta_description ) . '">' . "\n";
		}

		// Add structured data.
		$schema = $this->get_archive_schema_data();
		if ( $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . '</script>' . "\n";
		}
	}

	/**
	 * Check if current page is a course archive page.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_course_archive_page() {
		// Use centralized function for course archive detection (includes custom pages).
		if ( splms_is_course_archive() ||
			is_tax( SPLMS_TAXONOMIES['course_category'] ) ||
			is_tax( SPLMS_TAXONOMIES['course_tag'] ) ) {
			return true;
		}

		// Check for course search.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- GET parameter check only.
		if ( is_search() && ( ! empty( $_GET['course_search'] ) || get_query_var( 'post_type' ) === SPLMS_POST_TYPES['course'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get meta description for current archive page.
	 *
	 * @since 1.0.0
	 *
	 * @return string|false
	 */
	private function get_archive_meta_description() {
		if ( is_tax( SPLMS_TAXONOMIES['course_category'] ) || is_tax( SPLMS_TAXONOMIES['course_tag'] ) ) {
			$current_term = get_queried_object();
			if ( $current_term && $current_term->description ) {
				return wp_strip_all_tags( $current_term->description );
			}

			if ( is_tax( SPLMS_TAXONOMIES['course_category'] ) ) {
				/* translators: %s: Category name */
				return sprintf( __( 'Browse all courses in the %s category. Find the best courses to enhance your skills.', 'skillpulse-lms' ), $current_term->name );
			} else {
				/* translators: %s: Tag name */
				return sprintf( __( 'Discover courses tagged with %s. Find relevant courses to advance your learning journey.', 'skillpulse-lms' ), $current_term->name );
			}
		} elseif ( is_search() ) {
			$search_query = get_search_query();
			if ( $search_query ) {
				/* translators: %s: Search query */
				return sprintf( __( 'Search results for "%s" courses. Find relevant courses to match your interests.', 'skillpulse-lms' ), $search_query );
			}
		} elseif ( is_post_type_archive( SPLMS_POST_TYPES['course'] ) ) {
			$courses_page_id = splms_get_setting( 'courses_page_id', 0 );
			if ( $courses_page_id ) {
				$page_content = get_post_field( 'post_content', $courses_page_id );
				if ( $page_content ) {
					return wp_trim_words( wp_strip_all_tags( $page_content ), 30 );
				}
			}
			return __( 'Browse all available courses. Find the perfect course to enhance your skills and advance your career.', 'skillpulse-lms' );
		}

		return false;
	}

	/**
	 * Get schema data for current archive page.
	 *
	 * @since 1.0.0
	 *
	 * @return array|false
	 */
	private function get_archive_schema_data() {
		$base_schema = array(
			'@context' => 'https://schema.org',
			'@type'    => 'CollectionPage',
			'isPartOf' => array(
				'@type' => 'WebSite',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url( '/' ),
			),
		);

		if ( is_tax( SPLMS_TAXONOMIES['course_category'] ) || is_tax( SPLMS_TAXONOMIES['course_tag'] ) ) {
			$current_term               = get_queried_object();
			$base_schema['name']        = $current_term->name;
			$base_schema['description'] = $current_term->description ?
				wp_strip_all_tags( $current_term->description ) :
				sprintf(
					/* translators: %s: Course section name. */
					__( 'Browse all courses in the %s section', 'skillpulse-lms' ),
					$current_term->name
				);
			$base_schema['url'] = get_term_link( $current_term );
		} elseif ( is_search() ) {
			$search_query = get_search_query();
			if ( $search_query ) {
				/* translators: %s: Search query */
				$base_schema['name'] = sprintf( __( 'Search Results for "%s"', 'skillpulse-lms' ), $search_query );
				/* translators: %s: Search query */
				$base_schema['description'] = sprintf( __( 'Search results for "%s" courses', 'skillpulse-lms' ), $search_query );
				$base_schema['url']         = home_url( add_query_arg() );
			}
		} elseif ( is_post_type_archive( SPLMS_POST_TYPES['course'] ) ) {
			$courses_page_id = splms_get_setting( 'courses_page_id', 0 );
			if ( $courses_page_id ) {
				$base_schema['name'] = get_the_title( $courses_page_id );
			} else {
				$base_schema['name'] = __( 'All Courses', 'skillpulse-lms' );
			}
			$base_schema['description'] = __( 'Browse all available courses to enhance your skills', 'skillpulse-lms' );
			$base_schema['url']         = get_post_type_archive_link( SPLMS_POST_TYPES['course'] );
		} else {
			return false;
		}

		return $base_schema;
	}

	/**
	 * Get course archive setup data for templates.
	 * Centralizes the common logic shared across archive, category, and tag templates.
	 *
	 * @since 1.0.0
	 *
	 * @param string $context      Archive context: 'archive', 'category', 'tag'.
	 * @param object $current_term Current term object for taxonomy pages.
	 * @return array Setup data for the archive template.
	 */
	public static function get_course_archive_setup( $context = 'archive', $current_term = null ) {
		// Get settings.
		$search_enabled     = splms_get_setting( 'course_search_enabled', true );
		$categories_enabled = splms_get_setting( 'course_categories_enabled', true );
		$tags_enabled       = splms_get_setting( 'course_tags_enabled', true );

		// Set base archive classes based on context.
		$splms_archive_classes = array( 'splms-archive' );
		if ( 'archive' !== $context ) {
			$splms_archive_classes[] = 'splms-taxonomy-archive';
		}

		// Get categories based on context.
		$categories = array();
		if ( $categories_enabled ) {
			$category_args = array(
				'taxonomy'   => SPLMS_TAXONOMIES['course_category'],
				'hide_empty' => true,
			);

			// Exclude current category for category taxonomy pages.
			if ( 'category' === $context && $current_term && $current_term->term_id ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Single term exclusion; negligible performance impact.
				$category_args['exclude'] = array( $current_term->term_id );
			}

			$categories = get_terms( $category_args );
		}

		// Get tags based on context.
		$tags = array();
		if ( $tags_enabled ) {
			$tag_args = array(
				'taxonomy'   => SPLMS_TAXONOMIES['course_tag'],
				'hide_empty' => true,
			);

			// Exclude current tag for tag taxonomy pages.
			if ( 'tag' === $context && $current_term && $current_term->term_id ) {
				// phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Single term exclusion; negligible performance impact.
				$tag_args['exclude'] = array( $current_term->term_id );
			}

			$tags = get_terms( $tag_args );
		}

		// Check if we have sidebar filters.
		$has_sidebar_filters = ( $categories_enabled && ! empty( $categories ) && ! is_wp_error( $categories ) ) ||
								( $tags_enabled && ! empty( $tags ) && ! is_wp_error( $tags ) );

		// Determine control states.
		$control_count = 0;
		if ( $search_enabled ) {
			++$control_count;
		}
		++$control_count; // Course-filters always present.
		++$control_count; // Layout-switcher always present.

		if ( $control_count <= 1 ) {
			$splms_archive_classes[] = 'minimal-controls';
		}

		if ( ! $has_sidebar_filters ) {
			$splms_archive_classes[] = 'no-sidebar';
		} else {
			$filter_count = 0;
			if ( $categories_enabled && ! empty( $categories ) && ! is_wp_error( $categories ) ) {
				++$filter_count;
			}
			if ( $tags_enabled && ! empty( $tags ) && ! is_wp_error( $tags ) ) {
				++$filter_count;
			}

			if ( 1 === $filter_count ) {
				$splms_archive_classes[] = 'single-filter-sidebar';
			} elseif ( $filter_count <= 2 ) {
				$splms_archive_classes[] = 'minimal-sidebar';
			}
		}

		return array(
			'splms_archive_classes'     => $splms_archive_classes,
			'splms_categories'          => $categories,
			'splms_tags'                => $tags,
			'splms_has_sidebar_filters' => $has_sidebar_filters,
			'splms_search_enabled'      => $search_enabled,
			'splms_categories_enabled'  => $categories_enabled,
			'splms_tags_enabled'        => $tags_enabled,
		);
	}
}
