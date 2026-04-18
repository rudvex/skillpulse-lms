<?php
/**
 * Template Name: Dashboard Page
 *
 * Modern dashboard template with full-page layout
 * without standard WordPress title and content areas
 *
 * This template can be overridden by copying it to:
 * your-theme/page-dashboard.php
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



// Get dashboard instance and data.
$splms_dashboard = SkillPulse_LMS_Dashboard::get_instance();

// Check if user is logged in.
if ( ! is_user_logged_in() ) {
	splms_get_template( 'login-required.php' );
	return;
}

// Extract user data for convenience.
$splms_user       = wp_get_current_user();
$splms_user_name  = $splms_user->display_name ? $splms_user->display_name : $splms_user->user_login;
$splms_first_name = $splms_user->first_name ? $splms_user->first_name : explode( ' ', $splms_user_name )[0];
$splms_email      = $splms_user->user_email;
$splms_user_roles = $splms_user->roles;
$splms_user_role  = ! empty( $splms_user_roles ) ? ucfirst( str_replace( '_', ' ', $splms_user_roles[0] ) ) : __( 'Student', 'skillpulse-lms' );

// Get dashboard service and additional data.
$splms_dashboard_service = $splms_dashboard->get_service();

// Get dashboard data and current tab.
$splms_dashboard_data = $splms_dashboard_service->get_dashboard_data( $splms_user->ID );
$splms_current_tab    = $splms_dashboard->get_current_tab();

// Extract data for template.
$splms_user_stats            = $splms_dashboard_data['user_stats'];
$splms_enrolled_courses      = $splms_dashboard_data['enrolled_courses'];
$splms_user_metrics          = $splms_dashboard_data['user_metrics'];
$splms_recommended_courses   = $splms_dashboard_data['recommended_courses'];
$splms_completion_percentage = $splms_dashboard_data['completion_percentage'];

// Stats for template.
$splms_total_courses     = $splms_user_stats['total_enrolled'];
$splms_completed_courses = $splms_user_stats['completed_courses'];
$splms_average_score     = $splms_user_metrics['average_score'];
$splms_attendance_rate   = $splms_user_metrics['attendance_rate'];
$splms_learning_streak   = $splms_user_metrics['learning_streak'];
$splms_today_minutes     = $splms_user_metrics['today_minutes'];

// Get tab information and dashboard display data.
$splms_tab_info              = $splms_dashboard_service->get_tab_info();
$splms_dashboard_courses     = $splms_dashboard_service->get_dashboard_courses( $splms_user->ID );
$splms_dashboard_recommended = $splms_recommended_courses;

get_header();

/**
 * Hook: splms_before_dashboard_content
 */
do_action( 'splms_before_dashboard_content' );
?>

