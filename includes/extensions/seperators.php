<?php

/**
 * The plugin file that defines the front end functionality
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be\Extensions;

use \Wpcl\Be\Classes\Utilities as Utilities;

class Seperators extends \Wpcl\Be\Plugin implements \Wpcl\Be\Interfaces\Filter_Hook_Subscriber, \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		if( Utilities::get_settings( 'disable_seperators', '' ) != 1 ) {
			return array(
				array( 'fl_builder_register_settings_form' => array( 'extend_settings' , 10, 2 ) ),
				array( 'fl_builder_render_css' => array( 'extend_css', 10, 4 ) ),
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
		if( Utilities::get_settings( 'disable_seperators', '' ) != 1 ) {
			return array(
				array( 'init' => 'register_forms' ),
				array( 'fl_builder_before_render_row_bg' => 'do_seperator' ),
				array( 'fl_builder_after_render_row_bg' => 'do_seperator' ),
				array( 'fl_builder_before_render_modules' => array( 'do_column_seperator', 10, 2 ) ),
				array( 'fl_builder_after_render_modules' => array( 'do_column_seperator', 10, 2 ) ),
			);
		} else {
			return array();
		}
	}

	public function do_seperator( $module ) {

		if( !isset( $module->settings->seperators ) || empty( $module->settings->seperators ) ) {
			return;
		}
		$count = 1;

		switch ( current_filter() ) {
			case 'fl_builder_before_render_modules':
				$position = 'top';
				break;
			case 'fl_builder_before_render_row_bg':
				$position = 'top';
				break;
			case 'fl_builder_after_render_row_bg':
				$position = 'bottom';
				break;
			case 'fl_builder_after_render_modules':
				$position = 'bottom';
				break;
			default:
				$position = '';
				break;
		}

		foreach( $module->settings->seperators as $seperator ) {

			if( empty( $seperator->seperator_style ) || $seperator->vertical_position !== $position ) {
				continue;
			}

			$seperator->node = $module->node;

			$seperator->context = "{$module->type}-seperator";

			$seperator->seperator_id = "seperator-{$module->node}-{$position}-{$count}";

			$this->render_seperator( $seperator );

			++$count;
		}
	}

	/**
	 * Wrapper for column seperators, since they get fed different args
	 */
	public function do_column_seperator( $nodes, $module ) {
		$this->do_seperator( $module );
	}

	public function render_seperator( $settings ) {

		// var_dump($settings);

		// $defaults = array(
		// 	'width'             => '100',
		// 	'width_unit'        => '%',
		// 	'xposition' => '0',
		// 	'yposition' => '0',
		// 	'zposition' => 'auto',
		// );

		// $settings = Utilities::merge_settings( $defaults, $settings );

		// $wrapper_style  = '';
		// $wrapper_style  = "width: {$settings->width}{$settings->width_unit};";
		// $wrapper_style .= "{$settings->vertical_position}: 0;";
		// $wrapper_style .= "z-index: {$settings->zposition};";
		// $wrapper_style .= "transform: translate3d( {$settings->xposition}{$settings->xposition_unit},{$settings->yposition}{$settings->yposition_unit}, 0 );";

		// $fill = seperator_color
		$fill = \FLBuilderColor::hex_or_rgb( $settings->bgcolor );

		// $svg_style = $settings->height_unit === '%' ? '' : "height:{$settings->height}{$settings->height_unit}";

		Utilities::markup( array(
			'open'     => '<div %s>',
			'context'  => "be-seperator",
			'params'   => array(
				'class' => $settings->context,
				// 'style' => $wrapper_style,
				'id'    => $settings->seperator_id
			),
		) );

		if( file_exists( self::path( "includes/images/seperators/{$settings->seperator_style}.php" ) ) ) {
			include self::path( "includes/images/seperators/{$settings->seperator_style}.php" );
		}
		else if( $settings->seperator_style === 'custom' ) {
			echo $settings->custom_seperator;
		}

		Utilities::markup( array(
			'close'     => '</div>',
			'context'  => "be-seperator",
		) );
	}

	public function do_column_top_seperator( $nodes, $col ) {
		if( is_object( $col ) && $col->type === 'column' ) {

			if( !isset( $col->settings->seperators ) || empty( $col->settings->seperators ) ) {
				return;
			}

			$count = 1;

			foreach( $col->settings->seperators as $seperator ) {

				if( empty( $seperator->seperator_style ) || $seperator->vertical_position !== 'top' ) {
					continue;
				}

				$seperator->node = $col->node;

				$seperator->context = $col->type;

				$seperator->seperator_id = "seperator-{$col->node}-top{$count}";

				$this->render_seperator( $seperator );

				++$count;

			}
		}
	}

	public function do_column_bottom_seperator( $nodes, $col ) {
		if( is_object( $col ) && $col->type === 'column' ) {

			if( !isset( $col->settings->seperators ) || empty( $col->settings->seperators ) ) {
				return;
			}

			$count = 1;

			foreach( $col->settings->seperators as $seperator ) {

				if( empty( $seperator->seperator_style ) || $seperator->vertical_position !== 'bottom' ) {
					continue;
				}

				$seperator->node = $col->node;

				$seperator->context = $col->type;

				$seperator->seperator_id = "seperator-{$col->node}-bottom{$count}";

				$this->render_seperator( $seperator );

				++$count;

			}
		}
	}

	public function do_row_before_seperator( $row ) {
		if( !isset( $row->settings->seperators ) || empty( $row->settings->seperators ) ) {
			return;
		}

		$count = 1;

		foreach( $row->settings->seperators as $seperator ) {

			if( empty( $seperator->seperator_style ) || $seperator->vertical_position !== 'top' ) {
				continue;
			}

			$seperator->node = $row->node;

			$seperator->context = $row->type;

			$seperator->seperator_id = "seperator-{$row->node}-top{$count}";

			$this->render_seperator( $seperator );

			++$count;

		}
	}

	public function do_row_after_seperator( $row ) {
		if( !isset( $row->settings->seperators ) || empty( $row->settings->seperators ) ) {
			return;
		}

		$count = 1;

		foreach( $row->settings->seperators as $seperator ) {

			if( empty( $seperator->seperator_style ) || $seperator->vertical_position !== 'bottom' ) {
				continue;
			}

			$seperator->node = $row->node;

			$seperator->context = $row->type;

			$seperator->seperator_id = "seperator-{$row->node}-bottom{$count}";

			$this->render_seperator( $seperator );

			++$count;
		}
	}

	public function extend_css( $css, $nodes, $global_settings, $include_global ) {

		$prefix = '';

		foreach( $nodes as $node ) {

			foreach( $node as $module ) {

				if( !isset( $module->settings->seperators ) ) {
					continue;
				}

				$counts = array(
					'top' => 1,
					'bottom' => 1,
				);

				foreach( $module->settings->seperators as $seperator ) {

					// $seperator->vertical_position

					if( empty( $seperator->seperator_style ) ) {
						continue;
					}

					$defaults = array(
						'width'             => '100',
						'width_unit'        => '%',
						'xposition' => '0',
						'yposition' => '0',
						'zposition' => 'auto',
					);

					$seperator = Utilities::merge_settings( $defaults, $seperator );

					$wrapper_style  = '';
					$wrapper_style  = "width: {$seperator->width}{$seperator->width_unit};";
					$wrapper_style .= "{$seperator->vertical_position}: 0;";
					$wrapper_style .= "z-index: {$seperator->zposition};";
					$wrapper_style .= "transform: translate3d( {$seperator->xposition}{$seperator->xposition_unit},{$seperator->yposition}{$seperator->yposition_unit}, 0 );";

					$seperator = Utilities::merge_settings( $defaults, $seperator);

					$seperator_id = "seperator-{$module->node}-{$seperator->vertical_position}-{$counts[$seperator->vertical_position]}";

					/**
					 * Do the base styles
					 */
					$css .= ".fl-node-{$module->node} #{$seperator_id} { {$wrapper_style} }";

					if( $seperator->height_unit !== '%' ) {
						$css .= ".fl-node-{$module->node} #{$seperator_id} svg { height: {$seperator->height}{$seperator->height_unit} }";
					}

					/**
					 * Do medium Styles
					 */
					if( isset( $seperator->height_medium ) && !empty( $seperator->height_medium ) ) {

						$css .= sprintf( '@media (max-width: %spx){', $global_settings->medium_breakpoint );

						$css .= ".fl-node-{$module->node} #{$seperator_id} svg { height: {$seperator->height_medium}{$seperator->height_medium_unit} } }";

					}

					/**
					 * Do medium Styles
					 */
					if( isset( $seperator->height_responsive ) && !empty( $seperator->height_responsive ) ) {

						$css .= sprintf( '@media (max-width: %spx){', $global_settings->responsive_breakpoint );

						$css .= ".fl-node-{$module->node} #{$seperator_id} svg { height: {$seperator->height_responsive}{$seperator->height_responsive_unit} } }";

					}


					++$counts[$seperator->vertical_position];
				}
			}
		}
		return $css;
	}

	public function register_forms() {

		if( !class_exists( 'FLBuilder' ) ) {
			return false;
		}

		\FLBuilder::register_settings_form( 'be_seperator_form' , array(
			'title' => __( 'Add Seperator', 'wpcl_beaver_extender' ),
			'tabs'  => array(
				'general' => array( // Tab
					'title'         => __( 'General', 'wpcl_beaver_extender' ), // Tab title
					'sections'      => array( // Tab Sections
						'general'       => array( // Section
							'title'         => 'General', // Section Title
							'fields'        => array( // Section Fields
								'seperator_style'         => array(
									'type'          => 'select',
									'label'         => __( 'Seperator Style', 'wpcl_beaver_extender' ),
									'default'       => '',
									'options'       => array(
										'' => 'Choose Style',
										'angle-left-up'    => __( 'Angle: Up/Left', 'wpcl_beaver_extender' ),
										'angle-left-down'  => __( 'Angle: Down/Left', 'wpcl_beaver_extender' ),
										'angle-right-up'   => __( 'Angle: Up/Right', 'wpcl_beaver_extender' ),
										'angle-right-down' => __( 'Angle: Down/Right', 'wpcl_beaver_extender' ),
										'center-up'        => __( 'Center: Up', 'wpcl_beaver_extender' ),
										'center-down'      => __( 'Center: Down', 'wpcl_beaver_extender' ),
										'arrow-up'       => __( 'Arrow: Up', 'wpcl_beaver_extender' ),
										'arrow-down'       => __( 'Arrow: Down', 'wpcl_beaver_extender' ),
										'custom'           => __( 'Custom', 'wpcl_beaver_extender' ),
									),
									'preview'       => array(
										'type'          => 'refresh',
									),
									'toggle'        => array(
										'custom'   => array(
											'fields'        => array( 'custom_seperator' ),
										),
										'center-up'   => array(
											'fields'        => array( 'centerpoint' ),
										),
										'center-down'   => array(
											'fields'        => array( 'centerpoint' ),
										),
										'arrow-up'   => array(
											'fields'        => array( 'centerpoint' ),
										),
										'arrow-down'   => array(
											'fields'        => array( 'centerpoint' ),
										),
									),
								),
								'custom_seperator'        => array(
									'type'          => 'code',
									'label'         => __( 'Custom Seperator', 'wpcl_beaver_extender' ),
									'description'   => __( 'Input markup to display here', 'wpcl_beaver_extender' ),
									'default'       => '',
									'editor'           => 'html',
									'rows'          => '10',
									'preview'       => array(
										'type'          => 'refresh',
									),
								),
							),
						),
						'style' => array( // Section
						    'title'         => __('Style', 'wpcl_beaver_extender'), // Section Title
						    'fields'        => array( // Section Fields
						    	'centerpoint'        => array(
						    		'type'          => 'unit',
						    		'label'         => __( 'Centerpoint', 'wpcl_beaver_extender' ),
						    		'default'       => '',
						    		'slider' => array(
						    			'min'  	=> -100,
						    			'max'  	=> 100,
						    			'step' 	=> 1,
						    		),
						    		'sanitize'		=> 'absint',
						    		'preview'       => array(
						    			'type'          => 'refresh',
						    		),
						    	),
						    	'width'        => array(
						    		'type'          => 'unit',
						    		'label'         => __( 'Width', 'wpcl_beaver_extender' ),
						    		'default'       => '100',
						    		'units'	       => array( 'px', 'vw', '%', 'em', 'rem' ),
						    		'slider'       => true,
						    		'default_unit' => '%', // Optional
						    		'sanitize'		=> 'absint',
						    		'preview'       => array(
						    			'type'          => 'refresh',
						    		),
						    	),
						    	'height'  => array(
						    		'type'          => 'unit',
						    		'label'         => __( 'Height', 'wpcl_beaver_extender' ),
						    		'default'       => '100',
						    		'units'	        => array( 'px', 'vw', '%', 'em', 'rem' ),
						    		'default_unit'  => 'px', // Optional
						    		'slider'        => true,
						    		'responsive'    => true,
						    		'sanitize'		=> 'absint',
						    		'preview'       => array(
						    			'type'          => 'refresh',
						    		),
						    	),
						    	'bgcolor'         => array(
						    		'type'          => 'color',
						    		'label'         => __( 'Background Color', 'wpcl_beaver_extender' ),
						    		'default'       => '#000000',
						    		'show_alpha'    => true,
						    		'preview' => array(
						    			'type'       => 'css',
						    			'selector'   => '.be-seperator svg',
						    			'property'   => 'fill',
						    		),
						    	),
						    ),
						),
						'position'       => array( // Section
							'title'         => 'Position', // Section Title
							'fields'        => array( // Section Fields
								'xposition' => array(
									'type'        => 'unit',
									'label'       => 'X-Position',
									'units'	       => array( 'px', 'em', 'rem', '%' ),
									'default_unit' => 'px',
									'default' => '',
									'slider' => array(
										'min'  	=> -100,
										'max'  	=> 100,
										'step' 	=> 1,
									),
								),
								'yposition' => array(
									'type'        => 'unit',
									'label'       => 'Y-Position',
									'units'	       => array( 'px', 'em', 'rem', '%' ),
									'default_unit' => 'px',
									'default' => '',
									'slider' => array(
										'min'  	=> -100,
										'max'  	=> 100,
										'step' 	=> 1,
									),
								),
								'zposition'  => array(
									'type'          => 'unit',
									'label'         => __( 'Z-Position', 'wpcl_beaver_extender' ),
									'default'       => '',
									'sanitize'		=> 'absint',
									'slider' => array(
										'min'  	=> -100,
										'max'  	=> 100,
										'step' 	=> 1,
									),
								),
								'vertical_position'         => array(
									'type'          => 'select',
									'label'         => __( 'Vertical Position', 'wpcl_beaver_extender' ),
									'default'       => 'top',
									'options'       => array(
										'top'         => __( 'Top', 'wpcl_beaver_extender' ),
										'bottom'      => __( 'Bottom', 'wpcl_beaver_extender' ),
									),
									'preview'       => array(
										'type'          => 'refresh',
									),
								),
							),
						),
					),
				),
			),
		));
	}

	/**
	 * Extend Row Settings
	 * @param  [array] $form : The settings array for the row settings form
	 * @param  [string] $id  : The id of the form
	 * @return [array]       : The (maybe) modified form
	 */
	public function extend_settings( $form, $id ) {

		if( $id === 'row' || $id === 'col' ) {
			$form['tabs']['seperators'] = array(
				'title' => __( 'Seperators', 'wpcl_beaver_extender' ),
				'sections' => array(
					'general' => array(
						'title'         => 'Seperators', // Tab title
						'fields'        => array(
							'seperators'         => array(
								'type'          => 'form',
								'label'         => __( 'Seperators', 'wpcl_beaver_extender' ),
								'form'          => 'be_seperator_form', // ID from registered form below
								'preview_text'  => 'seperator_style', // Name of a field to use for the preview text
								'multiple'      => true,
							),
						),
					),
				),
			);
		}
		return $form;
	}
} // end class