<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEGmaps extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Google Map', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Display a Google map.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'partial_refresh'	=> true,
			'icon'				=> 'location.svg',
			'dir'           => Plugin::path('/'),
			'url'           => Plugin::url('/'),
		));

		/**
		 * Enqueue additional Javascript
		 */
		$apikey = Utilities::get_settings( 'google_maps_api', '' );

		if( !empty( $apikey ) ) {
			$this->add_js( 'googlemaps', "//maps.googleapis.com/maps/api/js?key={$apikey}", array(), '', false );
		}
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
			array( 'be_markup_attr_be-gmap' => array( 'map_attr', 10, 3 ) ),
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

		$apikey = Utilities::get_settings( 'google_maps_api', '' );

		if( !empty( $apikey ) ) {

			Utilities::markup( array(
				'open'  => '<div %s>',
				'close' => '</div>',
				'context' => "be-gmap",
				'instance' => $module,
			) );
		}

		else if( is_user_logged_in() ) {
			Utilities::markup( array(
				'open'  => '<p %s>',
				'close' => '</a>',
				'content' => sprintf( 'Google Maps requires an <a href="%s" target="_blank">API Key</a>.', admin_url( 'options-general.php?page=fl-builder-settings#wpcl_beaver_extender' ) ),
				'context' => "be-alert",
				'instance' => $module,
			) );
		}
	}

	public function map_attr( $atts, $context, $args ) {
		/**
		 * Bail if not this instance
		 */
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		$atts['id'] = "be-map-{$this->node}";

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

		$global_settings  = \FLBuilderModel::get_global_settings();

		$styles = array(
			'default' => array(),
			'medium' => array(),
			'responsive' => array(),
		);

		if( $settings->sizing === 'fixed' ) {
			$styles['default'] = array(
				'height' => !empty( $settings->height ) ? "{$settings->height}{$settings->height_unit}" : '300px',
			);
			if( !empty( $settings->height_medium ) ) {
				$styles['medium'] = array(
					'height' => $settings->height_medium . $settings->height_medium_unit
				);
			}
			if( !empty( $settings->height_responsive ) ) {
				$styles['responsive'] = array(
					'height' => $settings->height_responsive . $settings->height_responsive_unit
				);
			}
		}

		else {
			$styles['default'] = array(
				'height' => 0,
				'padding-bottom' => "{$settings->aspect}%",
				'min-height' => !empty( $settings->minheight ) ? "{$settings->minheight}px" : '',
			);
		}

		/**
		 * Output the CSS
		 */
		echo Utilities::be_css( array(
			".fl-node-{$id} .fl-module-content .be-gmap" => $styles['default'],
		) );

		if( !empty( $styles['medium'] ) ) {
			echo "@media (max-width: {$global_settings->medium_breakpoint}px){";

				echo Utilities::be_css( array(
					".fl-node-{$id} .fl-module-content .be-gmap" => $styles['medium'],
				) );

			echo '}';
		}

		if( !empty( $styles['responsive'] ) ) {
			echo "@media (max-width: {$global_settings->responsive_breakpoint}px){";

				echo Utilities::be_css( array(
					".fl-node-{$id} .fl-module-content .be-gmap" => $styles['responsive'],
				) );

			echo '}';
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

		include Plugin::path( 'js/templates/bemap.js.php' );
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		/**
		 * Register the module and its form settings.
		 */
		\FLBuilder::register_module( __CLASS__, array(
			'general'       => array(
				'title'         => __( 'General', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'center_address'       => array(
								'type'          => 'textarea',
								'rows'			=> '3',
								'label'         => __( 'Center', 'wpcl_beaver_extender' ),
								'placeholder'   => __( '1865 Winchester Blvd #202 Campbell, CA 95008', 'wpcl_beaver_extender' ),
								'preview'       => array(
									'type'            => 'refresh',
								),
							),
							'markers'         => array(
								'type'          => 'form',
								'label'         => __( 'Markers', 'wpcl_beaver_extender' ),
								'form'          => 'be_map_marker_form', // ID from registered form below
								'preview_text'  => 'title', // Name of a field to use for the preview text
								'multiple'      => true,
							),
							'zoom' => array(
							    'type'          => 'select',
							    'label'         => __( 'Zoom', 'wpcl_beaver_extender' ),
							    'default'       => '13',
							    'options'       => array(
							        '1'      => '1',
							        '2'      => '2',
							        '3'      => '3',
							        '4'      => '4',
							        '5'      => '5',
							        '6'      => '6',
							        '7'      => '7',
							        '8'      => '8',
							        '9'      => '9',
							        '10'      => '10',
							        '11'      => '11',
							        '12'      => '12',
							        '13'      => '13',
							        '14'      => '14',
							        '15'      => '15',
							        '16'      => '16',
							        '17'      => '17',
							        '18'      => '18',
							        '19'      => '19',
							        '20'      => '20',
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'minZoom' => array(
							    'type'          => 'select',
							    'label'         => __( 'Min Zoom', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							        '1'      => '1',
							        '2'      => '2',
							        '3'      => '3',
							        '4'      => '4',
							        '5'      => '5',
							        '6'      => '6',
							        '7'      => '7',
							        '8'      => '8',
							        '9'      => '9',
							        '10'      => '10',
							        '11'      => '11',
							        '12'      => '12',
							        '13'      => '13',
							        '14'      => '14',
							        '15'      => '15',
							        '16'      => '16',
							        '17'      => '17',
							        '18'      => '18',
							        '19'      => '19',
							        '20'      => '20',
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'maxZoom' => array(
							    'type'          => 'select',
							    'label'         => __( 'Max Zoom', 'wpcl_beaver_extender' ),
							    'default'       => '20',
							    'options'       => array(
							        '1'      => '1',
							        '2'      => '2',
							        '3'      => '3',
							        '4'      => '4',
							        '5'      => '5',
							        '6'      => '6',
							        '7'      => '7',
							        '8'      => '8',
							        '9'      => '9',
							        '10'      => '10',
							        '11'      => '11',
							        '12'      => '12',
							        '13'      => '13',
							        '14'      => '14',
							        '15'      => '15',
							        '16'      => '16',
							        '17'      => '17',
							        '18'      => '18',
							        '19'      => '19',
							        '20'      => '20',
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'zoomControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Zoom Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'panControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Pan Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'mapTypeControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Map Type Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'scaleControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Scale Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'streetViewControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Street View Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'overviewMapControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Overview Map Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'scrollwheel' => array(
							    'type'          => 'select',
							    'label'         => __( 'Scroll Wheel', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
							'rotateControl' => array(
							    'type'          => 'select',
							    'label'         => __( 'Rotate Control', 'wpcl_beaver_extender' ),
							    'default'       => '1',
							    'options'       => array(
							    	'1' => __( 'True', 'wpcl_beaver_extender' ),
							    	'0' => __( 'False', 'wpcl_beaver_extender' ),
							    ),
							    'preview'       => array(
							    	'type'            => 'refresh',
							    ),
							),
						),
					),
				),
			),
			'style'         => array( // Tab
				'title'         => __( 'Style', 'wpcl_beaver_extender' ), // Tab title
				'sections'      => array( // Tab Sections
					'style'        => array( // Section
						'title'         => __( 'Style', 'wpcl_beaver_extender' ), // Section Title
						'fields'        => array( // Section Fields
							'sizing'         => array(
								'type'          => 'select',
								'label'         => __( 'Sizing', 'wpcl_beaver_extender' ),
								'default'       => 'fixed',
								'options'       => array(
									'fixed'  => __( 'Fixed Height', 'wpcl_beaver_extender' ),
									'fluid' => __( 'Fluid Height', 'wpcl_beaver_extender' ),
								),
								'preview'       => array(
									'type'            => 'refresh',
								),
								'toggle'        => array(
								    'fixed'      => array(
								        'fields'        => array( 'height' ),
								    ),
								    'fluid'      => array(
								        'fields'        => array( 'maxheight', 'minheight', 'aspect' ),
								    ),
								)
							),
							'height'        => array(
								'type'          => 'unit',
								'label'         => __( 'Height', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', 'vw' ),
								'default_unit' => 'px', // Optional
								'default'      => '400',
								'slider' => array(
									'min' => 0,
									'max' => 1000,
								),
								'responsive' => true,
								'sanitize'		=> 'absint',
								'preview'       => array(
									'type'            => 'refresh',
								),
							),
							'aspect'        => array(
								'type'          => 'unit',
								'label'         => __( 'Height Ratio', 'wpcl_beaver_extender' ),
								'default'       => '56.25',
								'units'	       => array( '%'),
								'slider' => true,
								'default_unit' => '%', // Optional
								'sanitize'		=> 'absint',
								'preview'       => array(
									'type'            => 'refresh',
								),
							),
							'minheight'        => array(
								'type'          => 'unit',
								'label'         => __( 'Min Height', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', 'vw' ),
								'default_unit' => 'px', // Optional
								'default'      => '400',
								'slider' => array(
									'min' => 0,
									'max' => 1000,
								),
								'sanitize'		=> 'absint',
								'preview'       => array(
									'type'            => 'refresh',
								),
							),
							'style' => array(
							    'type'          => 'textarea',
							    'rows'          => '18',
							    'description'   => 'Paste in styles from <a href="https://mapstyle.withgoogle.com/" target="_blank">Map Style</a> or <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a>',
							    'label'         => __( 'Snazzy Maps Style', 'wpcl_beaver_extender' ),
							    'sanitize'	=> '\Wpcl\Be\Classes\Utilities::stringify_array',
							    'preview'       => array(
							    	'type'            => 'refresh',
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
		\FLBuilder::register_settings_form( 'be_map_marker_form', array(
			'title' => __( 'Marker', 'wpcl_beaver_extender' ),
			'tabs'  => array(
				'general'       => array( // Tab
					'title'         => __( 'General', 'wpcl_beaver_extender' ), // Tab title
					'sections'      => array( // Tab Sections
						'general'       => array( // Section
							'title'         => '', // Section Title
							'fields'        => array( // Section Fields
								'address'       => array(
									'type'          => 'textarea',
									'rows'			=> '3',
									'label'         => __( 'Address', 'wpcl_beaver_extender' ),
									'placeholder'   => __( '1865 Winchester Blvd #202 Campbell, CA 95008', 'wpcl_beaver_extender' ),
									'preview'       => array(
										'type'            => 'refresh',
									),
								),
								'title'       => array(
									'type'          => 'text',
									'label'         => __( 'Title', 'wpcl_beaver_extender' ),
									'preview'       => array(
										'type'            => 'refresh',
									),
								),
								'marker'       => array(
									'type'          => 'photo',
									'label'         => __('Custom Marker', 'fl-builder'),
									'show_remove'   => true,
									'preview'       => array(
										'type'            => 'refresh',
									),
								),
								'content'       => array(
									'type'          => 'editor',
									'rows'          => 10,
									'label'         => __( 'Content', 'wpcl_beaver_extender' ),
									'preview'       => array(
										'type'            => 'refresh',
									),
								),
							),
						),
					),
				),
			),
		));
	}
}