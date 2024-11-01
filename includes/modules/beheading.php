<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEHeading extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Heading', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Display a title/page heading.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'icon'				=> 'text.svg',
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
		);
	}

	/**
	 * Get the filter hooks this class subscribes to.
	 * @return array
	 */
	public function get_filters() {
		return array(
			array( 'be_markup_attr_be-heading' => array( 'heading_attr', 10, 3 ) ),
			array( 'be_markup_attr_be-heading-link' => array( 'link_attr', 10, 3 ) ),
		);
	}

	public function heading_attr( $attr, $context, $args ) {
		if( $args['instance'] === $this ) {

			if( $this->settings->tagtype === 'class' ) {
				$attr['class'] .= ' ' . $this->settings->tag;
			}

		}

		return $attr;
	}

	public function link_attr( $attr, $context, $args ) {

		if( $args['instance'] === $this ) {
			$attr['href'] = esc_url_raw( $this->settings->link );
			$attr['target'] = $this->settings->link_target;
			$attr['rel']  = $this->settings->link_nofollow === 'yes' ? 'nofollow' : '';
			$attr['rel'] .= $this->settings->link_target === '_blank' ? ' noopener noreferrer' : '';
		}

		return $attr;
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
			'open' => $settings->tagtype === 'tag' ? "<{$settings->tag} %s>" : '<span %s>',
			'context' => 'be-heading',
			'instance' => $module,
		) );

		if( !empty( $settings->link ) ) {
			Utilities::markup( array(
				'open' => '<a %s>',
				'context' => 'be-heading-link',
				'instance' => $module,
			) );
		}

			Utilities::markup( array(
				'open'  => '<span %s>',
				'close' => '</span>',
				'content' => $settings->heading,
				'context' => 'be-heading-text',
				'instance' => $module,
			) );

		if( !empty( $settings->link ) ) {
			Utilities::markup( array(
				'close' => '</a>',
				'context' => 'be-heading-link',
				'instance' => $module,
			) );
		}

		Utilities::markup( array(
			'close' => $settings->tagtype === 'tag' ? "</{$settings->tag}>" : '</span>',
			'context' => 'be-heading',
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
			'setting_name' => 'typeography',
			'selector' 	   => ".fl-node-{$id} .fl-module-content .be-heading",
		) );

		$css = Utilities::be_css( array(
			".fl-node-{$id} .fl-module-content .be-heading .be-heading-text"  => array(
				'color' => !empty( $settings->color ) ? \FLBuilderColor::hex_or_rgb( $settings->color ) : '',
			),
			".fl-node-{$id} .fl-module-content .be-heading .be-heading-text:hover,
			 .fl-node-{$id} .fl-module-content .be-heading .be-heading-text:focus"  => array(
				'color' => !empty( $settings->hover_color ) ? \FLBuilderColor::hex_or_rgb( $settings->hover_color ) : '',
			),

		) );

		echo $css;
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		\FLBuilder::register_module( __CLASS__, array(
			'general' => array(
				'title' => __( 'General', 'fl-builder' ),
				'sections' => array(
					'general' => array(
						'title' => '',
						'fields' => array(
							'heading' => array(
								'type' => 'text',
								'label' => __( 'Heading', 'fl-builder' ),
								'default' => '',
								'preview' => array(
									'type' => 'text',
									'selector' => '.be-heading-text',
								),
								'connections' => array( 'string' ),
							),
							'tagtype' => array(
								'type' => 'select',
								'label' => __( 'Type', 'fl-builder' ),
								'default' => 'tag',
								'options' => array(
									'tag' => __( 'Tag', 'fl-builder' ),
									'class' => __( 'Class', 'fl-builder' ),
								),
							),
							'tag' => array(
								'type' => 'select',
								'label' => __( 'Tag', 'fl-builder' ),
								'default' => 'h1',
								'options' => array(
									'h1' => 'h1',
									'h2' => 'h2',
									'h3' => 'h3',
									'h4' => 'h4',
									'h5' => 'h5',
									'h6' => 'h6',
								),
							),
							'link' => array(
								'type' => 'link',
								'label' => __( 'Link', 'wpcl_beaver_extender' ),
								'placeholder' => __( 'http://www.example.com', 'wpcl_beaver_extender' ),
								'show_target' => true,
								'show_nofollow' => true,
								'preview' => array(
									'type' => 'refresh',
								),
							),
							'color' => array(
								'type' => 'color',
								'show_reset' => true,
								'show_alpha' => true,
								'label' => __( 'Text Color', 'fl-builder' ),
							),
							'hover_color' => array(
								'type' => 'color',
								'show_reset' => true,
								'show_alpha' => true,
								'label' => __( 'Hover Color', 'fl-builder' ),
							),
							'typeography' => array(
								'type' => 'typography',
								'label' => 'Button Fonts',
								'responsive' => true,
								'preview' => array(
									'type' => 'css',
									'selector' => '.be-heading',
								),
							),
						),
					),
				),
			),
		));
	}
}