<?php

class WC_Pay_By_Paynow_Pl_Page {

	public const CONFIRM_BLIK_PAYMENT_ID = 'confirm_blik_payment';

	private $id;
	private $guid;
	private $title;
	private $name;

	public function __construct( $name ) {
		$this->name = $name;

		$post = get_post( get_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_id' ) );
		if ( $post != null ) {
			$this->id    = $post->ID;
			$this->title = $post->post_title;
			$this->guid  = $post->guid;
		}
	}

	/**
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function get_guid(): ?string {
		return $this->guid;
	}

	/**
	 * @return string
	 */
	public function get_title(): ?string {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function get_name(): ?string {
		return $this->name;
	}

	/**
	 * @param string $title
	 */
	public function set_title( string $title ): void {
		$this->title = $title;
	}

	/**
	 * @param string $guid
	 */
	public function set_guid( string $guid ) {
		$this->guid = $guid;
	}

	/**
	 * @return false|string|WP_Error
	 */
	public function get_url() {
		return get_permalink( $this->id );
	}

	/**
	 * Add page to database
	 *
	 * @global type $wpdb WPDB object
	 */
	public function add() {
		global $wpdb;

		delete_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_title' );
		add_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_title', $this->title, '', 'yes' );

		$page = get_page_by_title( $this->title );

		if ( ! $page ) {
			$info                   = array();
			$info['post_title']     = $this->title;
			$info['post_content']   = '[' . WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . 'content]';
			$info['post_status']    = 'publish';
			$info['post_type']      = 'page';
			$info['comment_status'] = 'closed';
			$info['ping_status']    = 'closed';
			$info['guid']           = $this->guid;
			$info['post_category']  = array( 1 );

			$pageId = wp_insert_post( $info );
		} else {
			$page->post_status = 'publish';
			$pageId            = wp_update_post( $page );
		}

		delete_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_id' );
		add_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_id', $pageId );
	}

	/**
	 * Remove page
	 *
	 * @global type $wpdb WPDB object
	 */
	public function remove() {
		global $wpdb;

		if ( $this->id ) {
			wp_delete_post( $this->id, true );
			delete_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_title' );
			delete_option( WC_PAY_BY_PAYNOW_PL_PLUGIN_PREFIX . $this->name . '_id' );
		}
	}
}
