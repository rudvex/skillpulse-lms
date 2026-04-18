<?php
/**
 * Archive template for courses
 *
 * This template follows WordPress theme conventions for archive pages.
 * It can be overridden by copying it to yourtheme/archive-course.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}



get_header();

$splms_courses_page_id = splms_get_course_page_id();

// Get archive setup data from centralized function.
$splms_setup_data = SkillPulse_LMS_Course_Frontend::get_course_archive_setup( 'archive' );
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file requires variable extraction for backwards compatibility.
extract( $splms_setup_data );
/**
 * Hook: splms_before_archive_content
 */
do_action( 'splms_before_archive_content' );
?>

<div class="splms-page-headign">
	<div class="splms-container">
		<div class="splms-archive-title">
			<h1 class="title">
				<?php
				if ( is_search() ) {
					// translators: %s: Search query.
					$splms_search_title = sprintf( __( 'Search Results for: %s', 'skillpulse-lms' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
					echo wp_kses_post( $splms_search_title );
				} elseif ( $splms_courses_page_id && ( is_page( $splms_courses_page_id ) || is_post_type_archive( SPLMS_POST_TYPES['course'] ) ) ) {
					// Use the courses page title if set, otherwise default.
					echo esc_html( get_the_title( $splms_courses_page_id ) );
				} else {
					esc_html_e( 'All Courses', 'skillpulse-lms' );
				}
				?>
			</h1>
			<p class="archive-subtitle"><?php esc_html_e( 'Browse all active courses available for you', 'skillpulse-lms' ); ?></p>
		</div>
	</div>
</div>
<div class="splms-container <?php echo esc_attr( implode( ' ', $splms_archive_classes ) ); ?>">
	<?php
	/**
	 * Hook: splms_archive_before_courses
	 */
	do_action( 'splms_archive_before_courses' );
	?>
	<div class="splms-courses-main">
		<?php
		if ( $splms_has_sidebar_filters ) {
			splms_get_template_part( 'course/course-sidebar-filters' );
		}
		?>
		<div class="splms-courses-content">
			<div class="splms-top-search-box">
				<div class="archive-controls-left">
					<?php
					/**
					 * Hook: splms_before_course_loop_item
					 */
					do_action( 'splms_before_course_loop_item' );
					?>
					<button class="splms-filter-button">
						<?php esc_html_e( 'Filters', 'skillpulse-lms' ); ?>
						<span class="icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
								<g clip-path="url(#clip0_204_34)">
									<path d="M4 4H20V6.172C19.9999 6.70239 19.7891 7.21101 19.414 7.586L15 12V19L9 21V12.5L4.52 7.572C4.18545 7.20393 4.00005 6.7244 4 6.227V4Z"
											stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
								</g>
								<defs>
									<clipPath id="clip0_204_34">
										<rect width="24" height="24" fill="white"/>
									</clipPath>
								</defs>
							</svg>
						</span>
					</button>
				</div>
				<div class="splms-search-layout">
					<?php
					splms_get_template_part( 'course/layout-switcher' );
					if ( splms_get_setting( 'course_search_enabled', true ) ) {
						splms_get_template_part( 'course/course-search' );
					}
					?>
				</div>
			</div>
			<?php if ( splms_course_loop() ) { ?>
				<?php
				/**
				 * Hook: splms_before_course_loop
				 */
				do_action( 'splms_before_course_loop' );
				?>
				<?php
				while ( have_posts() ) {
					the_post();
					splms_get_template_part( 'course/course' );
				}
				?>
				<?php
				/**
				 * Hook: splms_after_course_loop
				 */
				do_action( 'splms_after_course_loop' );
				?>
			<?php } else { ?>
				<?php splms_get_template_part( 'course/no-courses' ); ?>
			<?php } ?>
		</div>
	</div>
</div>
<?php
/**
 * Hook: splms_after_archive_content
 */
do_action( 'splms_after_archive_content' );

get_footer();
?>
