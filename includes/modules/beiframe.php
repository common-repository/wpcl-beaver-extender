<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEIframe extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'iFrame', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'A divider line to separate content.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'icon'              => 'layout.svg',
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
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'be_markup_attr_be-iframe' => array( 'iframe_attr', 10, 3 ) ),
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
			'open'  => '<iframe %s>',
			'close' => '</iframe>',
			'context' => "be-iframe",
			'instance' => $module,
		) );
	}

	public function iframe_attr( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		$atts['src'] = esc_url_raw( $this->settings->src );

		$atts['frameborder'] = $this->settings->frameborder;

		$atts['scrolling'] = $this->settings->scrolling;

		return $atts;
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

		$styles = array(
			'width' => !empty( $this->settings->width ) ? "{$this->settings->width}{$this->settings->width_unit}" : '',
			'height' => !empty( $this->settings->height ) ? "{$this->settings->height}{$this->settings->height_unit}" : '',
		);

		// Output the CSS
		$css = Utilities::be_css( array(
			".fl-node-{$id} .be-iframe"  => $styles,
		) );

		echo $css;
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
							'src'         => array(
								'type'          => 'text',
								'label'         => __( 'Source URL', 'wpcl_beaver_extender' ),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'frameborder' => array(
								'type'    => 'select',
								'label'   => __( 'Frameborder', 'wpcl_beaver_extender' ),
								'default' => 'none',
								'options' => array(
									'none'    => 'No',
									'true'    => 'Yes',
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'scrolling' => array(
								'type'    => 'select',
								'label'   => __( 'Scrolling', 'wpcl_beaver_extender' ),
								'default' => 'auto',
								'options' => array(
									'auto'    => 'Auto',
									'yes'    => 'Yes',
									'no'    => 'No',
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'width' => array(
								'type'         => 'unit',
								'label'        => __( 'Width', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'vw', '%', 'rem', 'em' ),
								'default_unit' => '%',
								'default'      => '100',
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'height' => array(
								'type'         => 'unit',
								'label'        => __( 'Height', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'vh', 'rem', 'em' ),
								'default_unit' => 'px',
								'default'      => '400',
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
						),
					),
				),
			),
		));
	}
}