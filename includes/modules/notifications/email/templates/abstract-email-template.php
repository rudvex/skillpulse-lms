<?php
/**
 * Abstract Email Template Class
 *
 * @package SkillPulse LMS
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Abstract Email Template Class
 *
 * This abstract class provides a foundation for managing email templates.
 * Extend this class to register new email templates.
 *
 * @since 1.0.0
 */
abstract class SPLMS_Abstract_Email_Template {

	/**
	 * Template key (unique identifier)
	 *
	 * @var string
	 */
	protected $template_key;

	/**
	 * Template name
	 *
	 * @var string
	 */
	protected $template_name;

	/**
	 * Template subject
	 *
	 * @var string
	 */
	protected $template_subject;

	/**
	 * Template content
	 *
	 * @var string
	 */
	protected $template_content;

	/**
	 * Is template active
	 *
	 * @var bool
	 */
	protected $is_active = true;

	/**
	 * Template description
	 *
	 * @var string
	 */
	protected $description = '';

	/**
	 * Available placeholders for this template
	 *
	 * @var array
	 */
	protected $placeholders = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->init_template();
		$this->register_template();
	}

	/**
	 * Initialize template properties.
	 * Override this method in child classes.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	abstract protected function init_template();

	/**
	 * Get template data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Template data.
	 */
	public function get_template_data() {
		return array(
			'template_key' => $this->get_template_key(),
			'name'         => $this->get_template_name(),
			'subject'      => $this->get_template_subject(),
			'content'      => $this->get_template_content(),
			'is_active'    => $this->is_active(),
			'description'  => $this->get_description(),
			'placeholders' => $this->get_placeholders(),
		);
	}

	/**
	 * Register template with the system.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function register_template() {
		add_filter( 'splms_email_templates', array( $this, 'add_template' ) );
	}

	/**
	 * Add template to the templates array.
	 *
	 * @since 1.0.0
	 *
	 * @param array $templates Existing templates.
	 * @return array Templates array with new template added.
	 */
	public function add_template( $templates ) {
		$templates[ $this->template_key ] = $this->get_template_data();
		return $templates;
	}

	/**
	 * Get template key.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template key.
	 */
	public function get_template_key() {
		return $this->template_key;
	}

	/**
	 * Set template key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Template key.
	 * @return void
	 */
	public function set_template_key( $key ) {
		$this->template_key = $key;
	}

	/**
	 * Get template name.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template name.
	 */
	public function get_template_name() {
		return $this->template_name;
	}

	/**
	 * Set template name.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Template name.
	 * @return void
	 */
	public function set_template_name( $name ) {
		$this->template_name = $name;
	}

	/**
	 * Get template subject.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template subject.
	 */
	public function get_template_subject() {
		return $this->template_subject;
	}

	/**
	 * Set template subject.
	 *
	 * @since 1.0.0
	 *
	 * @param string $subject Template subject.
	 * @return void
	 */
	public function set_template_subject( $subject ) {
		$this->template_subject = $subject;
	}

	/**
	 * Get template content.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template content.
	 */
	public function get_template_content() {
		return $this->template_content;
	}

	/**
	 * Set template content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content Template content.
	 * @return void
	 */
	public function set_template_content( $content ) {
		$this->template_content = $content;
	}

	/**
	 * Is template active.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function is_active() {
		return $this->is_active;
	}

	/**
	 * Get template description.
	 *
	 * @since 1.0.0
	 *
	 * @return string Template description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Set template description.
	 *
	 * @since 1.0.0
	 *
	 * @param string $description Template description.
	 * @return void
	 */
	public function set_description( $description ) {
		$this->description = $description;
	}

	/**
	 * Get available placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @return array Placeholders array.
	 */
	public function get_placeholders() {
		return $this->placeholders;
	}

	/**
	 * Set template placeholders.
	 *
	 * @since 1.0.0
	 *
	 * @param array $placeholders Placeholders array.
	 * @return void
	 */
	public function set_placeholders( $placeholders ) {
		$this->placeholders = $placeholders;
	}
}
