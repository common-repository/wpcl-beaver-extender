<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BETabs extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
		parent::__construct(array(
			'name'          	=> __( 'Tabs', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Display a collection of tabbed content.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'partial_refresh'	=> true,
			'icon'				=> 'layout.svg',
			'dir'           => Plugin::path('/'),
			'url'           => Plugin::url('/'),
		));

		$this->add_css( 'font-awesome-5' );

		$this->add_js( 'betabs', Plugin::url( 'js/jquery.betabs.min.js' ), array( 'jquery' ), Plugin::$version, false );
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
			array( 'be_markup_be-tabs-label_content' => array( 'label_content', 10, 2 ) ),
			array( 'be_markup_be-tabs-panel_content' => array( 'panel_content', 10, 2 ) ),
			array( 'be_markup_attr_be-tabs-label' => array( 'label_attr', 10, 3 ) ),
			array( 'be_markup_attr_be-tabs-panel' => array( 'panel_attr', 10, 3 ) ),
			array( 'be_markup_attr_be-tabs' => array( 'tabs_attr', 10, 3 ) ),
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

		Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-tabs", 'instance' => $module ) );

			Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-tabs-labels-container", 'instance' => $module ) );

				Utilities::markup( array( 'open' => '<ul %s>', 'context' => "be-tabs-labels", 'instance' => $module ) );

					foreach( $settings->items as $i => $item ) {

						Utilities::markup( array( 'open' => '<li %s>', 'close' => '</li>', 'context' => "be-tabs-label", 'item' => $item, 'index' => $i, 'instance' => $module ) );

					}

				Utilities::markup( array( 'close'  => '</ul>', 'context' => "be-tabs-labels", 'instance' => $module ) );

			Utilities::markup( array( 'close'  => '</div>', 'context' => "be-tabs-labels-container", 'instance' => $module ) );

			Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-tabs-panels-container", 'instance' => $module ) );

				Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-tabs-panels", 'instance' => $module ) );

					foreach( $settings->items as $i => $item ) {

						Utilities::markup( array( 'open' => '<div %s>', 'close' => '</div>', 'context' => "be-tabs-panel", 'item' => $item, 'index' => $i, 'instance' => $module ) );

					}

				Utilities::markup( array( 'close'  => '</div>', 'context' => "be-tabs-panels", 'instance' => $module ) );

			Utilities::markup( array( 'close'  => '</div>', 'context' => "be-tabs-panels-container", 'instance' => $module ) );

		Utilities::markup( array( 'close'  => '</div>', 'context' => "be-tabs", 'instance' => $module ) );
	}

	public function tabs_attr( $attr, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $attr;
		}
		$attr['class'] .= ' ' . $this->settings->layout;

		return $attr;
	}

	public function label_content( $content, $args ) {

		if( $args['instance'] !== $this ) {
			return $content;
		}

		$content .= sprintf( '<a href="#panel-%s-%s">', $this->node, $args['index'] );

		$content .= !empty( $args['item']->label_icon ) ? sprintf( '<span class="label-icon %s"></span>', $args['item']->label_icon ) : '';

		$content .= sprintf( '<span class="label-text">%s</span>', $args['item']->label );

		$content .= '</a>';

		return $content;

	}

	public function label_attr( $attr, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $attr;
		}
		if( $args['index'] === 0 ) {
			$attr['class'] .= ' active';
		}

		return $attr;
	}

	public function panel_content( $content, $args ) {

		if( $args['instance'] !== $this ) {
			return $content;
		}

		Utilities::markup( array( 'open' => '<div %s>', 'close' => '</div>', 'context' => "be-tabs-label", 'item' => $args['item'], 'index' => $args['index'], 'instance' => $this ) );

		$content .= '<div class="panel-content">';

		$content .= apply_filters( 'the_content', $args['item']->content );

		$content .= '</div>';

		return $content;

	}

	public function panel_attr( $attr, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $attr;
		}
		if( $args['index'] === 0 ) {
			$attr['class'] .= ' active';
		}

		$attr['id'] = sprintf( 'panel-%s-%s', $this->node, $args['index'] );

		return $attr;
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

		echo 'jQuery( document ).ready( function( $ ) {';

			printf( '$( ".fl-node-%s" ).BETabs()', $this->node );

		echo '});';
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		/**
		 * Register the module and its form settings.
		 */
		\FLBuilder::register_module( __CLASS__, array(
			'items'         => array(
				'title'         => __( 'Items', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'items'         => array(
								'type'          => 'form',
								'label'         => __( 'Item', 'wpcl_beaver_extender' ),
								'form'          => 'betabs_items_form', // ID from registered form below
								'preview_text'  => 'label', // Name of a field to use for the preview text
								'multiple'      => true,
							),
						),
					),
				),
			),
			'style'        => array(
				'title'         => __( 'Style', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'layout'        => array(
								'type'          => 'select',
								'label'         => __( 'Layout', 'wpcl_beaver_extender' ),
								'default'       => 'horizontal',
								'options'       => array(
									'horizontal'    => __( 'Horizontal', 'wpcl_beaver_extender' ),
									'vertical'      => __( 'Vertical', 'wpcl_beaver_extender' ),
								),
							),
							'border_color'  => array(
								'type'          => 'color',
								'label'         => __( 'Border Color', 'wpcl_beaver_extender' ),
								'default'       => 'e5e5e5',
							),
						),
					),
				),
			),
		));

		/**
		 * Register a settings form to use in the "form" field type above.
		 */
		\FLBuilder::register_settings_form( 'betabs_items_form', array(
			'title' => __( 'Add Item', 'wpcl_beaver_extender' ),
			'tabs'  => array(
				'general'      => array(
					'title'         => __( 'General', 'wpcl_beaver_extender' ),
					'sections'      => array(
						'general'       => array(
							'title'         => '',
							'fields'        => array(
								'label'         => array(
									'type'          => 'text',
									'label'         => __( 'Label', 'wpcl_beaver_extender' ),
									'connections'   => array( 'string' ),
								),
								'label_icon'         => array(
									'type'          => 'icon',
									'label'         => __( 'Icon', 'wpcl_beaver_extender' ),
									'show_remove'   => true
								),
							),
						),
						'content'       => array(
							'title'         => __( 'Content', 'wpcl_beaver_extender' ),
							'fields'        => array(
								'content'       => array(
									'type'          => 'editor',
									'label'         => 'Tab Content',
									'wpautop'		=> true,
								),
							),
						),
					),
				),
			),
		));
	}
}