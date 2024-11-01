<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEGravityForms extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

	/**
	 * API Manager / Loader to interact with the other parts of the plugin
	 * @since 1.0.0
	 * @var (object) $api : The instance of the api manager class
	 */
	protected $api;

	/**
	 * Hook Name
	 * @since 1.0.0
	 * @var [string] : hook name, same as the slug created later by FLBuilderModule
	 */
	protected $hook_name;

	/**
	 * @method __construct
	 */
	public function __construct() {
		/**
		 * Set the hook name. Same as the slug, but created here so we can access it
		 */
		$this->hook_name = basename( __FILE__, '.php' );
		/**
		 * Get the API instance to interact with the other parts of our plugin
		 */
		$this->api = \Wpcl\Be\Loader::get_instance( $this );

		/**
		 * Construct our parent class (FLBuilderModule);
		 */
		parent::__construct( array(
			'name'          	=> __( 'Gravity Form', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Place a Gravity Forms form', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'dir'           => Plugin::path('/'),
			'url'           => Plugin::url('/'),
			'enabled' => class_exists( 'RGFormsModel' ), // disable if gravity forms are not enabled
		));
	}

	/**
	 * Get the actions hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( "beaver_extender_frontend_{$this->hook_name}" => array( 'do_frontend' , 10, 3 ) ),
			array( "beaver_extender_css_{$this->hook_name}" => array( 'do_css' , 10, 3 ) ),
		);
	}

	/**
	 * Get svg icon string
	 *
	 * @since 2.0
	 * @return String
	 */
	public function get_icon( $icon = '' ) {
		return file_get_contents( \Wpcl\Be\Plugin::path( 'includes/images/svg/gforms.svg' ) );
	}

	/**
	 * Organize the front end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 * @see  https://docs.gravityforms.com/embedding-a-form/
	 */
	public function do_frontend( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
		/**
		 * Make sure gravity forms is installed and enabled
		 */
		if( !class_exists( 'GFAPI' ) ) {
			return;
		}

		$field_values = array();

		if( !empty( $settings->field_values ) ) {
			foreach( $settings->field_values as $field ) {
				$field_values[$field->field_name] = $field->field_value;
			}
		}

		/**
		 * If our form exists and is active
		 * gravity_form( $id_or_title, $display_title = true, $display_description = true, $display_inactive = false, $field_values = null, $ajax = false, $tabindex, $echo = true );
		 */
		if( \GFAPI::get_form( $settings->form_id ) ) {

			$atts  = '';

			$atts .= !empty( $settings->form_id ) ? " id='{$settings->form_id}'" : '';

			$atts .= !empty( $settings->display_title ) ? " title='{$settings->display_title}'" : '';

			$atts .= !empty( $settings->display_description ) ? " description='{$settings->display_description}'" : '';

			$atts .= !empty( $settings->enable_ajax ) ? " ajax='{$settings->enable_ajax}'" : '';

			$atts .= !empty( $settings->tab_index ) ? " tabindex='{$settings->tab_index}'" : '';

			if( !empty( $settings->field_values ) ) {

				$field_vals = '';

				foreach( $settings->field_values as  $index => $field ) {

					$field_vals .= $index !== 0 ? '&' : '';

					$field_vals .= $field->field_name . '=' . $field->field_value;

				}

				$atts .= " field_values='{$field_vals}'";
			}

			echo do_shortcode( "[gravityform {$atts}]" );

		}
		/**
		 * Else default message
		 */
		else {
			echo 'Choose a form to display';
		}
	}

	/**
	 * Organize the css end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_css( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
		\FLBuilderCSS::typography_field_rule( array(
			'settings'	   => $settings,
			'setting_name' => 'typeography',
			'selector' 	   => ".fl-node-{$id} .fl-module-content .gform_wrapper input[type='submit'].gform_button",
		) );
		\FLBuilderCSS::border_field_rule( array(
			'settings' 	=> $settings,
			'setting_name' 	=> 'button_border',
			'selector' 	=> ".fl-node-{$id} .fl-module-content .gform_wrapper input[type='submit'].gform_button",
		) );
		/**
		 * Transparent button styles
		 */
		if( $settings->button_style === 'transparent' ) {
			$style = array(
				'background-color' => 'transparent',
				'border' => '2px solid #1779ba',
			);
			echo Utilities::be_css( array(
				".fl-node-{$id} .fl-module-content input[type='submit'].gform_button" => $style,
			) );

			// Extra hover stuff
			if( !empty( $settings->button_bg_hover_color ) ) {
				echo Utilities::be_css( array(
					".fl-node-{$id} .fl-module-content input[type='submit'].gform_button:hover,
					 .fl-node-{$id} .fl-module-content input[type='submit'].gform_button:focus" => array(
						'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color )
					)
				) );
			}
		}
		/**
		 * Gradient styles
		 */
		if( $settings->button_style === 'gradient' ) {
			echo Utilities::be_css( array(
				".fl-node-{$id} .fl-module-content input[type='submit'].gform_button" => array(
					'background-image' => \FLBuilderColor::gradient( $settings->button_gradient ),
			) ) );
		}
		/**
		 * Flat Styles
		 */
		if( $settings->button_style === 'flat' ) {
			echo Utilities::be_css( array(
				".fl-node-{$id} .fl-module-content input[type='submit'].gform_button" => array(
					'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_color ),
			) ) );
			if( !empty( $settings->button_bg_hover_color ) ) {
				echo Utilities::be_css( array(
					".fl-node-{$id} .fl-module-content input[type='submit'].gform_button:hover,
					 .fl-node-{$id} .fl-module-content input[type='submit'].gform_button:focus" => array(
						'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ),
				) ) );
			}
		}
		/**
		 * Size and alignment
		 */
		$styles = array(
			'display' => 'block',
			'margin'  => $settings->button_align,
			'padding-top' => isset( $settings->button_padding_top ) && !empty( $settings->button_padding_top ) ? "{$settings->button_padding_top}{$settings->button_padding_unit}" : '',
			'padding-right' => isset( $settings->button_padding_right ) && !empty( $settings->button_padding_right ) ? "{$settings->button_padding_right}{$settings->button_padding_unit}" : '',
			'padding-bottom' => isset( $settings->button_padding_bottom ) && !empty( $settings->button_padding_bottom ) ? "{$settings->button_padding_bottom}{$settings->button_padding_unit}" : '',
			'padding-left' => isset( $settings->button_padding_left ) && !empty( $settings->button_padding_left ) ? "{$settings->button_padding_left}{$settings->button_padding_unit}" : '',
		);

		if( $settings->button_width === 'custom' && !empty( $settings->button_custom_width ) ) {
			$styles['width']     = '100%';
			$styles['max-width'] = "{$settings->button_custom_width}{$settings->button_custom_width_unit}";
		}

		else if( $settings->button_width === 'full' ) {
			$styles['width'] = '100%';
		}

		echo Utilities::be_css( array(
			".fl-node-{$id} .fl-module-content input[type='submit'].gform_button" => $styles,
		) );
	}

	/**
	 * Get all of the gravity forms created on the site
	 *
	 * @return [array] Array of form ID's and Titles, for use in select field
	 */
	public function get_forms() {

		$options = array( '' => 'Choose Form' );

		if( class_exists('RGFormsModel') ) {

			$forms = \RGFormsModel::get_forms();

			foreach( $forms as $form ) {
				$options[$form->id] = $form->title;
			}
		}

		return $options;
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		\FLBuilder::register_module( __CLASS__, array(
			'general'       => array( // Tab
				'title'         => __( 'General', 'wpcl_beaver_extender' ), // Tab title
				'sections'      => array( // Tab Sections
					'general'       => array( // Section
						'title'         => '', // Section Title
						'fields'        => array( // Section Fields
							'form_id'        => array(
								'type'          => 'select',
								'label'         => __( 'Form', 'wpcl_beaver_extender' ),
								'default'       => null,
								'options'       => $this->get_forms(),
							),
							'display_title'         => array(
								'type'          => 'select',
								'label'         => __( 'Form Title', 'wpcl_beaver_extender' ),
								'default'       => 'true',
								'options'       => array(
									'true'  => __( 'Display', 'wpcl_beaver_extender' ),
									'false' => __( 'Hide', 'wpcl_beaver_extender' ),
								),
							),
							'display_description'         => array(
								'type'          => 'select',
								'label'         => __( 'Form Description', 'wpcl_beaver_extender' ),
								'default'       => 'true',
								'options'       => array(
									'true'  => __( 'Display', 'wpcl_beaver_extender' ),
									'false' => __( 'Hide', 'wpcl_beaver_extender' ),
								),
							),
							'enable_ajax'         => array(
								'type'          => 'select',
								'label'         => __( 'Enable Ajax', 'wpcl_beaver_extender' ),
								'default'       => 'true',
								'options'       => array(
									'true'  => __( 'Enable', 'wpcl_beaver_extender' ),
									'false' => __( 'Disable', 'wpcl_beaver_extender' ),
								),
							),
							'tab_index'    => array(
								'type'          => 'text',
								'label'         => __( 'Tab Index', 'wpcl_beaver_extender' ),
								'default'       => '1',
								'maxlength'     => '5',
								'size'          => '5',
							),
							'field_values'         => array(
								'type'          => 'form',
								'label'         => __( 'Field Values', 'wpcl_beaver_extender' ),
								'form'          => 'begravityforms_field_values', // ID from registered form below
								'preview_text'  => 'field_name', // Name of a field to use for the preview text
								'multiple'      => true,
							),
						),
					),
				),
			),
			'style'         => array(
				'title'         => __( 'Button', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'background'        => array( // Section
						'title'         => __( 'Background', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'button_style'         => array(
								'type'          => 'select',
								'label'         => __( 'Background Style', 'wpcl_beaver_extender' ),
								'default'       => 'flat',
								'options'       => array(
									'flat'          => __( 'Flat', 'wpcl_beaver_extender' ),
									'gradient'      => __( 'Gradient', 'wpcl_beaver_extender' ),
									'transparent'   => __( 'Transparent', 'wpcl_beaver_extender' ),
								),
								'toggle'        => array(
									'flat'   => array(
										'fields'        => array( 'button_bg_color', 'button_bg_hover_color' ),
									),
									'transparent'   => array(
										'fields'        => array( 'button_bg_hover_color' ),
									),
									'gradient' => array(
										'fields' => array( 'button_gradient' ),
									),
								),
							),
							'button_gradient' => array(
								'type'    => 'gradient',
								'label'   => 'Gradient',
								'preview' => array(
									'type'     => 'css',
									'selector' => '.my-selector',
									'property' => 'background-image',
								),
							),

							'button_bg_color'      => array(
								'type'          => 'color',
								'label'         => __( 'Background Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_bg_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Background Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
						),
					),
					'structure'        => array( // Section
						'title'         => __( 'Structure', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'button_width'         => array(
								'type'          => 'select',
								'label'         => __( 'Width', 'wpcl_beaver_extender' ),
								'default'       => 'auto',
								'options'       => array(
									'auto'          => _x( 'Auto', 'Width.', 'wpcl_beaver_extender' ),
									'full'          => __( 'Full Width', 'wpcl_beaver_extender' ),
									'custom'        => __( 'Custom', 'wpcl_beaver_extender' ),
								),
								'toggle'        => array(
									'custom'        => array(
										'fields'        => array( 'button_custom_width' ),
									),
								),
							),
							'button_custom_width'  => array(
								'type'          => 'unit',
								'label'         => __( 'Custom Width', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', '%' ),
								'default_unit' => 'px',
								'default' => '200',
							),
							'button_align'         => array(
								'type'          => 'align',
								'label'         => __( 'Alignment', 'wpcl_beaver_extender' ),
								'default'       => 'left',
								'values'  => array(
									'left'   => '0 auto 0 0',
									'center' => '0 auto',
									'right'  => '0 0 0 auto',
								),
							),
							'button_padding'       => array(
								'type'        => 'dimension',
								'label'         => __( 'Padding', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', '%' ),
								'default_unit' => 'px',
							),
						),
					),
					'border'        => array( // Section
						'title'         => __( 'Border & Shadow', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'button_border' => array(
								'type'       => 'border',
								'label'      => 'Border Options',
								'responsive' => true,
								'show_alpha'    => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.gform_wrapper input[type=submit].gform_button',
								),
							),
						),
					),
					'text'        => array( // Section
						'title'         => __( 'Text', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'typeography' => array(
								'type'       => 'typography',
								'label'      => 'Button Fonts',
								'responsive' => true,
								'preview'    => array(
									'type'	    => 'css',
									'selector'  => '.gform_wrapper input[type=submit].gform_button',
								),
							),
							'button_text_color'    => array(
								'type'          => 'color',
								'label'         => __( 'Text Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_text_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Text Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
						),
					),
				),
			),
		));
		/**
		 * Register a settings form to use in the "form" field type above.
		 */
		\FLBuilder::register_settings_form( 'begravityforms_field_values' , array(
			'title' => __( 'Add Field Value', 'wpcl_beaver_extender' ),
			'tabs'  => array(
				'general'       => array( // Tab
					'title'         => __( 'General', 'wpcl_beaver_extender' ), // Tab title
					'sections'      => array( // Tab Sections
						'general'       => array( // Section
							'title'         => '', // Section Title
							'fields'        => array( // Section Fields
								'field_name'         => array(
									'type'          => 'text',
									'label'         => __( 'Field Name', 'wpcl_beaver_extender' ),
									'default'       => '',
									'description'   => __( 'The name of the field to dynamically populate', 'wpcl_beaver_extender' ),
									'help'          => __( 'The name of the field to dynamically populate', 'wpcl_beaver_extender' )
								),
								'field_value' => array(
									'type'          => 'text',
									'label'         => __( 'Field Value', 'wpcl_beaver_extender' ),
									'default'       => '',
									'description'   => __( 'The value of the field to dynamically populate', 'wpcl_beaver_extender' ),
									'help'          => __( 'The value of the field to dynamically populate', 'wpcl_beaver_extender' )
								),
							),
						),
					),
				),
			),
		));
	}
}
