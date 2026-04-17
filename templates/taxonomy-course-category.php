<?php
/**
 * Course category taxonomy archive template
 *
 * This template follows WordPress theme conventions for taxonomy archives.
 * It can be overridden by copying it to yourtheme/taxonomy-course-category.php.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();

// Get the current term object.
$current_term = get_queried_object();

// Get archive setup data from centralized function.
$setup_data = SkillPulse_LMS_Course_Frontend::get_course_archive_setup( 'category', $current_term );
// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template file uses extract for convenience with controlled data.
extract( $setup_data );


/**
 * Hook: splms_before_archive_content
 */
do_action( 'splms_before_archive_content' );
?>

<div class="splms-page-headign">
	<div class="splms-container">
		<div class="splms-breadcrumb-wrapper">
			<div class="splms-breadcrumb-container">
				<nav class="splms-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb Navigation', 'skillpulse-lms' ); ?>">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="splms-breadcrumb__link">
						<?php esc_html_e( 'Home', 'skillpulse-lms' ); ?>
					</a>
					<span class="splms-breadcrumb__separator">
						<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="9,18 15,12 9,6"></polyline>
						</svg>
					</span>
					<?php
					$courses_page_id = splms_get_course_page_id();
					if ( $courses_page_id ) {
						?>
						<a href="<?php echo esc_url( get_permalink( $courses_page_id ) ); ?>"  class="splms-breadcrumb__link">
							<?php echo esc_html( get_the_title( $courses_page_id ) ); ?>
						</a>
						<span class="splms-breadcrumb__separator">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="9,18 15,12 9,6"></polyline>
							</svg>
						</span>
						<?php
					}
					?>
					<span class="current"><?php echo esc_html( single_term_title( '', false ) ); ?></span>
				</nav>
			</div>
		</div>
		<div class="splms-archive-title">
			<h1 class="title" id="main-heading" tabindex="-1">
				<?php echo esc_html( single_term_title( '', false ) ); ?>
				<?php
				$course_count = $current_term->count;
				if ( $course_count > 0 ) {
					?>
					<?php /* translators: %d: Number of courses available */ ?>
					<span class="course-count" aria-label="<?php echo esc_attr( sprintf( _n( '%d course available', '%d courses available', $course_count, 'skillpulse-lms' ), absint( $course_count ) ) ); ?>">
						<?php
						/* translators: %d: Number of courses */
						echo esc_html( sprintf( _n( '(%d course)', '(%d courses)', $course_count, 'skillpulse-lms' ), absint( $course_count ) ) );
						?>
					</span>
					<?php
				}
				?>
			</h1>
			<?php if ( $current_term && $current_term->description ) { ?>
				<p class="archive-subtitle"><?php echo esc_html( wp_strip_all_tags( $current_term->description ) ); ?></p>
			<?php } else { ?>
				<p class="archive-subtitle">
					<?php
					/* translators: %s: Category name */
					printf( esc_html__( 'Explore all courses in %s category', 'skillpulse-lms' ), esc_html( single_term_title( '', false ) ) );
					?>
				</p>
			<?php } ?>
		</div>
	</div>
</div>

<div class="splms-container <?php echo esc_attr( implode( ' ', $archive_classes ) ); ?>">
	<?php
	/**
	 * Hook: splms_archive_before_courses
	 */
	do_action( 'splms_archive_before_courses' );
	?>
	<div class="splms-courses-main">
		<?php
		if ( $has_sidebar_filters ) {
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
