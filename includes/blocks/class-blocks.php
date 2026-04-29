<?php
/**
 * Gutenberg Blocks registration and rendering.
 *
 * @since   1.0.0
 * @package SkillPulse_LMS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gutenberg Blocks class.
 *
 * Registers all SkillPulse LMS Gutenberg blocks and provides
 * server-side render callbacks.
 *
 * @since 1.0.0
 */
class SkillPulse_LMS_Blocks {

	/**
	 * Class instance.
	 *
	 * @var SkillPulse_LMS_Blocks|null
	 */
	private static $instance = null;

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SkillPulse_LMS_Blocks
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0.0
	 */
	private function setup_hooks() {
		add_action( 'init', array( $this, 'register_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the SkillPulse LMS block category.
	 *
	 * @since 1.0.0
	 *
	 * @param array                   $categories Existing block categories.
	 * @param WP_Block_Editor_Context $context    Block editor context.
	 * @return array Modified categories.
	 */
	public function register_block_category( $categories, $context ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- Required by filter signature.
		return array_merge(
			array(
				array(
					'slug'  => 'skillpulse-lms',
					'title' => __( 'SkillPulse LMS', 'skillpulse-lms' ),
					'icon'  => 'welcome-learn-more',
				),
			),
			$categories
		);
	}

	/**
	 * Register all blocks.
	 *
	 * @since 1.0.0
	 */
	public function register_blocks() {
		// Register shared frontend style for all blocks.
		wp_register_style(
			'splms-blocks-style',
			SKILLPULSE_LMS_URL_PATH . 'assets/css/blocks-editor.css',
			array(),
			SKILLPULSE_LMS_VERSION
		);

		$blocks = array(
			'course-grid'       => array( $this, 'render_course_grid' ),
			'course-card'       => array( $this, 'render_course_card' ),
			'course-categories' => array( $this, 'render_course_categories' ),
			'course-search'     => array( $this, 'render_course_search' ),
			'account-info'      => array( $this, 'render_account_info' ),
			'enroll-button'     => array( $this, 'render_enroll_button' ),
			'course-curriculum' => array( $this, 'render_course_curriculum' ),
			'course-progress'   => array( $this, 'render_course_progress' ),
			'my-courses'        => array( $this, 'render_my_courses' ),
			'course-instructor' => array( $this, 'render_course_instructor' ),
		);

		foreach ( $blocks as $block_name => $render_callback ) {
			$block_json = SKILLPULSE_LMS_DIR_PATH . 'src/blocks/' . $block_name . '/block.json';
			if ( file_exists( $block_json ) ) {
				register_block_type(
					$block_json,
					array(
						'render_callback' => $render_callback,
						'style'           => 'splms-blocks-style',
					)
				);
			}
		}
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_editor_assets() {
		$asset_file = SKILLPULSE_LMS_DIR_PATH . 'assets/js/blocks.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		$asset = include $asset_file;

		wp_enqueue_script(
			'splms-blocks-editor',
			SKILLPULSE_LMS_URL_PATH . 'assets/js/blocks.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Block styles are registered via register_block_type( 'style' => 'splms-blocks-style' ).
		// Enqueue in editor too so ServerSideRender preview is styled.
		wp_enqueue_style( 'splms-blocks-style' );

		wp_localize_script(
			'splms-blocks-editor',
			'splmsBlocksData',
			array(
				'restUrl'   => rest_url( 'splms/v1/' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
				'postTypes' => SPLMS_POST_TYPES,
			)
		);
	}

	/**
	 * Render course grid block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_grid( $attributes ) {
		$defaults   = array(
			'columns'        => 3,
			'perPage'        => 9,
			'categories'     => array(),
			'tags'           => array(),
			'difficulty'     => 'all',
			'orderBy'        => 'date',
			'order'          => 'DESC',
			'showPagination' => true,
			'layout'         => 'grid',
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		$query_args = array(
			'post_type'      => SPLMS_POST_TYPES['course'],
			'post_status'    => 'publish',
			'posts_per_page' => absint( $attributes['perPage'] ),
			'orderby'        => sanitize_key( $attributes['orderBy'] ),
			'order'          => 'ASC' === strtoupper( $attributes['order'] ) ? 'ASC' : 'DESC',
			'paged'          => max( 1, get_query_var( 'paged', 1 ) ),
		);

		// Category filter.
		if ( ! empty( $attributes['categories'] ) ) {
			$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for category filtering.
				array(
					'taxonomy' => 'sp-course-category',
					'field'    => 'term_id',
					'terms'    => array_map( 'absint', $attributes['categories'] ),
				),
			);
		}

		// Tag filter.
		if ( ! empty( $attributes['tags'] ) ) {
			$tag_query = array(
				'taxonomy' => 'sp-course-tag',
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', $attributes['tags'] ),
			);
			if ( isset( $query_args['tax_query'] ) ) {
				$query_args['tax_query']['relation'] = 'AND';
				$query_args['tax_query'][]           = $tag_query;
			} else {
				$query_args['tax_query'] = array( $tag_query ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for tag filtering.
			}
		}

		// Difficulty filter.
		if ( 'all' !== $attributes['difficulty'] ) {
			$query_args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Necessary for difficulty filtering.
				array(
					'key'   => '_splms_course_difficulty',
					'value' => sanitize_text_field( $attributes['difficulty'] ),
				),
			);
		}

		$courses = new WP_Query( $query_args );

		// Set layout prop so splms_course_loop_start() uses the correct data-layout attribute.
		splms_set_loop_prop( 'layout', sanitize_key( $attributes['layout'] ) );

		ob_start();
		?>
		<div class="wp-block-splms-course-grid">
			<?php
			if ( $courses->have_posts() ) {
				/**
				 * Fires before the block course loop.
				 *
				 * @since 1.0.0
				 */
				do_action( 'splms_before_course_loop' );

				while ( $courses->have_posts() ) {
					$courses->the_post();
					splms_get_template_part(
						'course/course',
						array( 'splms_course_id' => get_the_ID() )
					);
				}

				/**
				 * Fires after the block course loop.
				 *
				 * @since 1.0.0
				 */
				do_action( 'splms_after_course_loop' );

				wp_reset_postdata();

				if ( $attributes['showPagination'] && $courses->max_num_pages > 1 ) {
					?>
					<div class="splms-pagination">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'total'   => $courses->max_num_pages,
									'current' => max( 1, get_query_var( 'paged', 1 ) ),
								)
							)
						);
						?>
					</div>
					<?php
				}
			} else {
				splms_get_template_part( 'course/no-courses' );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course card block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_card( $attributes ) {
		$course_id = isset( $attributes['courseId'] ) ? absint( $attributes['courseId'] ) : 0;

		if ( ! $course_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please select a course.', 'skillpulse-lms' ) . '</p>';
		}

		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Course not found.', 'skillpulse-lms' ) . '</p>';
		}

		$defaults   = array(
			'showThumbnail'    => true,
			'showExcerpt'      => true,
			'showPrice'        => true,
			'showRating'       => true,
			'showInstructor'   => true,
			'showDifficulty'   => true,
			'showActionButton' => true,
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		// Build CSS classes to hide toggled-off elements.
		// Each class targets a specific CSS selector in the course template.
		$toggle_map   = array(
			'showThumbnail'    => 'splms-hide-thumbnail',
			'showExcerpt'      => 'splms-hide-excerpt',
			'showPrice'        => 'splms-hide-price',
			'showRating'       => 'splms-hide-rating',
			'showInstructor'   => 'splms-hide-instructor',
			'showDifficulty'   => 'splms-hide-difficulty',
			'showActionButton' => 'splms-hide-actions',
		);
		$hide_classes = array();
		foreach ( $toggle_map as $attr_key => $css_class ) {
			if ( ! $attributes[ $attr_key ] ) {
				$hide_classes[] = $css_class;
			}
		}

		$wrapper_class = 'wp-block-splms-course-card ' . implode( ' ', $hide_classes );

		ob_start();
		?>
		<div class="<?php echo esc_attr( trim( $wrapper_class ) ); ?>">
			<?php
			splms_get_template_part(
				'course/course',
				array( 'splms_course_id' => $course_id )
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course categories block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_categories( $attributes ) {
		$defaults   = array(
			'columns'         => 4,
			'showCount'       => true,
			'showDescription' => false,
			'hideEmpty'       => true,
			'categories'      => array(),
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		$term_args = array(
			'taxonomy'   => 'sp-course-category',
			'hide_empty' => $attributes['hideEmpty'],
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		if ( ! empty( $attributes['categories'] ) ) {
			$term_args['include'] = array_map( 'absint', $attributes['categories'] );
		}

		$categories = get_terms( $term_args );

		if ( is_wp_error( $categories ) || empty( $categories ) ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'No course categories found.', 'skillpulse-lms' ) . '</p>';
		}

		$columns = absint( $attributes['columns'] );

		ob_start();
		?>
		<div class="wp-block-splms-course-categories splms-course-categories splms-course-categories--cols-<?php echo esc_attr( $columns ); ?>">
			<?php foreach ( $categories as $category ) : ?>
				<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="splms-course-category-card">
					<?php
					// Category thumbnail from term meta.
					$thumbnail_id = get_term_meta( $category->term_id, '_splms_category_thumbnail_id', true );
					if ( $thumbnail_id ) :
						?>
						<div class="splms-course-category-card__image">
							<?php echo wp_get_attachment_image( $thumbnail_id, 'medium' ); ?>
						</div>
					<?php endif; ?>
					<div class="splms-course-category-card__content">
						<h3 class="splms-course-category-card__title"><?php echo esc_html( $category->name ); ?></h3>
						<?php if ( $attributes['showDescription'] && ! empty( $category->description ) ) : ?>
							<p class="splms-course-category-card__description"><?php echo esc_html( $category->description ); ?></p>
						<?php endif; ?>
						<?php if ( $attributes['showCount'] ) : ?>
							<span class="splms-course-category-card__count">
								<?php
								printf(
									/* translators: %d: Number of courses. */
									esc_html( _n( '%d Course', '%d Courses', $category->count, 'skillpulse-lms' ) ),
									(int) $category->count
								);
								?>
							</span>
						<?php endif; ?>
					</div>
				</a>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course search block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_search( $attributes ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by render_callback signature.
		ob_start();
		?>
		<div class="wp-block-splms-course-search">
			<?php splms_get_template_part( 'course/course-search' ); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render account info block.
	 *
	 * Displays a single user profile field for the currently logged-in user.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_account_info( $attributes ) {
		$field = isset( $attributes['field'] ) ? sanitize_key( $attributes['field'] ) : 'display_name';

		if ( ! is_user_logged_in() ) {
			return '<span class="splms-account-info splms-account-info--guest">' . esc_html__( 'Please log in to view account info.', 'skillpulse-lms' ) . '</span>';
		}

		$user    = wp_get_current_user();
		$user_id = $user->ID;
		$output  = '';

		switch ( $field ) {
			case 'display_name':
			case 'user_login':
			case 'user_email':
			case 'nickname':
				$output = $user->$field;
				break;

			case 'first_name':
				$output = get_user_meta( $user_id, 'first_name', true );
				break;

			case 'last_name':
				$output = get_user_meta( $user_id, 'last_name', true );
				break;

			case 'full_name':
				$first  = get_user_meta( $user_id, 'first_name', true );
				$last   = get_user_meta( $user_id, 'last_name', true );
				$output = trim( $first . ' ' . $last );
				if ( empty( $output ) ) {
					$output = $user->display_name;
				}
				break;

			case 'description':
				$output = get_user_meta( $user_id, 'description', true );
				break;

			case 'avatar':
				return '<span class="splms-account-info splms-account-info--avatar">' . get_avatar( $user_id, 96 ) . '</span>';

			case 'user_registered':
				$output = date_i18n( get_option( 'date_format' ), strtotime( $user->user_registered ) );
				break;

			case 'ID':
				$output = (string) $user_id;
				break;

			case 'user_role':
				$roles  = $user->roles;
				$output = ! empty( $roles ) ? ucfirst( reset( $roles ) ) : '';
				break;

			case 'enrolled_courses_count':
				$enrolled = splms_get_user_enrolled_courses( $user_id );
				$output   = is_array( $enrolled ) ? (string) count( $enrolled ) : '0';
				break;

			case 'completed_courses_count':
				$enrolled  = splms_get_user_enrolled_courses( $user_id );
				$completed = 0;
				if ( is_array( $enrolled ) ) {
					foreach ( $enrolled as $enrollment ) {
						if ( isset( $enrollment->status ) && 'completed' === $enrollment->status ) {
							++$completed;
						}
					}
				}
				$output = (string) $completed;
				break;

			case 'certificates_count':
				$certificates = get_user_meta( $user_id, '_splms_certificates', true );
				$output       = is_array( $certificates ) ? (string) count( $certificates ) : '0';
				break;

			default:
				$output = '';
				break;
		}

		if ( empty( $output ) ) {
			// Return empty span in editor context for preview, nothing on frontend.
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return '<span class="splms-account-info splms-account-info--empty">' . esc_html__( '(empty)', 'skillpulse-lms' ) . '</span>';
			}
			return '';
		}

		return '<span class="splms-account-info splms-account-info--' . esc_attr( $field ) . '">' . esc_html( $output ) . '</span>';
	}

	/**
	 * Render enroll button block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_enroll_button( $attributes ) {
		$course_id = isset( $attributes['courseId'] ) ? absint( $attributes['courseId'] ) : 0;

		if ( ! $course_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please select a course.', 'skillpulse-lms' ) . '</p>';
		}

		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type || 'publish' !== $course->post_status ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Course not found.', 'skillpulse-lms' ) . '</p>';
		}

		$user_id     = get_current_user_id();
		$is_enrolled = splms_is_user_enrolled( $course_id, $user_id );
		$access_info = splms_get_course_access_info( $course_id );

		ob_start();
		?>
		<div class="wp-block-splms-enroll-button">
			<?php
			if ( $is_enrolled ) {
				$status = splms_get_user_enrollment_status( $course_id, $user_id );
				if ( 'completed' === $status ) {
					echo '<a href="' . esc_url( get_permalink( $course_id ) ) . '" class="btn btn-primary">' . esc_html__( 'Completed', 'skillpulse-lms' ) . '</a>';
				} else {
					echo '<a href="' . esc_url( get_permalink( $course_id ) ) . '" class="btn btn-continue">' . esc_html__( 'Continue Learning', 'skillpulse-lms' ) . '</a>';
				}
			} elseif ( ! empty( $access_info['course_mode'] ) && 'paid' === $access_info['course_mode'] ) {
				echo wp_kses_post( splms_render_paid_course_button( $course_id, $user_id ) );
			} else {
				echo wp_kses_post( splms_render_free_course_button( $course_id, $user_id ) );
			}
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course curriculum block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_curriculum( $attributes ) {
		$course_id = isset( $attributes['courseId'] ) ? absint( $attributes['courseId'] ) : 0;

		if ( ! $course_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please select a course.', 'skillpulse-lms' ) . '</p>';
		}

		$course = get_post( $course_id );
		if ( ! $course || SPLMS_POST_TYPES['course'] !== $course->post_type ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Course not found.', 'skillpulse-lms' ) . '</p>';
		}

		ob_start();
		?>
		<div class="wp-block-splms-course-curriculum">
			<?php
			splms_get_template_part(
				'course/partials/tabs/curriculum',
				'',
				array(
					'splms_course_id'  => $course_id,
					'splms_expand_all' => ! empty( $attributes['expandAll'] ),
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course progress block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_progress( $attributes ) {
		$course_id = isset( $attributes['courseId'] ) ? absint( $attributes['courseId'] ) : 0;

		if ( ! $course_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please select a course.', 'skillpulse-lms' ) . '</p>';
		}

		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please log in to view progress.', 'skillpulse-lms' ) . '</p>';
		}

		// Check enrollment before calculating progress.
		if ( ! splms_is_user_enrolled( $course_id, $user_id ) ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'You are not enrolled in this course.', 'skillpulse-lms' ) . '</p>';
		}

		$progress_data = splms_get_course_progress_data( $user_id, $course_id );
		$label         = ! empty( $attributes['label'] ) ? sanitize_text_field( $attributes['label'] ) : __( 'Progress', 'skillpulse-lms' );

		ob_start();
		?>
		<div class="wp-block-splms-course-progress">
			<?php
			splms_get_template_part(
				'shared/progress-bar',
				'',
				array(
					'splms_progress_data'    => $progress_data,
					'splms_show_percentage'  => isset( $attributes['showPercentage'] ) ? $attributes['showPercentage'] : true,
					'splms_show_items_count' => isset( $attributes['showItemCount'] ) ? $attributes['showItemCount'] : true,
					'label'                  => $label,
				)
			);
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render my courses block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_my_courses( $attributes ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please log in to view your courses.', 'skillpulse-lms' ) . '</p>';
		}

		$defaults   = array(
			'columns'      => 3,
			'status'       => 'all',
			'showProgress' => true,
			'emptyMessage' => '',
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		// Get enrollment objects (not just IDs) so we can filter by status.
		$enrollments_query = SkillPulse_LMS_Enrollments_Query::get_instance();
		$query_args        = array( 'status' => array( 'active', 'completed' ) );

		// Filter by specific status if not "all".
		if ( 'all' !== $attributes['status'] ) {
			$query_args['status'] = array( sanitize_key( $attributes['status'] ) );
		}

		$enrollments = $enrollments_query->get_user_courses( $user_id, $query_args );
		if ( ! is_array( $enrollments ) || empty( $enrollments ) ) {
			$empty_msg = ! empty( $attributes['emptyMessage'] )
				? $attributes['emptyMessage']
				: __( 'You are not enrolled in any courses yet.', 'skillpulse-lms' );

			return '<div class="wp-block-splms-my-courses"><p class="splms-block-placeholder">' . esc_html( $empty_msg ) . '</p></div>';
		}

		$columns = absint( $attributes['columns'] );

		// Set layout prop for the grid.
		splms_set_loop_prop( 'layout', 'grid' );

		ob_start();
		?>
		<div class="wp-block-splms-my-courses">
			<?php
			splms_course_loop_start();

			foreach ( $enrollments as $enrollment ) {
				$course_id = isset( $enrollment->course_id ) ? absint( $enrollment->course_id ) : 0;
				if ( ! $course_id ) {
					continue;
				}

				$course_post = get_post( $course_id );
				if ( ! $course_post || 'publish' !== $course_post->post_status ) {
					continue;
				}

				// Set up post data so the template can use the_permalink(), the_title(), etc.
				// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Temporary override restored by wp_reset_postdata().
				$GLOBALS['post'] = $course_post;
				setup_postdata( $course_post );

				splms_get_template_part(
					'course/course',
					array( 'splms_course_id' => $course_id )
				);
			}

			splms_course_loop_end();
			wp_reset_postdata();
			?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render course instructor block.
	 *
	 * @since 1.0.0
	 *
	 * @param array $attributes Block attributes.
	 * @return string Rendered block HTML.
	 */
	public function render_course_instructor( $attributes ) {
		$instructor_id = isset( $attributes['instructorId'] ) ? absint( $attributes['instructorId'] ) : 0;

		if ( ! $instructor_id ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Please select an instructor.', 'skillpulse-lms' ) . '</p>';
		}

		$user = get_userdata( $instructor_id );
		if ( ! $user ) {
			return '<p class="splms-block-placeholder">' . esc_html__( 'Instructor not found.', 'skillpulse-lms' ) . '</p>';
		}

		$defaults   = array(
			'showBio'          => true,
			'showCourseCount'  => true,
			'showStudentCount' => true,
		);
		$attributes = wp_parse_args( $attributes, $defaults );

		// Get instructor data.
		$display_name = $user->display_name;
		$bio          = get_user_meta( $instructor_id, 'description', true );
		$avatar       = get_avatar( $instructor_id, 96 );

		// Count instructor's courses.
		$course_count = 0;
		if ( $attributes['showCourseCount'] ) {
			$instructor_courses = get_posts(
				array(
					'post_type'      => SPLMS_POST_TYPES['course'],
					'post_status'    => 'publish',
					'author'         => $instructor_id,
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);
			$course_count       = count( $instructor_courses );
		}

		// Count total students.
		$student_count = 0;
		if ( $attributes['showStudentCount'] ) {
			$student_count = SkillPulse_LMS_Enrollments_Query::get_instance()->get_author_student_count( $instructor_id );
		}

		ob_start();
		?>
		<div class="wp-block-splms-course-instructor splms-instructor-card">
			<div class="splms-instructor-card__avatar">
				<?php echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar returns safe HTML. ?>
			</div>
			<div class="splms-instructor-card__content">
				<h3 class="splms-instructor-card__name"><?php echo esc_html( $display_name ); ?></h3>

				<?php if ( $attributes['showBio'] && ! empty( $bio ) ) : ?>
					<p class="splms-instructor-card__bio"><?php echo esc_html( wp_trim_words( $bio, 30, '...' ) ); ?></p>
				<?php endif; ?>

				<div class="splms-instructor-card__stats">
					<?php if ( $attributes['showCourseCount'] ) : ?>
						<span class="splms-instructor-card__stat">
							<?php
							printf(
								/* translators: %d: Number of courses. */
								esc_html( _n( '%d Course', '%d Courses', $course_count, 'skillpulse-lms' ) ),
								(int) $course_count
							);
							?>
						</span>
					<?php endif; ?>

					<?php if ( $attributes['showStudentCount'] ) : ?>
						<span class="splms-instructor-card__stat">
							<?php
							printf(
								/* translators: %d: Number of students. */
								esc_html( _n( '%d Student', '%d Students', $student_count, 'skillpulse-lms' ) ),
								(int) $student_count
							);
							?>
						</span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
