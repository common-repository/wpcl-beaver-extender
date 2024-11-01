<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BESimpleMenu extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Simple Menu', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Insert a simple menu', 'wpcl_beaver_extender' ),
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
			array( 'be_markup_attr_be-simple-menu-item-link' => array( 'link_attr', 10, 3 ) ),
			array( 'be_markup_attr_be-simple-menu-item' => array( 'menu_item_attr', 10, 3 ) ),
			array( 'be_markup_attr_be-simple-menu' => array( 'menu_attr', 10, 3 ) ),
		);
	}

	public function link_attr( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}
		// Do the href
		$atts['href']   = !empty( $args['item']->link ) ? $args['item']->link : "";
		// Do the target
		$atts['target'] = $args['item']->link_target;
		// Do the rel
		$atts['rel']    = $args['item']->link_nofollow === 'yes' ? 'nofollow' : '';
		$atts['rel']   .= $args['item']->link_target === '_blank' ? ' noreferrer noopener' : '';

		return $atts;
	}

	public function menu_item_attr( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}
		$queried_object = get_queried_object();

		$linkid = url_to_postid( $args['item']->link );

		if( $queried_object->ID === $linkid ) {
			$atts['class'] .= ' current-item';
		}

		return $atts;
	}

	public function menu_attr( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		$atts['class'] .= " be-{$this->settings->menu_type}";

		$atts['class'] .= " be-align-{$this->settings->alignment}";

		return $atts;
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

		// global $wp;
		// echo url_to_postid( 'https://local19.local/about/zone-maps' );

		// $queried_object = get_queried_object();
		// var_dump( $queried_object );

		// var_dump($settings);

		Utilities::markup( array(
			'open'  => '<ul %s>',
			'context' => "be-simple-menu",
			'instance' => $module,
		) );

		foreach( $settings->menu_items as $item ) {
			// $class =
			// $class = $queried_object->ID === url_to_postid( $item->link ) ? ' current-page' : '';
			Utilities::markup( array(
				'open'  => '<li %s>',
				'context' => "be-simple-menu-item",
				'instance' => $module,
				'item'      => $item,
			) );

				Utilities::markup( array(
					'open'  => '<span><a %s>',
					'close' => '</a></span>',
					'content' => $item->title,
					'context' => "be-simple-menu-item-link",
					'instance' => $module,
					'item'      => $item,
				) );

			Utilities::markup( array(
				'close'  => '</li>',
				'context' => "be-simple-menu-item",
				'instance' => $module,
			) );
		}

		Utilities::markup( array(
			'close'  => '</ul>',
			'context' => "be-simple-menu",
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
			'menu'         => array(
				'title'         => __( 'Menu', 'fl-builder' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'menu_items'         => array(
								'type'          => 'form',
								'label'         => __( 'Menu Item', 'wpcl_beaver_extender' ),
								'form'          => 'be_simple_menu_form', // ID from registered form below
								'preview_text'  => 'title', // Name of a field to use for the preview text
								'multiple'      => true,
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
		/**
		 * Register a settings form to use in the "form" field type above.
		 */
		\FLBuilder::register_settings_form( 'be_simple_menu_form', array(
			'title' => __( 'Menu Item', 'wpcl_beaver_extender' ),
			'tabs'  => array(
				'general'       => array( // Tab
					'title'         => __( 'General', 'wpcl_beaver_extender' ), // Tab title
					'sections'      => array( // Tab Sections
						'general'       => array( // Section
							'title'         => '', // Section Title
							'fields'        => array( // Section Fields
								'title' => array(
									'type'          => 'text',
									'label'         => 'Title',
								),
								'link' => array(
									'type'          => 'link',
									'label'         => 'Link',
									'show_target'	=> true,
									'show_nofollow'	=> true,
								),
							),
						),
					),
				),
			),
		));
	}
}