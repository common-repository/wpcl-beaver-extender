<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEIcon extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Icon', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Display an icon and optional title.', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'editor_export' 	=> true,
			'partial_refresh'	=> true,
			'icon'				=> 'star-filled.svg',
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
			array( "be_markup_attr_be-icon" => array( 'icon_atts', 10, 3 ) ),
			array( "be_markup_attr_be-icon-wrap" => array( 'icon_wrap_atts', 10, 3 ) ),
			array( "be_markup_attr_be-icon-link" => array( 'icon_link_atts', 10, 3 ) ),
			array( "be_markup_attr_be-icon-text" => array( 'icon_text_atts', 10, 3 ) ),
		);
	}

	public function icon_atts( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		$atts['class'] .= ' ' . $this->settings->icon;

		return $atts;
	}

	public function icon_wrap_atts( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		if( $this->settings->icon_alignment !== false && !empty( $this->settings->icon_alignment ) ) {
			$atts['class'] .= sprintf( ' be-icon-align-%s', $this->settings->icon_alignment );
		}

		if( $this->settings->text_placement !== false && !empty( $this->settings->text_placement ) ) {
			$atts['class'] .= sprintf( ' text-%s', $this->settings->text_placement );
		}

		return $atts;
	}

	public function icon_link_atts( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		$atts['class'] = 'be-icon-wrap';

		if( !empty( $this->settings->link ) ) {
			$atts['class'] .= ' be-icon-link';
			$atts['href']  = esc_url_raw( $this->settings->link );
			$atts['target'] = $this->settings->link_target;
		}

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

		// var_dump($settings);
		/**
		 * Maybe use a link
		 */
		$icon_link_tag = !empty( $settings->link ) ? 'a' : 'span';
		/**
		 * Do the output
		 */
		if( !isset( $settings->exclude_wrapper ) ) {
			Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-icon-wrap", 'instance' => $module ) );
		}

			if( !empty( $settings->text ) && ( $settings->text_placement === 'above' || $settings->text_placement === 'left' ) ) {
				Utilities::markup( array( 'open' => '<div %s>', 'close' => '</div>', 'context' => "be-icon-text", 'content' => $settings->text, 'instance' => $module ) );
			}

			Utilities::markup( array( 'open' => "<div %s>", 'context' => "be-icon-container", 'instance' => $module ) );

				Utilities::markup( array( 'open' => "<{$icon_link_tag} %s>", 'context' => "be-icon-link", 'instance' => $module ) );

					Utilities::markup( array( 'open' => '<span %s>', 'close' => '</span>', 'context' => "be-icon", 'instance' => $module ) );

				Utilities::markup( array( 'close' => "</{$icon_link_tag}>", 'context' => "be-icon-link", 'instance' => $module ) );

			Utilities::markup( array( 'close' => "</div>", 'context' => "be-icon-container", 'instance' => $module ) );

			if( !empty( $settings->text ) && $settings->text_placement !== 'above' && $settings->text_placement !== 'left' ) {
				Utilities::markup( array( 'open' => '<div %s>', 'close' => '</div>', 'context' => "be-icon-text", 'content' => $settings->text, 'instance' => $module ) );
			}

		if( !isset( $settings->exclude_wrapper ) ) {
			Utilities::markup( array( 'close'  => '</div>', 'context' => "be-icon-wrap", 'instance' => $module ) );
		}
	}

	public static function get_css( $settings ) {
		$rules = array(
			'base' => array(),
			'hover' => array(),
			'medium' => array(),
			'responsive' => array(),
			'margins' => array(),
		);
		/**
		 * Base CSS Rules
		 */
		$rules['base'] = array(
			'font-size' => !empty( $settings->font_size ) ? $settings->font_size . $settings->font_size_unit : '',
			'color' => !empty( $settings->color ) ? \FLBuilderColor::hex_or_rgb( $settings->color ) : '',
			'background-color' => !empty( $settings->bg_color ) ? \FLBuilderColor::hex_or_rgb( $settings->bg_color ) : '',
			'width' => !empty( $settings->bg_size ) ? $settings->bg_size . $settings->bg_size_unit : '',
			'height' => !empty( $settings->bg_size ) ? $settings->bg_size . $settings->bg_size_unit : '',
			'line-height' => !empty( $settings->bg_size ) ? $settings->bg_size . $settings->bg_size_unit : '',
		);
		/**
		 * Hover CSS Rules
		 */
		if( !empty( $settings->hover_color ) ) {
			$rules['hover']['color'] = \FLBuilderColor::hex_or_rgb( $settings->hover_color );
		}

		if( !empty( $settings->bg_hover_color ) ) {
			$rules['hover']['background-color'] = \FLBuilderColor::hex_or_rgb( $settings->bg_hover_color );
		}
		/**
		 * Margin Rules
		 */
		foreach( array( 'top', 'right', 'bottom', 'left' ) as $pos ) {
			if( !empty( $settings->{"icon_margin_{$pos}"} ) ) {
				$rules['margins']['desktop']["margin-{$pos}"] = $settings->{"icon_margin_{$pos}"} . $settings->icon_margin_unit;
			}
		}
		/**
		 * Medium CSS Rules
		 */
		$rules['medium'] = array(
			'font-size' => !empty( $settings->font_size_medium ) ? $settings->font_size_medium . $settings->font_size_medium_unit : '',
			'width' => !empty( $settings->bg_size_medium ) ? $settings->bg_size_medium . $settings->bg_size_medium_unit : '',
			'height' => !empty( $settings->bg_size_medium ) ? $settings->bg_size_medium . $settings->bg_size_medium_unit : '',
			'line-height' => !empty( $settings->bg_size_medium ) ? $settings->bg_size_medium . $settings->bg_size_medium_unit : '',
		);
		foreach( array( 'top', 'right', 'bottom', 'left' ) as $pos ) {
			if( !empty( $settings->{"icon_margin_{$pos}_medium"} ) ) {
				$rules['margins']['medium']["margin-{$pos}"] = $settings->{"icon_margin_{$pos}_medium"} . $settings->icon_margin_medium_unit;
			}
		}
		/**
		 * Responsive CSS Rules
		 */
		$rules['responsive'] = array(
			'font-size' => !empty( $settings->font_size_responsive ) ? $settings->font_size_responsive . $settings->font_size_responsive_unit : '',
			'width' => !empty( $settings->bg_size_responsive ) ? $settings->bg_size_responsive . $settings->bg_size_responsive_unit : '',
			'height' => !empty( $settings->bg_size_responsive ) ? $settings->bg_size_responsive . $settings->bg_size_responsive_unit : '',
			'line-height' => !empty( $settings->bg_size_responsive ) ? $settings->bg_size_responsive . $settings->bg_size_responsive_unit : '',
		);
		foreach( array( 'top', 'right', 'bottom', 'left' ) as $pos ) {
			if( !empty( $settings->{"icon_margin_{$pos}_responsive"} ) ) {
				$rules['margins']['responsive']["margin-{$pos}"] = $settings->{"icon_margin_{$pos}_responsive"} . $settings->icon_margin_responsive_unit;
			}
		}

		return $rules;
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

		$rules = self::get_css( $settings );

		/**
		 * Do border / radius
		 */
		\FLBuilderCSS::border_field_rule( array(
			'settings' 	=> $settings,
			'setting_name' 	=> 'border_radius',
			'selector' 	=> ".fl-node-{$id} .be-icon",
		) );

		$css = '';

		if( array_filter( $rules['base'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon,
				 .fl-node-{$id}.fl-module-beicon .be-icon:before,
				 .fl-node-{$id}.fl-module-beicon a .be-icon,
				 .fl-node-{$id}.fl-module-beicon a .be-icon:before"  => $rules['base'],
			) );
		}
		if( array_filter( $rules['hover'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon:hover,
				 .fl-node-{$id}.fl-module-beicon a:hover .be-icon,
				 .fl-node-{$id}.fl-module-beicon .be-icon:focus,
				 .fl-node-{$id}.fl-module-beicon a:focus .be-icon,
				 .fl-node-{$id}.fl-module-beicon .be-icon:hover:before,
				 .fl-node-{$id}.fl-module-beicon a:hover .be-icon:before,
				 .fl-node-{$id}.fl-module-beicon .be-icon:focus:before,
				 .fl-node-{$id}.fl-module-beicon a:focus .be-icon:before"  => $rules['hover'],
			) );
		}

		if( isset( $rules['margins']['desktop'] ) && !empty( $rules['margins']['desktop'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon-container" => $rules['margins']['desktop'],
			) );
		}

		$global_settings  = \FLBuilderModel::get_global_settings();

		/**
		 * Medium output
		 */
		if( array_filter( $rules['medium'] ) ) {
			$medium = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon,
				 .fl-node-{$id}.fl-module-beicon .be-icon:before,
				 .fl-node-{$id}.fl-module-beicon a .be-icon,
				 .fl-node-{$id}.fl-module-beicon a .be-icon:before"  => $rules['medium'],
			) );

			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$medium}}";
		}
		if( isset( $rules['margins']['medium'] ) && !empty( $rules['margins']['medium'] ) ) {
			$margin_medium = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon-container" => $rules['margins']['medium'],
			) );
			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$margin_medium}}";
		}

		/**
		 * Responsive output
		 */
		if( array_filter( $rules['responsive'] ) ) {
			$responsive = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon,
				 .fl-node-{$id}.fl-module-beicon .be-icon:before,
				 .fl-node-{$id}.fl-module-beicon a .be-icon,
				 .fl-node-{$id}.fl-module-beicon a .be-icon:before"  => $rules['responsive'],
			) );

			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$responsive}}";
		}
		if( isset( $rules['margins']['responsive'] ) && !empty( $rules['margins']['responsive'] ) ) {
			$margin_responsive = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicon .be-icon-container" => $rules['margins']['responsive'],
			) );
			$css .= "@media (max-width: {$global_settings->responsive_breakpoint}px){{$margin_responsive}}";
		}

		echo $css;
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
								'icon'          => array(
									'type'          => 'icon',
									'label'         => __( 'Icon', 'wpcl_beaver_extender' ),
								),
							),
						),
						'link'          => array(
							'title'         => __( 'Link', 'wpcl_beaver_extender' ),
							'fields'        => array(
								'link'          => array(
									'type'          => 'link',
									'label'         => __( 'Link', 'wpcl_beaver_extender' ),
									'preview'       => array(
										'type'          => 'refresh',
									),
								),
								'link_target'   => array(
									'type'          => 'select',
									'label'         => __( 'Link Target', 'wpcl_beaver_extender' ),
									'default'       => '_self',
									'options'       => array(
										'_self'         => __( 'Same Window', 'wpcl_beaver_extender' ),
										'_blank'        => __( 'New Window', 'wpcl_beaver_extender' ),
									),
									'preview'       => array(
										'type'          => 'none',
									),
								),
							),
						),
						'text'          => array(
							'title'         => __( 'Text', 'wpcl_beaver_extender' ),
							'fields'        => array(
								'text'          => array(
									'type'          => 'editor',
									'label'         => '',
									'media_buttons' => false,
									'connections'   => array( 'string' ),
									'preview'       => array(
										'type'          => 'refresh',
									),
								),
							),
						),
					),
			),
			'style'         => array( // Tab
				'title'         => __( 'Style', 'wpcl_beaver_extender' ), // Tab title
				'sections'      => array( // Tab Sections
					'colors'        => array( // Section
						'title'         => __( 'Colors', 'wpcl_beaver_extender' ), // Section Title
						'fields'        => array( // Section Fields
							'color'         => array(
								'type'          => 'color',
								'label'         => __( 'Color', 'wpcl_beaver_extender' ),
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Hover Color', 'wpcl_beaver_extender' ),
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'none',
								),
							),
							'bg_color'      => array(
								'type'          => 'color',
								'label'         => __( 'Background Color', 'wpcl_beaver_extender' ),
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'bg_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Background Hover Color', 'wpcl_beaver_extender' ),
								'show_reset'    => true,
								'preview'       => array(
									'type'          => 'none',
								),
							),
						),
					),
					'structure'     => array( // Section
						'title'         => __( 'Structure', 'wpcl_beaver_extender' ), // Section Title
						'fields'        => array( // Section Fields
							'font_size'          => array(
								'type'          => 'unit',
								'label'         => __( 'Font Size', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem' ),
								'default_unit' => 'px',
								'responsive'   => true,
								'sanitize'		=> 'absint',
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'bg_size'          => array(
								'type'          => 'unit',
								'label'         => __( 'Background Size', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem' ),
								'default_unit' => 'px',
								'responsive'   => true,
								'sanitize'		=> 'absint',
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'border_radius'          => array(
								'type'       => 'border',
								'label'         => __( 'Border Radius', 'wpcl_beaver_extender' ),
								'responsive' => true,
								'preview'    => array(
									'type'     => 'refresh'
								),
							),
							'icon_alignment'         => array(
								'type'          => 'select',
								'label'         => __( 'Icon Alignment', 'fl-builder' ),
								'default'       => 'center',
								'options'       => array(
									'center'        => __( 'Center', 'fl-builder' ),
									'left'          => __( 'Left', 'fl-builder' ),
									'right'         => __( 'Right', 'fl-builder' ),
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'text_placement'         => array(
								'type'          => 'select',
								'label'         => __( 'Text Placement', 'fl-builder' ),
								'default'       => 'below',
								'options'       => array(
									'below'        => __( 'Below', 'fl-builder' ),
									'above'        => __( 'Above', 'fl-builder' ),
									'left'          => __( 'Left', 'fl-builder' ),
									'right'         => __( 'Right', 'fl-builder' ),
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'icon_margin' => array(
								'type'        => 'dimension',
								'label'       => 'Icon Margin',
								'units'	       => array( 'px', 'em', 'rem' ),
								'default_unit' => 'px',
								'responsive'   => true,
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