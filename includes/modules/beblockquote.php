<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEBlockQuote extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber {

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
			'name'          	=> __( 'Block Quote', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Block Quote Module', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'icon'              => 'format-quote.svg',
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
			'open'  => '<blockquote %s>',
			'context' => "be-blockquote",
			'instance' => $module,
		) );

		printf( '<span class="be-blockquote-content">%s</span>', $settings->quote );

		if( !empty( $settings->cite ) ) {
			printf( '<cite class="be-blockquote-citation"><span class="be-blockquote-citation-content">%s</span></cite>', $settings->cite );
		}

		Utilities::markup( array(
			'close'  => '</blockquote>',
			'context' => "be-blockquote",
			'instance' => $module,
		) );
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

		\FLBuilderCSS::typography_field_rule( array(
			'settings'	   => $settings,
			'setting_name' => 'typography',
			'selector' 	   => ".fl-node-{$id} .be-blockquote",
		) );

		\FLBuilderCSS::typography_field_rule( array(
			'settings'	   => $settings,
			'setting_name' => 'citetypography',
			'selector' 	   => ".fl-node-{$id} .be-blockquote cite",
		) );
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
							'quote' => array(
							    'type'          => 'editor',
							    'label'         => __( 'Quote Text', 'wpcl_beaver_extender' ),
							    'media_buttons' => false,
							    'preview'       => array(
							    	'type'          => 'refresh',
							    ),
							),
							'cite'         => array(
								'type'          => 'text',
								'label'         => __( 'Cite', 'wpcl_beaver_extender' ),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'typography' => array(
								'type'       => 'typography',
								'label'      => 'Quotation Typography',
								'responsive' => true,
								'preview'    => array(
									'type'	    => 'css',
									'selector'  => '.be-blockquote',
								),
							),
							'citetypography' => array(
								'type'       => 'typography',
								'label'      => 'Citation Typography',
								'responsive' => true,
								'preview'    => array(
									'type'	    => 'css',
									'selector'  => '.be-blockquote cite',
								),
							),
						),
					),
				),
			),
		));
	}
}