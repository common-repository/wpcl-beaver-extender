<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEMenu extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Menu', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Insert a menu', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
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

	public function get_form_options() {
		$options = array(
			'none' => __( 'Select menu', 'wpcl_beaver_extender' ),
		);

		$menus = get_terms('nav_menu');

		if( is_array( $menus ) ) {
			foreach( $menus as $menu ) {
				$options[$menu->term_id] = $menu->name;
			}
		}
		return $options;
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

		if( $settings->menu_id !== 'none' && !empty( $settings->menu_id ) ) {
			$args = array(
				'theme_location'  => '',
				'menu'            => intval( $settings->menu_id ),
				'container'       => 'div',
				'container_class' => "bemenu {$settings->menu_type}-menu {$settings->alignment}-menu",
				'container_id'    => '',
				'menu_class'      => 'menu',
				'menu_id'         => '',
				'echo'            => false,
				'fallback_cb'     => 'wp_page_menu',
				'before'          => '',
				'after'           => '',
				'link_before'     => '',
				'link_after'      => '',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'depth'           => 0,
				'walker'          => ''
			);
			echo wp_nav_menu( $args );
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
			'icons'         => array(
				'title'         => __( 'Icons', 'fl-builder' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'menu_id'        => array(
								'type'          => 'select',
								'label'         => __( 'Menu', 'wpcl_beaver_extender' ),
								'default'       => 'none',
								'options'       => $this->get_form_options(),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'menu_type'        => array(
								'type'          => 'select',
								'label'         => __( 'Layout', 'wpcl_beaver_extender' ),
								'default'       => 'horizontal',
								'options'       => array(
									'horizontal' => __( 'Horizontal', 'wpcl_beaver_extender' ),
									'vertical' => __( 'Vertical', 'wpcl_beaver_extender' ),
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'alignment'        => array(
								'type'          => 'select',
								'label'         => __( 'Alignment', 'wpcl_beaver_extender' ),
								'default'       => 'left',
								'options'       => array(
									'left' => __( 'Left', 'wpcl_beaver_extender' ),
									'center' => __( 'Center', 'wpcl_beaver_extender' ),
									'right' => __( 'Right', 'wpcl_beaver_extender' ),
								),
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