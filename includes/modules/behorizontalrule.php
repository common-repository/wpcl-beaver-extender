<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEHorizontalRule extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

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
			'name'          	=> __( 'Horizontal Rule', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'A divider line to separate content.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'icon'              => 'minus.svg',
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
		);
	}

	/**
	 * Organize the front end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_frontend( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
		Utilities::markup( array(
			'open'     => '<hr %s/>',
			'context'  => 'be-hr',
			'instance' => $module,
		) );
	}

	/**
	 * Organize the front end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_css( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
		/**
		 * Base styles
		 */
		$styles = array(
			'border-top-width' => !empty( $settings->height ) ? "{$settings->height}px" : '1px',
			'border-top-color' => !empty( $settings->color ) ? \FLBuilderColor::hex_or_rgb( $settings->color ) : '',
			'border-top-style' => !empty( $settings->color ) ? $settings->style : '',
			'max-width'        => !empty( $settings->width ) ? "{$settings->width}{$settings->width_unit}" : '',
			'margin-left'      => $settings->align === 'left' ? '0' : 'auto',
			'margin-right'     => $settings->align === 'right' ? '0' : 'auto',
		);
		echo Utilities::be_css( array(
			".fl-node-{$id} .fl-module-content .be-hr"  => $styles,
		) );

		$global_settings  = \FLBuilderModel::get_global_settings();

		$medium_styles = array();
		$responsive_styles = array();

		/**
		 * Medium breakpoint styles
		 */
		if( !empty( $settings->align_medium ) ) {
			$medium_styles = array_merge( $medium_styles, array(
				'margin-left'  => $settings->align_medium === 'left' ? '0' : 'auto',
				'margin-right' => $settings->align_medium === 'right' ? '0' : 'auto',
			) );
		}

		if( !empty( $settings->height_medium ) ) {
			$medium_styles = array_merge( $medium_styles, array(
				'border-top-width' => "{$settings->height_medium}px"
			) );
		}

		if( !empty( $settings->width_medium ) ) {
			$medium_styles = array_merge( $medium_styles, array(
				'max-width' => "{$settings->width_medium}{$settings->width_medium_unit}"
			) );
		}

		if( !empty( $medium_styles ) ) {
			$medium = Utilities::be_css( array(
				".fl-node-{$id} .fl-module-content .be-hr"  => $medium_styles,
			) );
			echo "@media (max-width: {$global_settings->medium_breakpoint}px){{$medium}}";
		}
		/**
		 * Responsive breakpoint styles
		 */
		if( !empty( $settings->align_responsive ) ) {
			$responsive_styles = array_merge( $responsive_styles, array(
				'margin-left'  => $settings->align_responsive === 'left' ? '0' : 'auto',
				'margin-right' => $settings->align_responsive === 'right' ? '0' : 'auto',
			) );
		}

		if( !empty( $settings->height_responsive ) ) {
			$responsive_styles = array_merge( $responsive_styles, array(
				'border-top-width' => "{$settings->height_responsive}px"
			) );
		}

		if( !empty( $settings->width_responsive ) ) {
			$responsive_styles = array_merge( $responsive_styles, array(
				'max-width' => "{$settings->width_responsive}{$settings->width_responsive_unit}"
			) );
		}

		if( !empty( $responsive_styles ) ) {
			$responsive = Utilities::be_css( array(
				".fl-node-{$id} .fl-module-content .be-hr"  => $responsive_styles,
			) );
			echo "@media (max-width: {$global_settings->responsive_breakpoint}px){{$responsive}}";
		}

	}

	/**
	 * Organize the front end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_js( $module, $settings, $id ) {

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
							'color'         => array(
								'type'          => 'color',
								'label'         => __( 'Color', 'wpcl_beaver_extender' ),
								'default'       => 'EEEEEE',
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'css',
									'selector'      => '.be-hr',
									'property'      => 'border-top-color',
								),
							),
							'height' => array(
								'type'         => 'unit',
								'label'        => __( 'height', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px' ),
								'default_unit' => 'px',
								'default'      => '1',
								'responsive'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
								'preview'       => array(
									'type'          => 'css',
									'selector'      => '.be-hr',
									'property'      => 'border-top-width',
									'unit'          => 'px',
								),
							),
							'width' => array(
								'type'         => 'unit',
								'label'        => __( 'Width', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'vw', '%', 'rem', 'em' ),
								'default_unit' => '%',
								'default'      => '100',
								'responsive'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'align'         => array(
								'type'          => 'select',
								'label'         => __( 'Align', 'wpcl_beaver_extender' ),
								'default'       => 'center',
								'responsive'    => true,
								'options'       => array(
									'center'      => _x( 'Center', 'Alignment.', 'wpcl_beaver_extender' ),
									'left'        => _x( 'Left', 'Alignment.', 'wpcl_beaver_extender' ),
									'right'       => _x( 'Right', 'Alignment.', 'wpcl_beaver_extender' ),
								),
							),
							'style'         => array(
								'type'          => 'select',
								'label'         => __( 'Style', 'wpcl_beaver_extender' ),
								'default'       => 'solid',
								'options'       => array(
									'solid'         => _x( 'Solid', 'Border type.', 'wpcl_beaver_extender' ),
									'dashed'        => _x( 'Dashed', 'Border type.', 'wpcl_beaver_extender' ),
									'dotted'        => _x( 'Dotted', 'Border type.', 'wpcl_beaver_extender' ),
									'double'        => _x( 'Double', 'Border type.', 'wpcl_beaver_extender' ),
								),
								'preview'       => array(
									'type'          => 'css',
									'selector'      => '.be-hr',
									'property'      => 'border-top-style',
								),
								'help'          => __( 'The type of border to use. Double borders must have a height of at least 3px to render properly.', 'wpcl_beaver_extender' ),
							),
						),
					),
				),
			),
		));
	}
}