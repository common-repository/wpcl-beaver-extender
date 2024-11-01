<?php

/**
 * The plugin file that controls the admin functions
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Classes;

class Frontend extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber {


	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'wp_enqueue_scripts' => 'enqueue_scripts' ),
			array( 'wp_enqueue_scripts' => 'enqueue_styles' ),
		);
	}


	public function enqueue_scripts() {
		if( class_exists( 'FLBuilderModel' ) && \FLBuilderModel::is_builder_active() ) {
			// Enqueue public script
			wp_enqueue_script( 'wpcl_beaver_extender_public', self::url( 'js/public.min.js' ), array( 'jquery' ), self::$version, true );
		}
	}

	public function enqueue_styles() {
		if( class_exists( 'FLBuilderModel' ) && \FLBuilderModel::is_builder_active() ) {
			// Enqueue public script
			wp_enqueue_style( 'wpcl_beaver_extender_editor', self::url( 'css/editor.css' ), array( ), self::$version, 'all' );
		}

	}

} // end class