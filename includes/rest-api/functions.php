<?php
/**
 * SkillPulse LMS REST API Functions
 *
 * Core utility functions for the REST API module.
 * Contains only functions that are actively used by REST API controllers.
 *
 * @package SkillPulse_LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'splms_rest_namespace' ) ) {

	/**
	 * SkillPulse LMS REST API namespace.
	 *
	 * @since 1.0.0
	 *
	 * @return string REST API namespace.
	 */
	function splms_rest_namespace() {
		$namespace = 'splms';

		/**
		 * Filter API namespace.
		 *
		 * @since 1.0.0
		 *
		 * @param string $namespace SkillPulse LMS core namespace. Default 'splms'.
		 *
		 * @return string Filtered REST API namespace.
		 */
		return apply_filters( 'splms_rest_namespace', $namespace );
	}
}


if ( ! function_exists( 'splms_rest_version' ) ) {

	/**
	 * SkillPulse LMS REST API version.
	 *
	 * @since 1.0.0
	 *
	 * @return string REST API version.
	 */
	function splms_rest_version() {
		/**
		 * Filter API version.
		 *
		 * @since 1.0.0
		 *
		 * @param string $version SkillPulse LMS core version. Default 'v1'.
		 *
		 * @return string Filtered REST API version.
		 */
		return apply_filters( 'splms_rest_version', 'v1' );
	}
}

if ( ! function_exists( 'splms_rest_url' ) ) {

	/**
	 * Get REST API URL for a specific endpoint.
	 *
	 * Utility function for building REST API URLs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $endpoint Endpoint path.
	 *
	 * @return string Full REST API URL.
	 */
	function splms_rest_url( $endpoint = '' ) {
		$namespace = splms_rest_namespace() . '/' . splms_rest_version();
		$endpoint  = ltrim( $endpoint, '/' );

		return rest_url( $namespace . '/' . $endpoint );
	}
}