<div class="splms-container">
	<div class="splms-dashboard-wrapper splms-modern">
		<div class="splms-dashboard-container">
			<!-- Sidebar -->
			<aside class="splms-sidebar">
				<div class="splms-user-profile">
					<div class="splms-avatar">
						<?php echo get_avatar( $splms_user->ID, 48 ); ?>
					</div>
					<div class="splms-user-info">
						<h3><?php echo esc_html( $splms_user_name ); ?></h3>
						<span><?php echo esc_html( $splms_user_role ); ?></span>
					</div>
				</div>

				<ul class="splms-nav-menu">
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( '' ) ); ?>"
							class="splms-nav-link<?php echo ( ! $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-home-03"></i>
							<?php esc_html_e( 'Dashboard', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'my-courses' ) ); ?>"
							class="splms-nav-link<?php echo ( 'my-courses' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-book-open-01"></i>
							<?php esc_html_e( 'My Courses', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'wishlist' ) ); ?>"
							class="splms-nav-link<?php echo ( 'wishlist' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-favourite"></i>
							<?php esc_html_e( 'Wishlist', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'bookmarks' ) ); ?>"
							class="splms-nav-link<?php echo ( 'bookmarks' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-bookmark-01"></i>
							<?php esc_html_e( 'Bookmarks', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'certificates' ) ); ?>"
							class="splms-nav-link<?php echo ( 'certificates' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-certificate-01"></i>
							<?php esc_html_e( 'Certificates', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'notifications' ) ); ?>"
							class="splms-nav-link<?php echo ( 'notifications' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-notification-03"></i>
							<?php esc_html_e( 'Notifications', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'orders' ) ); ?>"
							class="splms-nav-link<?php echo ( 'orders' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-clipboard"></i>
							<?php esc_html_e( 'Orders', 'skillpulse-lms' ); ?>
						</a>
					</li>
					<li class="splms-nav-item">
						<a href="<?php echo esc_url( $splms_dashboard->get_tab_url( 'settings' ) ); ?>"
							class="splms-nav-link<?php echo ( 'settings' === $splms_current_tab ) ? ' active' : ''; ?>">
							<i class="hgi-stroke hgi-settings-02"></i>
							<?php esc_html_e( 'Settings', 'skillpulse-lms' ); ?>
						</a>
					</li>
				</ul>

			</aside>

			<!-- Main Content -->
			<main class="splms-main-content">
				<?php if ( $splms_current_tab && isset( $splms_tab_info[ $splms_current_tab ] ) ) : ?>
					<!-- Load specific tab content -->
					<?php
					// Check for special routes like order details.
					$splms_dashboard_action = get_query_var( 'splms_dashboard_action' );
					$splms_dashboard_id     = get_query_var( 'splms_dashboard_id' );

					// Handle order details route.
					if ( 'orders' === $splms_current_tab && 'view' === $splms_dashboard_action && $splms_dashboard_id ) {
						$splms_template_name = 'dashboard/order-details';

						// Prepare args for order details template.
						$args = array(
							'splms_user_id'        => $splms_user->ID,
							'splms_user_name'      => $splms_user_name,
							'splms_user_email'     => $splms_email,
							'splms_user_role'      => $splms_user_role,
							'splms_dashboard_data' => $splms_dashboard_data,
							'splms_current_tab'    => $splms_current_tab,
							'splms_order_id'       => intval( $splms_dashboard_id ),
						);
					} else {
						// Normal tab template.
						$splms_template_name = 'dashboard/' . $splms_tab_info[ $splms_current_tab ]['template'];

						// Prepare args for tab template.
						$args = array(
							'splms_user_id'        => $splms_user->ID,
							'splms_user_name'      => $splms_user_name,
							'splms_user_email'     => $splms_email,
							'splms_user_role'      => $splms_user_role,
							'splms_dashboard_data' => $splms_dashboard_data,
							'splms_current_tab'    => $splms_current_tab,
						);
					}

					echo '<div class="splms-dashboard-tab-content">';
					splms_get_template( $splms_template_name . '.php', $args );
					echo '</div>';
					?>
				<?php else : ?>
					<!-- Dashboard Overview Content -->

					<!-- Greeting Section -->
					<div class="splms-greeting-card">
						<div class="splms-greeting-text">
							<h1>
							<?php
								printf(
								/* translators: %s: First name */
									esc_html__( 'Welcome back, %s! 👋', 'skillpulse-lms' ),
									esc_html( $splms_first_name )
								);
							?>
								</h1>
							<p>
								<?php
								printf(
								/* translators: %d: minutes learned today */
									esc_html__( "You've learned for %d minutes today. Keep it up!", 'skillpulse-lms' ),
									esc_html( $splms_today_minutes )
								);
								?>
							</p>
						</div>
						<div class="splms-streak-badge">
							<i class="hgi-stroke hgi-fire" style="font-size: 32px; color: #FCD34D;"></i>
							<div>
								<div class="splms-streak-count">
									<?php
									printf(
										/* translators: %d: number of streak days */
										esc_html__( '%d Days', 'skillpulse-lms' ),
										esc_html( $splms_learning_streak )
									);
									?>
								</div>
								<div class="splms-streak-label"><?php esc_html_e( 'Learning Streak', 'skillpulse-lms' ); ?></div>
							</div>
						</div>
					</div>

					<!-- Stats Overview -->
					<div>
						<div class="splms-section-title">
							<i class="hgi-stroke hgi-chart-average"></i>
							<?php esc_html_e( 'Progress Overview', 'skillpulse-lms' ); ?>
						</div>
						<div class="splms-stats-grid">
							<div class="splms-stat-card">
								<div class="splms-circular-chart" style="--percentage: <?php echo esc_attr( $splms_completion_percentage ); ?>%">
									<div class="splms-chart-value"><?php echo esc_html( $splms_completion_percentage ); ?>%</div>
								</div>
								<div class="splms-stat-label"><?php esc_html_e( 'Course Completion', 'skillpulse-lms' ); ?></div>
								<div class="splms-stat-sub">
									<?php
									printf(
										/* translators: %1$d: completed courses, %2$d: total courses */
										esc_html__( '%1$d/%2$d Courses', 'skillpulse-lms' ),
										esc_html( $splms_completed_courses ),
										esc_html( $splms_total_courses )
									);
									?>
								</div>
							</div>

							<div class="splms-stat-card">
								<div class="splms-circular-chart" style="--percentage: <?php echo esc_attr( $splms_average_score ); ?>%; --splms-primary: #10B981">
									<div class="splms-chart-value" style="color: #10B981"><?php echo esc_html( $splms_average_score ); ?>%</div>
								</div>
								<div class="splms-stat-label"><?php esc_html_e( 'Quiz Performance', 'skillpulse-lms' ); ?></div>
								<div class="splms-stat-sub"><?php esc_html_e( 'Average Quiz Score', 'skillpulse-lms' ); ?></div>
							</div>

							<div class="splms-stat-card">
								<div class="splms-circular-chart" style="--percentage: <?php echo esc_attr( $splms_attendance_rate ); ?>%; --splms-primary: #F59E0B">
									<div class="splms-chart-value" style="color: #F59E0B"><?php echo esc_html( $splms_attendance_rate ); ?>%</div>
								</div>
								<div class="splms-stat-label"><?php esc_html_e( 'Attendance', 'skillpulse-lms' ); ?></div>
								<div class="splms-stat-sub"><?php esc_html_e( 'Last 30 Days', 'skillpulse-lms' ); ?></div>
							</div>
						</div>
					</div>

					<!-- Active Courses -->
					<div>
						<div class="splms-section-title">
							<i class="hgi-stroke hgi-book-open-01"></i>
							<?php esc_html_e( 'Continue Learning', 'skillpulse-lms' ); ?>
						</div>
						<div class="splms-course-list">
							<?php
							$splms_course_colors = array( '#4F46E5', '#EC4899', '#10B981', '#F59E0B' );
							$splms_course_icons  = array( 'hgi-computer-check', 'hgi-paint-board', 'hgi-book-open-01', 'hgi-code-circle' );

							foreach ( $splms_dashboard_courses as $splms_index => $splms_course ) :
								$splms_color = $splms_course_colors[ $splms_index % count( $splms_course_colors ) ];
								$splms_icon  = $splms_course_icons[ $splms_index % count( $splms_course_icons ) ];
								?>
								<div class="splms-course-card">
									<div class="splms-course-icon" style="color: <?php echo esc_attr( $splms_color ); ?>; background-color: <?php echo esc_attr( $splms_color ); ?>20;">
										<i class="hgi-stroke <?php echo esc_attr( $splms_icon ); ?>"></i>
									</div>
									<div class="splms-course-info">
										<h3 class="splms-course-title"><?php echo esc_html( $splms_course['title'] ); ?></h3>
										<div class="splms-progress-bar">
											<div class="splms-progress-fill" style="width: <?php echo esc_attr( $splms_course['progress'] ); ?>%; background-color: <?php echo esc_attr( $splms_color ); ?>;"></div>
										</div>
										<div class="splms-progress-text">
											<?php
											printf(
												/* translators: %1$d: progress percentage, %2$s: remaining time */
												esc_html__( '%1$d%% Completed • %2$s', 'skillpulse-lms' ),
												esc_html( $splms_course['progress'] ),
												esc_html( $splms_course['remaining'] )
											);
											?>
										</div>
									</div>
									<a href="<?php echo esc_url( $splms_course['url'] ); ?>" class="splms-btn splms-btn-primary">
										<?php esc_html_e( 'Continue', 'skillpulse-lms' ); ?> <i class="hgi-stroke hgi-arrow-right-01"></i>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Recommended -->
					<div>
						<div class="splms-section-title">
							<i class="hgi-stroke hgi-star"></i>
							<?php esc_html_e( 'Recommended For You', 'skillpulse-lms' ); ?>
						</div>
						<div class="splms-recommendations-grid">
							<?php
							foreach ( $splms_dashboard_recommended as $splms_index => $splms_course ) :
								$splms_enrollment_display = is_numeric( $splms_course['enrollments'] ) && $splms_course['enrollments'] > 999 ?
									number_format( $splms_course['enrollments'] / 1000, 1 ) . 'k' :
									$splms_course['enrollments'];

								?>
								<div class="splms-rec-card">
									<div class="splms-rec-image">
										<?php if ( ! empty( $splms_course['thumbnail'] ) ) { ?>
											<img src="<?php echo esc_url( $splms_course['thumbnail'] ); ?>" alt="<?php echo esc_attr( $splms_course['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
										<?php } ?>
										<?php if ( ! empty( $splms_course['tag'] ) ) { ?>
											<span class="splms-rec-tag"><?php echo esc_html( $splms_course['tag'] ); ?></span>
										<?php } ?>
									</div>
									<div class="splms-rec-content">
										<h4 class="splms-rec-title"><?php echo esc_html( $splms_course['title'] ); ?></h4>
										<div class="splms-rec-meta">
											<span>
												<i class="hgi-stroke hgi-clock-01"></i>
												<?php echo esc_html( $splms_course['duration'] ); ?>
											</span>
											<span>
												<i class="hgi-stroke hgi-user-group"></i>
												<?php echo esc_html( $splms_enrollment_display ); ?>
											</span>
										</div>
										<a href="<?php echo esc_url( $splms_course['url'] ); ?>" class="splms-btn splms-btn-outline">
											<?php esc_html_e( 'View Course', 'skillpulse-lms' ); ?>
										</a>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

				<?php endif; ?>
			</main>
		</div>
	</div>
</div>

<?php
/**
 * Hook: splms_after_dashboard_content
 */
do_action( 'splms_after_dashboard_content' );

get_footer();