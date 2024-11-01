<?php

/**
 * The plugin file that defines the front end functionality
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Extensions;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Presets extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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

		if( !in_array( $id, array( 'row', 'col', 'module_advanced' ) ) ) {
			return $form;
		}

		$presets = array(
			'row' => apply_filters( 'be_row_preset_classes', array() ),
			'col' => apply_filters( 'be_column_preset_classes', array() ),
			'module_advanced' => apply_filters( 'be_module_preset_classes', array() ),
		);

		if( !empty( $presets[$id] ) ) {

			$preset = array_merge( array( '' => 'Choose Preset' ), $presets[$id] );

			if( $id === 'module_advanced' ) {
				$form['sections']['css_selectors']['fields']['preset_css_classes'] = array(
					'type'          => 'select',
					'label'         => __( 'Presets', 'wpcl_beaver_extender' ),
					'default'       => '',
					'options'       => $preset,
					'multiple'      => true,
					'preview'       => array(
						'type'            => 'refresh',
					),
				);
			} else {
				$form['tabs']['advanced'][ 'sections' ]['css_selectors']['fields']['preset_css_classes'] = array(
					'type'          => 'select',
					'label'         => __( 'Presets', 'wpcl_beaver_extender' ),
					'default'       => '',
					'options'       => $preset,
					'multiple'      => true,
					'preview'       => array(
						'type'            => 'refresh',
					),
				);
			}
		}
		return $form;
	}

	public function extend_attributes( $atts, $module ) {
		// Get Legacy presets
		$legacy_presets = isset( $module->settings->presets ) && !empty( $module->settings->presets ) ? $module->settings->presets : array();
		// Get new presets
		$presets = isset( $module->settings->preset_css_classes ) && !empty( $module->settings->preset_css_classes ) ? $module->settings->preset_css_classes : array();
		// Merge
		$presets = array_merge( $presets, $legacy_presets );
		// Add the classes
		if( !empty( $presets ) ) {
			$atts['class'][] = join( ' ', $presets );
		}
		return $atts;
	}
} // end class