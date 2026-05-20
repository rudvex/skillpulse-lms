<?php
/**
 * Relationships Query Class
 *
 * Handles database queries for course item relationships.
 *
 * @package SkillPulse_LMS
 * @subpackage Courses
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! class_exists( 'SPLMS_Base_Query' ) ) {
	require_once SPLMS_DIR_PATH . 'includes/modules/core/base/class-base-query.php';
}

/**
 * Relationships Query Class
 *
 * @since 1.0.0
 */
class SPLMS_Relationships_Query extends SPLMS_Base_Query {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $table_name Table name.
	 */
	protected function __construct( $table_name ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found -- Constructor needed to call parent.
		parent::__construct( $table_name );
	}

	/**
	 * Get the instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return SPLMS_Relationships_Query The class instance.
	 */
	public static function get_instance() {
		return parent::get_base_instance( __CLASS__, 'splms_relationships' );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'splms_delete_course_item', array( $this, 'handle_course_item_deletion' ) );
	}

	/**
	 * Handle course item deletion.
	 *
	 * @since 1.0.0
	 *
	 * @param int $item_id Item ID.
	 * @return void
	 */
	public function handle_course_item_deletion( $item_id ) {
		$children = $this->get_children( $item_id );
		foreach ( $children as $child ) {
			// Delete all relationships where this is the child.
			$this->delete_relationship( $item_id, $child->child_id );
		}
	}

	/**
	 * Insert a new relationship.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $parent_id   Parent ID.
	 * @param int    $child_id    Child ID.
	 * @param string $child_type  Child type.
	 * @param int    $order_index Order index.
	 * @return int|false Relationship ID on success, false on failure.
	 */
	public function insert_relationship( $parent_id, $child_id, $child_type, $order_index ) {
		// Check if relationship already exists to prevent duplicates.
		$existing = $this->get_relationship( $parent_id, $child_id, $child_type );
		if ( $existing ) {
			// Update order if different.
			if ( (int) $existing->order_index !== (int) $order_index ) {
				return $this->update_relationship_order( $parent_id, $child_id, $order_index );
			}

			// Return existing relationship ID.
			return $existing->id;
		}

		return $this->insert(
			array(
				'parent_id'   => $parent_id,
				'child_id'    => $child_id,
				'child_type'  => $child_type,
				'order_index' => $order_index,
			),
			array( '%d', '%d', '%s', '%d' )
		);
	}

	/**
	 * Get a relationship between parent and child.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $parent_id   Parent ID.
	 * @param int    $child_id    Child ID.
	 * @param string $child_type  Child type (optional).
	 * @return object|null Relationship object or null if not found.
	 */
	public function get_relationship( $parent_id, $child_id, $child_type = null ) {
		if ( $child_type ) {
			$query = "SELECT * FROM {$this->table_name} WHERE parent_id = %d AND child_id = %d AND child_type = %s";
			return $this->get_row( $query, array( $parent_id, $child_id, $child_type ) );
		} else {
			$query = "SELECT * FROM {$this->table_name} WHERE parent_id = %d AND child_id = %d";
			return $this->get_row( $query, array( $parent_id, $child_id ) );
		}
	}

	/**
	 * Update relationship order.
	 *
	 * @since 1.0.0
	 *
	 * @param int $parent_id Parent ID.
	 * @param int $child_id  Child ID.
	 * @param int $new_order New order index.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function update_relationship_order( $parent_id, $child_id, $new_order ) {
		return $this->update(
			array( 'order_index' => $new_order ),
			array(
				'parent_id' => $parent_id,
				'child_id'  => $child_id,
			),
			array( '%d' ),
			array( '%d', '%d' )
		);
	}

	/**
	 * Delete a relationship.
	 *
	 * @since 1.0.0
	 *
	 * @param int $parent_id Parent ID.
	 * @param int $child_id  Child ID.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function delete_relationship( $parent_id, $child_id ) {
		return $this->delete(
			array(
				'parent_id' => $parent_id,
				'child_id'  => $child_id,
			),
			array( '%d', '%d' )
		);
	}

	/**
	 * Get all children of a parent.
	 *
	 * @since 1.0.0
	 *
	 * @param int         $parent_id  Parent ID.
	 * @param string|null $child_type Optional child type filter.
	 * @return array Array of child objects.
	 */
	public function get_children( $parent_id, $child_type = null ) {
		$query = "SELECT * FROM {$this->table_name} WHERE parent_id = %d";
		if ( $child_type ) {
			$query .= ' AND child_type = %s ORDER BY order_index ASC';

			return $this->get_results( $query, array( $parent_id, $child_type ) );
		}

		$query .= ' ORDER BY order_index ASC';

		return $this->get_results( $query, array( $parent_id ) );
	}

	/**
	 * Get all parents of a child.
	 *
	 * @since 1.0.0
	 *
	 * @param int $child_id Child ID.
	 * @return array Array of parent objects.
	 */
	public function get_parents( $child_id ) {
		$query = "SELECT parent_id FROM {$this->table_name} WHERE child_id = %d";

		return $this->get_results( $query, array( $child_id ) );
	}

	/**
	 * Delete all children of a parent.
	 *
	 * @since 1.0.0
	 *
	 * @param int $parent_id Parent ID.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function delete_children( $parent_id ) {
		return $this->delete(
			array( 'parent_id' => $parent_id ),
			array( '%d' )
		);
	}

	/**
	 * Delete all parents of a child.
	 *
	 * @since 1.0.0
	 *
	 * @param int $child_id Child ID.
	 * @return int|false Number of rows affected on success, false on failure.
	 */
	public function delete_parents( $child_id ) {
		return $this->delete(
			array( 'child_id' => $child_id ),
			array( '%d' )
		);
	}
}
