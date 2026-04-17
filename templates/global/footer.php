<?php
/**
 * SkillPulse LMS Global Footer Template
 *
 * This template can be overridden by copying it to yourtheme/skillpulse-lms/global/footer.php.
 * This won't conflict with WordPress theme hierarchy or block themes.
 *
 * @package SkillPulse_LMS
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Hook: splms_before_get_footer
 */
do_action( 'splms_before_get_footer' );
?>
	</main>

	<footer class="splms-footer site-footer" role="contentinfo">
		<?php splms_render_footer_content(); ?>
	</footer>

</div><!-- .splms-site-wrapper -->

<?php
/**
 * Hook: splms_before_wp_footer
 */
do_action( 'splms_before_wp_footer' );



wp_footer();
?>
</body>
</html>
<?php
/**
 * Hook: splms_after_get_footer
 */
do_action( 'splms_after_get_footer' );
?>
