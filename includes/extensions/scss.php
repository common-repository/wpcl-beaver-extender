<?php

/**
 * The plugin file that defines the front end functionality
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Extensions;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Scss extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Filter_Hook_Subscriber, \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		if( Utilities::get_settings( 'disable_scss', '' ) != 1 ) {
			return array(
				array( 'fl_builder_register_settings_form' => array( 'extend_settings_form' , 10, 2 ) ),
				array( 'fl_builder_render_css' => array( 'render_css', 10, 4 ) ),
				array( 'fl_builder_custom_fields' => 'register_field' ),
			);
		} else {
			return array();
		}
	}

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		if( Utilities::get_settings( 'disable_scss', '' ) != 1 ) {
			return array(
				array( 'wp_enqueue_scripts' => 'enqueue_scripts' ),
			);
		} else {
			return array();
		}
	}

	public function enqueue_scripts() {
		if( \FLBuilderModel::is_builder_active() ) {
			wp_enqueue_style( 'ui-field-scss', self::url( 'css/ui-field-scss.css' ), array(), self::$version, 'all' );
			wp_enqueue_script( 'be_ace', self::url( 'js/ace/src-min/ace.js' ), array(), self::$version, true );
			wp_enqueue_script( 'be_ui_field_scss', self::url( 'js/ui-field-scss.min.js' ), array( 'jquery' ), self::$version, true );
			wp_localize_script( 'be_ui_field_scss', 'be_ui_field_scss', array( 'baseurl' => self::url() ) );
		}
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

		$prefix = array(
			'row' => '.fl-node-$id.fl-row',
			'col' => '.fl-node-$id.fl-col',
			'module_advanced' => '.fl-node-$id .fl-module-$slug',
		);

		$field = array(
			'title'          => __( 'Custom SCSS', 'wpcl_beaver_extender' ), // Section Title
			'fields'         => array(
				'custom_scss' => array(
				    'type'     => 'bescss',
				    'label'    => '',
				    'prefix'   => $prefix[$id],
				    'default'  => '',
				    'preview'  => array(
				    	'type' => 'refresh',
				    ),
				),
			),
		);

		if( $id === 'module_advanced' ) {
			$form[ 'sections' ][ 'custom_scss' ] = $field;
		}

		else {
			$form['tabs']['advanced'][ 'sections' ][ 'custom_scss' ] = $field;
		}

		return $form;
	}


	public function render_css( $css, $nodes, $global_settings, $include_global ) {

		$prefix = '';

		foreach( $nodes as $node ) {

			foreach( $node as $module ) {

				if( !in_array( $module->type, array( 'row', 'column', 'module' ) ) ) {
					continue;
				}

				if( $module->type === 'row' ) {
					$prefix = ".fl-node-{$module->node}.fl-row";
				}
				else if( $module->type === 'column' ) {
					$prefix = ".fl-node-{$module->node}.fl-col";
				}
				else if( $module->type === 'module' ) {
					$prefix = ".fl-node-{$module->node}.fl-module-{$module->slug}";
				}

				// Some backwards compatibility
				if( isset( $module->settings->custom_css ) && !empty( $module->settings->custom_css ) ) {
					$module->settings->custom_scss = $module->settings->custom_css;
				}

				// Do scss
				if( isset( $module->settings->custom_scss ) && !empty( $module->settings->custom_scss ) ) {

					$scss  = '$medium-breakpoint : ' . $global_settings->medium_breakpoint . 'px;';
					$scss .= '$responsive-breakpoint : ' . $global_settings->responsive_breakpoint . 'px;';
					$scss .= "$prefix { {$module->settings->custom_scss} }";

					$compiled = self::scss( $scss );
					$css .= $compiled;
				}
			}
		}
		return $css;
	}

	public static function scss( $scss ) {

		require_once self::path( 'vendors/scssphp/scss.inc.php' );

		$css = '';

		try {
			$compiler = new \Leafo\ScssPhp\Compiler();
			$css = $compiler->compile( $scss );
		} catch (\Exception $e) {
			// Nothing to do right now
		}
		return $css;
	}

	public function register_field( $fields ) {
		$fields['bescss'] = self::path( 'includes/fields/ui-field-scss.php' );
		return $fields;
	}
} // end class