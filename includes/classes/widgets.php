<?php

/**
 * The plugin file that controls widgets
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Classes;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Widgets extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( 'widgets_init' => 'add_widgets' ),
		);
	}

	public function add_widgets() {
		if( Utilities::get_settings( 'disable_content_block', '' ) != 1 ) {
			register_widget( '\\Wpcl\\Be\\Classes\\Widgets\\ContentBlock' );
		}
	}

} // end class