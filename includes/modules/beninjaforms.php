<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BENinjaForms extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Ninja Forms', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Place a Ninja Forms form', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'enabled' => class_exists('Ninja_Forms'),
			'dir'           => Plugin::path('/'),
			'url'           => Plugin::url('/'),
		));
	}

	/**
	 * Get the action hooks this class subscribes to.
	 * @return array
	 */
	public function get_actions() {
		return array(
			array( "beaver_extender_frontend_{$this->hook_name}" => array( 'do_frontend' , 10, 3 ) ),
			array( "beaver_extender_css_{$this->hook_name}" => array( 'do_css' , 10, 3 ) ),
			array( "beaver_extender_js_{$this->hook_name}" => array( 'do_js' , 10, 3 ) ),
		);
	}

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(

		);
	}


	/**
	 * Get all of the ninja forms created on the site
	 *
	 * @return [array] Array of form ID's and Titles, for use in select field
	 */
	public function get_forms() {

		$options = array();

		if( class_exists('Ninja_Forms') ) {

			$forms_modified = array();

			if ( get_option( 'ninja_forms_load_deprecated', false ) ) {

				$forms = Ninja_Forms()->forms()->get_all();

				foreach ( $forms as $form_id ) {

					$options[ $form_id ] = Ninja_Forms()->form( $form_id )->get_setting( 'form_title' );;

				}
			}

			else {

				$forms = Ninja_Forms()->form()->get_forms();

				foreach ( $forms as $index => $form ) {

					$options[ $form->get_id() ] = $form->get_setting( 'title' );

				}
			}
		}

		return array_merge( array( '0' => 'Choose Form' ), $options );
	}

	/**
	 * Organize the front end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_frontend( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) || !class_exists('Ninja_Forms') ) {
			return;
		}
		// Display empty form notice
		if( empty( $settings->form_id ) ) {
			Utilities::markup( array(
				'open' => '<p %s>',
				'close' => '</p>',
				'context' => 'be-notice',
				'content' => __( 'Choose form to display', 'wpcl_beaver_extender' ),
			) );
		}
		// Display form
		else {
			echo do_shortcode( "[ninja_form id={$settings->form_id}]" );
		}
	}

	/**
	 * Organize the css output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_css( $module, $settings, $id ) {
		/**
		 * Bail if not this instance
		 */
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
	}

	/**
	 * Organize the js output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_js( $module, $settings, $id ) {
		/**
		 * Bail if not this instance
		 */
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
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
						),
					),
				),
			),
			'style'         => array(
				'title'         => __( 'Style', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'submit'        => array(
						'title'         => __( 'Submit Button', 'wpcl_beaver_extender' ),
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
									'transparent'   => array(
										'fields'        => array( 'button_bg_opacity', 'button_bg_hover_opacity' ),
									),
									'gradient' => array(
										'fields' => array( 'button_bg_gradient_color' ),
									),
								),
							),
							'button_bg_color'      => array(
								'type'          => 'color',
								'label'         => __( 'Background Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_bg_gradient_color'      => array(
								'type'          => 'color',
								'label'         => __( 'Gradient End Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_bg_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Background Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_text_color'    => array(
								'type'          => 'color',
								'label'         => __( 'Text Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_text_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Text Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_border_color'    => array(
								'type'          => 'color',
								'label'         => __( 'Border Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_border_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Border Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_border_size'   => array(
								'type'          => 'text',
								'label'         => __( 'Border Size', 'wpcl_beaver_extender' ),
								'default'       => '',
								'description'   => 'px',
								'maxlength'     => '3',
								'size'          => '5',
								'placeholder'   => '0',
							),
							'button_bg_opacity'    => array(
								'type'          => 'text',
								'label'         => __( 'Background Opacity', 'wpcl_beaver_extender' ),
								'default'       => '100',
								'description'   => '%',
								'maxlength'     => '3',
								'size'          => '5',
								'placeholder'   => '0',
								'sanitize'		=> 'absint',
							),
							'button_bg_hover_opacity'    => array(
								'type'          => 'text',
								'label'         => __( 'Background Hover Opacity', 'wpcl_beaver_extender' ),
								'default'       => '100',
								'description'   => '%',
								'maxlength'     => '3',
								'size'          => '5',
								'placeholder'   => '0',
								'sanitize'		=> 'absint',
							),
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
									'auto'          => array(
										'fields'        => array( 'align' ),
									),
									'full'          => array(),
									'custom'        => array(
										'fields'        => array( 'align', 'button_custom_width' ),
									),
								),
							),
							'button_custom_width'  => array(
								'type'          => 'text',
								'label'         => __( 'Custom Width', 'wpcl_beaver_extender' ),
								'default'       => '200',
								'maxlength'     => '3',
								'size'          => '4',
								'description'   => 'px',
							),
							'button_align'         => array(
								'type'          => 'select',
								'label'         => __( 'Alignment', 'wpcl_beaver_extender' ),
								'default'       => 'left',
								'options'       => array(
									'center'        => __( 'Center', 'wpcl_beaver_extender' ),
									'left'          => __( 'Left', 'wpcl_beaver_extender' ),
									'right'         => __( 'Right', 'wpcl_beaver_extender' ),
								),
							),
							'button_font_size'     => array(
								'type'          => 'text',
								'label'         => __( 'Font Size', 'wpcl_beaver_extender' ),
								'default'       => '',
								'maxlength'     => '3',
								'size'          => '4',
								'description'   => 'px',
							),
							'button_padding'       => array(
								'type'        => 'dimension',
								'label'         => __( 'Padding', 'wpcl_beaver_extender' ),
								'description' => 'px',
							),
							'button_border_radius' => array(
								'type'          => 'text',
								'label'         => __( 'Round Corners', 'wpcl_beaver_extender' ),
								'default'       => '',
								'maxlength'     => '3',
								'size'          => '4',
								'description'   => 'px',
							),
						),
					),
				),
			),
		));
	}
}