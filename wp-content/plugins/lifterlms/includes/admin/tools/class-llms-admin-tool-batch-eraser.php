<?php
/**
 * Admin tool to delete pending batches created by a background processor
 *
 * @package LifterLMS/Admin/Tools/Classes
 *
 * @since 3.37.19
 * @version 3.37.19
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Tool_Batch_Eraser
 *
 * @since 3.37.19
 */
class LLMS_Admin_Tool_Batch_Eraser extends LLMS_Abstract_Admin_Tool {

	/**
	 * Tool ID.
	 *
	 * @var string
	 */
	protected $id = 'batch-eraser';

	/**
	 * Retrieve a description of the tool
	 *
	 * This is displayed on the right side of the tool's list before the button.
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	protected function get_description() {

		$count = $this->get_pending_batches();

		$desc  = __( 'Deletes pending batches generated by LifterLMS background processors.', 'lifterlms' );
		$desc .= ' ';
		// Translators: %d = the number of pending batches.
		$desc .= sprintf(
			_n(
				'There is currently %d pending batch that will be deleted.',
				'There are currently %d pending batches that will be deleted.',
				$count,
				'lifterlms'
			),
			$count
		);

		return $desc;

	}

	/**
	 * Retrieve the tool's label
	 *
	 * The label is the tool's title. It's displayed in the left column on the tool's list.
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	protected function get_label() {
		return __( 'Delete processor batches', 'lifterlms' );
	}

	/**
	 * Retrieve the tool's button text
	 *
	 * @since 3.37.19
	 *
	 * @return string
	 */
	protected function get_text() {
		return __( 'Delete batches', 'lifterlms' );
	}

	/**
	 * Retrieve the number of pending batches.
	 *
	 * @since 3.37.19
	 *
	 * @return int
	 */
	protected function get_pending_batches() {

		$count = wp_cache_get( $this->id, 'llms_tool_data' );
		if ( false === $count ) {

			global $wpdb;
			$count = absint( $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '%llms_%_batch_%';" ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

			wp_cache_set( $this->id, $count, 'llms_tool_data' );

		}

		return $count;

	}

	/**
	 * Process the tool.
	 *
	 * This method should do whatever the tool actually does.
	 *
	 * By the time this tool is called a nonce and the user's capabilities have already been checked.
	 *
	 * @since 3.37.19
	 *
	 * @return mixed
	 */
	protected function handle() {

		global $wpdb;
		$res = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%llms_%_batch_%';" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		wp_cache_delete( $this->id, 'llms_tool_data' );
		return $res > 0;

	}

	/**
	 * Conditionally load the tool
	 *
	 * This tool should only load if there's batches in the database.
	 *
	 * @since 3.37.19
	 *
	 * @return boolean Return `true` to load the tool and `false` to not load it.
	 */
	protected function should_load() {

		return $this->get_pending_batches() > 0;

	}

}

return new LLMS_Admin_Tool_Batch_Eraser();
