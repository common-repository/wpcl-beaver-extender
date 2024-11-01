<?php

/**
 * The plugin file that defines the front end functionality
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Extensions;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Animations extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'fl_builder_register_settings_form' => array( 'extend_settings_form' , 10, 2 ) ),
			array( 'fl_builder_column_attributes' => array( 'extend_attributes' , 10, 2 ) ),
			array( 'fl_builder_row_attributes' => array( 'extend_attributes' , 10, 2 ) ),
			array( 'fl_builder_module_attributes' => array( 'extend_attributes' , 10, 2 ) ),
		);
	}

	/**
	 * Extend Settings Form
	 * @param  [array] $form : The settings array for the row settings form
	 * @param  [string] $id  : The id of the form
	 * @return [array]       : The (maybe) modified form
	 */
	public function extend_settings_form( $form, $id ) {

		// if( version_compare( '2.2', FL_BUILDER_VERSION, '>=' ) ) {

		if( $id === 'col' || $id === 'row' ) {
			// animation
			$animation_section = array(
				'title'         => __( 'Animation', 'wpcl_beaver_extender' ),
				'fields'        => array(
					'animation'     => array(
						'type'          => 'animation',
						'label'         => __( 'Animation', 'wpcl_beaver_extender' ),
						'preview'         => array(
							'type'            => 'refresh',
						),
					),
				),
			);
			// Add the animation field
			$form['tabs']['advanced']['sections']['animation'] = $animation_section;
		}

		return $form;
	}

	public function extend_attributes( $atts, $module ) {
		if( $module->type === 'row' || $module->type === 'column' ) {
			if( isset( $module->settings->animation ) && !empty( $module->settings->animation['style'] ) ) {
				$atts['class'][] = "fl-animation fl-{$module->settings->animation['style']}";
				$atts['data-animation-delay'] = array( $module->settings->animation['delay']);
				$atts['data-animation-duration'] = array( $module->settings->animation['duration'] );
			}
		}
		return $atts;
	}
} // end class