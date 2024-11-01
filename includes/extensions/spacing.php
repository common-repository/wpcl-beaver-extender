<?php

/**
 * The plugin file that defines the front end functionality
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Extensions;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Spacing extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'fl_builder_register_settings_form' => array( 'extend_settings_form' , 10, 2 ) ),
			array( 'fl_builder_render_css' => array( 'extend_css', 10, 4 ) ),
		);
	}

	/**
	 * Extend Settings Form
	 * @param  [array] $form : The settings array for the row settings form
	 * @param  [string] $id  : The id of the form
	 * @return [array]       : The (maybe) modified form
	 */
	public function extend_settings_form( $form, $id ) {

		if( $id === 'col' ) {
			$form['tabs']['advanced'][ 'sections' ][ 'margins' ][ 'fields' ]['max_width'] = array(
				'type'         => 'unit',
				'label'         => __( 'Max Width', 'wpcl_beaver_extender' ),
				'units'	       => array( 'px', 'vw', '%' ),
				'default_unit' => 'px', // Optional
				'responsive'  => true,
				'preview'         => array(
					'type'            => 'refresh',
				),
			);

			$form['tabs']['advanced'][ 'sections' ][ 'margins' ][ 'fields' ]['max_width_alignment'] = array(
				'type'          => 'select',
				'label'         => __( 'Node Alignment', 'wpcl_beaver_extender' ),
				'default'       => 'center',
				'responsive'  => true,
				'options' 		=> array(
					'left' 			=> __( 'Left', 'wpcl_beaver_extender' ),
					'center' 			=> __( 'Center', 'wpcl_beaver_extender' ),
					'right' 			=> __( 'Right', 'wpcl_beaver_extender' ),
				),
				'preview'         => array(
					'type'            => 'refresh',
				),
			);
		}

		else if( $id === 'module_advanced' ) {
			$form[ 'sections' ][ 'margins' ][ 'fields' ]['be_padding'] = array(
				'type'         => 'dimension',
				'label'         => __( 'Padding', 'wpcl_beaver_extender' ),
				'units'	       => array( 'px', 'em', 'rem', '%' ),
				'default_unit' => 'px', // Optional
				'responsive'  => true,
				'slider'      => true,
				'preview' => array(
					'type'     => 'css',
					'selector' => '.fl-module-content',
					'property' => 'padding',
				),
			);

			$form[ 'sections' ][ 'margins' ][ 'fields' ]['max_width'] = array(
				'type'         => 'unit',
				'label'         => __( 'Max Width', 'wpcl_beaver_extender' ),
				'units'	       => array( 'px', 'vw', '%' ),
				'default_unit' => 'px', // Optional
				'responsive'  => true,
				'preview'         => array(
					'type'            => 'refresh',
				),
			);

			$form[ 'sections' ][ 'margins' ][ 'fields' ]['max_width_alignment'] = array(
				'type'          => 'select',
				'label'         => __( 'Node Alignment', 'wpcl_beaver_extender' ),
				'default'       => 'center',
				'responsive'  => true,
				'options' 		=> array(
					'left' 			=> __( 'Left', 'wpcl_beaver_extender' ),
					'center' 			=> __( 'Center', 'wpcl_beaver_extender' ),
					'right' 			=> __( 'Right', 'wpcl_beaver_extender' ),
				),
				'preview'         => array(
					'type'            => 'refresh',
				),
			);
		}

		return $form;
	}

	public function extend_css( $css, $nodes, $global_settings, $include_global ) {

		foreach( $nodes as $node ) {

			foreach( $node as $module ) {

				$padding = array(
					'base' => '',
					'medium' => '',
					'responsive' => '',
				);

				foreach( array( 'top', 'right', 'bottom', 'left' ) as $dim ) {

					if( !empty( $module->settings->{"be_padding_{$dim}"} ) ) {
						$padding['base'] .= sprintf( 'padding-%s: %s%s;', $dim, $module->settings->{"be_padding_{$dim}"}, $module->settings->{"be_padding_unit"} );
					}
					if( !empty( $module->settings->{"be_padding_{$dim}_medium"} ) ) {
						$padding['medium'] .= sprintf( 'padding-%s: %s%s;', $dim, $module->settings->{"be_padding_{$dim}_medium"}, $module->settings->{"be_padding_medium_unit"} );
					}
					if( !empty( $module->settings->{"be_padding_{$dim}_responsive"} ) ) {
						$padding['responsive'] .= sprintf( 'padding-%s: %s%s;', $dim, $module->settings->{"be_padding_{$dim}_responsive"}, $module->settings->{"be_padding_responsive_unit"} );
					}
				}

				if( !empty( $padding['base'] ) ) {
					$css .= ".fl-node-$module->node .fl-module-content { {$padding['base']} }";
				}
				if( !empty( $padding['medium'] ) ) {
					// echo "@media (max-width: {$global_settings->medium_breakpoint}px){.fl-node-$module->node .fl-module-content { {$padding['medium']} }";
					$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){.fl-node-$module->node .fl-module-content { {$padding['medium']} }";
				}
				if( !empty( $padding['responsive'] ) ) {
					$css .= "@media (max-width: {$global_settings->responsive_breakpoint}px){.fl-node-$module->node .fl-module-content { {$padding['responsive']} }";
				}

				// Do Max Width Stuff
				if( isset( $module->settings->max_width ) || isset( $module->settings->max_width_medium ) || isset( $module->settings->max_width_responsive ) ) {
					// Bail if they are all empty
					if( !empty( $module->settings->max_width ) || !empty( $module->settings->max_width_medium ) || !empty( $module->settings->max_width_responsive ) ) {

						$alignment_rules = array(
							'center' => 'margin-left: auto; margin-right: auto;',
							'right' => 'margin-left: auto; margin-right: 0;',
							'left' => 'margin-left: 0; margin-right: auto;',
						);

						$style = '';

						/**
						 * Do base styles
						 */
						if( !empty( $module->settings->max_width ) ) {

							$selector = '';

							switch ( $module->type ) {
								case 'column':
									$selector = sprintf( '.fl-node-%s > .fl-col-content', $module->node );
									break;
								default:
									$selector = sprintf( '.fl-node-%s', $module->node );
									break;
							}

							$style .= sprintf( '%s { max-width: %s%s;',
								$selector,
								$module->settings->max_width,
								$module->settings->max_width_unit
							);

							if( isset( $module->settings->max_width_alignment ) && isset( $alignment_rules[$module->settings->max_width_alignment] ) ) {
								$style .= $alignment_rules[$module->settings->max_width_alignment];
							}

							$style .= '}';
						}
						/**
						 * Do medium Styles
						 */
						if( isset( $module->settings->max_width_medium ) && !empty( $module->settings->max_width_medium ) ) {

							$style .= sprintf( '@media (max-width: %spx){', $global_settings->medium_breakpoint );

							$style .= sprintf( '%s { max-width: %s%s;',
								$selector,
								$module->settings->max_width_medium,
								$module->settings->max_width_medium_unit
							);

							if( isset( $module->settings->max_width_alignment_medium ) && isset( $alignment_rules[$module->settings->max_width_alignment_medium] ) ) {
								$style .= $alignment_rules[$module->settings->max_width_alignment_medium];
							}

							$style .= '} }';
						}
						/**
						 * Do small Styles
						 */
						if( isset( $module->settings->max_width_responsive ) && !empty( $module->settings->max_width_responsive ) ) {

							$style .= sprintf( '@media (max-width: %spx){', $global_settings->responsive_breakpoint );

							$style .= sprintf( '%s { max-width: %s%s;',
								$selector,
								$module->settings->max_width_responsive,
								$module->settings->max_width_responsive_unit
							);

							if( isset( $module->settings->max_width_alignment_responsive ) && isset( $alignment_rules[$module->settings->max_width_alignment_responsive] ) ) {
								$style .= $alignment_rules[$module->settings->max_width_alignment_responsive];
							}

							$style .= '} }';
						}
						$css .= $style;
					}
				}
			}
		}
		return $css;
	}
} // end class