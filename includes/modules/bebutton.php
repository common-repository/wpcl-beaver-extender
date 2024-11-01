<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEButton extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Button', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Insert a button link', 'wpcl_beaver_extender' ),
			'category'      	=> __( 'Beaver Extender', 'wpcl_beaver_extender' ),
			'partial_refresh'	=> true,
			'editor_export' 	=> true,
			'icon'				=> 'button.svg',
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
			array( 'be_markup_attr_be-button' => array( 'button_attr', 10, 3 ) ),
			array( 'be_markup_be-button_content' => array( 'button_content', 10, 2 ) ),
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
			'open'  => '<a %s>',
			'close' => '</a>',
			'content' => $settings->text,
			'context' => "be-button",
			'instance' => $module,
		) );
	}

	public function button_attr( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}
		// Add the button class
		$atts['class'] .= ' button';
		$atts['class'] .= isset( $this->settings->button_class ) && !empty( $this->settings->button_class  ) ? ' ' . esc_attr( $this->settings->button_class  ) : '';
		// Do the type settings
		$atts['class'] .= sprintf( ' button-%s', $this->settings->button_style );
		$atts['class'] .= !empty( $this->settings->icon ) ? ' has-icon' : '';
		$atts['class'] .= ( !empty( $this->settings->size ) && $this->settings->size !== 'custom' ) ? ' ' . $this->settings->size : '';
		// Do the width settings
		$atts['class'] .= $this->settings->button_width === 'full' ? ' full-width' : '';
		// $atts['style']  = $this->settings->width === 'custom' ? sprintf( 'width: 100%%; max-width: %dpx;', $this->settings->custom_width ) : '';
		// // Do the icon settings
		$atts['class'] .= $this->settings->icon_animation === 'enable' ? ' animated-icon' : '';
		// // Do the href
		$atts['href']   = !empty( $this->settings->link ) ? $this->settings->link : '#';
		// // Do the target
		$atts['target'] = $this->settings->link_target;
		// // Do the rel
		$atts['rel']    = $this->settings->link_nofollow === 'yes' ? 'nofollow' : '';
		$atts['rel']   .= $this->settings->link_target === '_blank' ? ' noreferrer noopener' : '';
		// Maybe ID
		if( isset( $this->settings->button_id ) && !empty( $this->settings->button_id ) ) {
			$atts['id'] = esc_attr( $this->settings->button_id );
		}

		return $atts;
	}

	public function button_content( $content, $args ) {

		if( $args['instance'] !== $this ) {
			return $content;
		}
		// Append a span around the content
		$content = sprintf( '<span class="be-button-content">%s</span>', $this->settings->text );
		// Maybe append icon
		if( !empty( $this->settings->icon ) ) {

			$icon = sprintf( '<span class="be-button-icon be-button-icon-%s %s"></span>', $this->settings->icon_position, $this->settings->icon );

			$icon = '<span class="be-icon-wrapper">' . $icon . '</span>';

			$content = $this->settings->icon_position === 'after' ? $content . $icon : $icon . $content;
		}
		return "<span class='be-button-inner'>{$content}</span>";


	}

	/**
	 * Organize the css end output
	 * @param  [object] $module  : The instance of this module
	 * @param  [array] $settings : The array of settings for this instance
	 * @param  [string] $id : the unique ID of the module
	 */
	public function do_css( $module, $settings, $id ) {
		// Bail if it's not this specific instance
		if( $module !== $this || !is_object( $settings ) ) {
			return;
		}
		\FLBuilderCSS::typography_field_rule( array(
			'settings'	   => $settings,
			'setting_name' => 'typography',
			'selector' 	   => ".fl-node-{$id}.fl-module-bebutton .be-button",
		) );
		\FLBuilderCSS::border_field_rule( array(
			'settings' 	=> $settings,
			'setting_name' 	=> 'button_border',
			'selector' 	=> ".fl-node-{$id}.fl-module-bebutton .be-button",
		) );

		if( !empty( $settings->hover_border_color ) ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button:hover,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus" => array(
					'border-color' => \FLBuilderColor::hex_or_rgb( $settings->hover_border_color )
				)
			) );
		} else if( $settings->button_border['color'] ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button:hover,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus" => array(
					'border-color' => \FLBuilderColor::hex_or_rgb( $settings->button_border['color'] )
				)
			) );
		}
		if( !empty( $settings->button_text_color ) ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button,
				 .fl-node-{$id}.fl-module-bebutton .be-button *" => array(
					'color' => \FLBuilderColor::hex_or_rgb( $settings->button_text_color )
				)
			) );
		}

		if( !empty( $settings->button_text_hover_color ) ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button:hover,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus,
				 .fl-node-{$id}.fl-module-bebutton .be-button:hover *,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus *" => array(
					'color' => \FLBuilderColor::hex_or_rgb( $settings->button_text_hover_color )
				)
			) );
		} else if( !empty( $settings->button_text_color ) ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button:hover,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus,
				 .fl-node-{$id}.fl-module-bebutton .be-button:hover *,
				 .fl-node-{$id}.fl-module-bebutton .be-button:focus *" => array(
					'color' => \FLBuilderColor::hex_or_rgb( $settings->button_text_color )
				)
			) );
		}
		/**
		 * Transparent button styles
		 */
		if( $settings->button_style === 'transparent' ) {
			// Extra hover stuff
			if( !empty( $settings->button_bg_hover_color ) ) {
				echo Utilities::be_css( array(
					".fl-node-{$id}.fl-module-bebutton .be-button:hover,
					 .fl-node-{$id}.fl-module-bebutton .be-button:focus" => array(
						'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color )
					)
				) );
			}
		}

		/**
		 * Gradient styles
		 */
		if( $settings->button_style === 'gradient' ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button" => array(
					'background-image' => \FLBuilderColor::gradient( $settings->button_gradient ),
			) ) );
		}
		/**
		 * Flat Styles
		 */
		if( $settings->button_style === 'flat' ) {
			echo Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .be-button" => array(
					'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_color ),
			) ) );
			if( !empty( $settings->button_bg_hover_color ) ) {
				echo Utilities::be_css( array(
					".fl-node-{$id}.fl-module-bebutton .be-button:hover,
					 .fl-node-{$id}.fl-module-bebutton .be-button:focus" => array(
						'background-color' => \FLBuilderColor::hex_or_rgb( $settings->button_bg_hover_color ),
				) ) );
			}
		}
		/**
		 * Size and alignment
		 */
		$styles = array(
			'padding-top' => isset( $settings->button_padding_top ) && !empty( $settings->button_padding_top ) ? "{$settings->button_padding_top}{$settings->button_padding_unit}" : '',
			'padding-right' => isset( $settings->button_padding_right ) && !empty( $settings->button_padding_right ) ? "{$settings->button_padding_right}{$settings->button_padding_unit}" : '',
			'padding-bottom' => isset( $settings->button_padding_bottom ) && !empty( $settings->button_padding_bottom ) ? "{$settings->button_padding_bottom}{$settings->button_padding_unit}" : '',
			'padding-left' => isset( $settings->button_padding_left ) && !empty( $settings->button_padding_left ) ? "{$settings->button_padding_left}{$settings->button_padding_unit}" : '',
		);

		if( $settings->button_width === 'custom' && !empty( $settings->button_custom_width ) ) {
			$styles['max-width']     = '100%';
			$styles['width'] = "{$settings->button_custom_width}{$settings->button_custom_width_unit}";
		}

		echo Utilities::be_css( array(
			".fl-node-{$id}.fl-module-bebutton .be-button" => $styles,
		) );
		echo Utilities::be_css( array(
			".fl-node-{$id}.fl-module-bebutton .fl-module-content" => array( 'text-align' => $settings->button_align ),
		) );
		echo Utilities::be_css( array(
			".fl-node-{$id}.fl-module-bebutton" => array( 'display' => $settings->button_display ),
		) );

		$global_settings  = \FLBuilderModel::get_global_settings();
		/**
		 * Medium breakpoint styles
		 */
		$medium = '';
		if( !empty( $settings->button_align_medium ) ) {
			$medium .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .fl-module-content" => array( 'text-align' => $settings->button_align_medium ),
			) );
		}

		if( !empty( $settings->button_display_medium ) ) {
			$medium .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton" => array( 'display' => $settings->button_display_medium ),
			) );
		}

		if( !empty( $medium ) ) {
			echo "@media (max-width: {$global_settings->medium_breakpoint}px){{$medium}}";
		}
		/**
		 * Small breakpoint styles
		 */
		$responsive = '';
		if( !empty( $settings->button_align_responsive) ) {
			$responsive .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton .fl-module-content" => array( 'text-align' => $settings->button_align_responsive ),
			) );
		}

		if( !empty( $settings->button_display_responsive ) ) {
			$responsive .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-bebutton" => array( 'display' => $settings->button_display_responsive ),
			) );
		}

		if( !empty( $responsive ) ) {
			echo "@media (max-width: {$global_settings->responsive_breakpoint}px){{$responsive}}";
		}
	}

	/**
	 * Register the module and its form settings.
	 */
	public function register_module() {
		\FLBuilder::register_module( __CLASS__, array(
			'general'       => array(
				'title'         => __( 'General', 'wpcl_beaver_extender' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'text'          => array(
								'type'          => 'text',
								'label'         => __( 'Text', 'wpcl_beaver_extender' ),
								'default'       => __( 'Click Here', 'wpcl_beaver_extender' ),
								'preview'         => array(
									'type'            => 'text',
									'selector'        => '.be-button-text',
								),
								'connections'         => array( 'string' ),
							),
							'icon'          => array(
								'type'          => 'icon',
								'label'         => __( 'Icon', 'wpcl_beaver_extender' ),
								'show_remove'   => true,
							),
							'icon_position' => array(
								'type'          => 'select',
								'label'         => __( 'Icon Position', 'wpcl_beaver_extender' ),
								'default'       => 'before',
								'options'       => array(
									'before'        => __( 'Before Text', 'wpcl_beaver_extender' ),
									'after'         => __( 'After Text', 'wpcl_beaver_extender' ),
								),
							),
							'icon_animation' => array(
								'type'          => 'select',
								'label'         => __( 'Icon Visibility', 'wpcl_beaver_extender' ),
								'default'       => 'disable',
								'options'       => array(
									'disable'        => __( 'Always Visible', 'wpcl_beaver_extender' ),
									'enable'         => __( 'Fade In On Hover', 'wpcl_beaver_extender' ),
								),
							),
						),
					),
					'link'          => array(
						'title'         => __( 'Link', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'link'          => array(
								'type'          => 'link',
								'label'         => __( 'Link', 'wpcl_beaver_extender' ),
								'placeholder'   => __( 'http://www.example.com', 'wpcl_beaver_extender' ),
								'show_target'	=> true,
								'show_nofollow'	=> true,
								'preview'       => array(
									'type'          => 'none',
								),
								'connections'         => array( 'url' ),
							),
						),
					),
					'atts'          => array(
						'title'         => __( 'Attributes', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'button_class'          => array(
								'type'          => 'text',
								'label'         => __( 'Button Class', 'wpcl_beaver_extender' ),
								'default'       => '',
								'preview'         => array(
									'type'            => 'refresh',
								),
							),
							'button_id'          => array(
								'type'          => 'text',
								'label'         => __( 'Button ID', 'wpcl_beaver_extender' ),
								'default'       => '',
								'preview'         => array(
									'type'            => 'refresh',
								),
							),
						),
					),
				),
			),
			'style' => array(
				'title' => __( 'Style', 'wpcl_beaver_extender' ),
				'sections' => array(
					'background' => array( // Section
						'title' => __( 'Background', 'wpcl_beaver_extender' ),
						'fields' => array(
							'button_style' => array(
								'type' => 'select',
								'label' => __( 'Background Style', 'wpcl_beaver_extender' ),
								'default' => 'flat',
								'options' => array(
									'flat' => __( 'Flat', 'wpcl_beaver_extender' ),
									'gradient' => __( 'Gradient', 'wpcl_beaver_extender' ),
									'transparent' => __( 'Transparent', 'wpcl_beaver_extender' ),
								),
								'toggle' => array(
									'flat' => array(
										'fields' => array( 'button_bg_color', 'button_bg_hover_color' ),
									),
									'transparent' => array(
										'fields' => array( 'button_bg_hover_color' ),
									),
									'gradient' => array(
										'fields' => array( 'button_gradient' ),
									),
								),
							),
							'button_gradient' => array(
								'type'    => 'gradient',
								'label'   => 'Gradient',
								'preview' => array(
									'type'     => 'css',
									'selector' => '.be-button',
									'property' => 'background-image',
								),
							),

							'button_bg_color'      => array(
								'type'          => 'color',
								'label'         => __( 'Background Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_bg_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Background Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
						),
					),
					'structure'        => array( // Section
						'title'         => __( 'Structure', 'wpcl_beaver_extender' ),
						'fields'        => array(
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
									'custom'        => array(
										'fields'        => array( 'button_custom_width' ),
									),
								),
							),
							'button_custom_width'  => array(
								'type'          => 'unit',
								'label'         => __( 'Custom Width', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', '%' ),
								'default_unit' => 'px',
								'default' => '200',
							),
							'button_display'         => array(
								'type'          => 'select',
								'label'         => __( 'Display', 'wpcl_beaver_extender' ),
								'default'       => 'block',
								'responsive'    => true,
								'options'       => array(
									'block'   => __( 'Block', 'wpcl_beaver_extender' ),
									'inline-block' => __( 'Inline', 'wpcl_beaver_extender' ),
								),
							),
							'button_align'         => array(
								'type'          => 'align',
								'label'         => __( 'Alignment', 'wpcl_beaver_extender' ),
								'default'       => 'left',
								'responsive'    => true,
								'values'  => array(
									'left'   => 'left',
									'center' => 'center',
									'right'  => 'right',
								),
							),
							'button_padding'       => array(
								'type'        => 'dimension',
								'label'         => __( 'Padding', 'wpcl_beaver_extender' ),
								'units'	       => array( 'px', 'em', 'rem', '%' ),
								'default_unit' => 'px',
							),
						),
					),
					'border'        => array( // Section
						'title'         => __( 'Border & Shadow', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'button_border' => array(
								'type'       => 'border',
								'label'      => 'Border Options',
								'responsive' => true,
								'show_alpha'    => true,
								'preview'    => array(
									'type'     => 'css',
									'selector' => '.be-button',
								),
							),
							'hover_border_color' => array(
								'type'          => 'color',
								'label'         => __( 'Hover Border Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
						),
					),
					'text'        => array( // Section
						'title'         => __( 'Text', 'wpcl_beaver_extender' ),
						'fields'        => array(
							'typography' => array(
								'type'       => 'typography',
								'label'      => 'Button Fonts',
								'responsive' => true,
								'preview'    => array(
									'type'	    => 'css',
									'selector'  => '.be-button',
								),
							),
							'button_text_color'    => array(
								'type'          => 'color',
								'label'         => __( 'Text Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
								'preview'       => array(
									'type'          => 'refresh',
								),
							),
							'button_text_hover_color' => array(
								'type'          => 'color',
								'label'         => __( 'Text Hover Color', 'wpcl_beaver_extender' ),
								'default'       => '',
								'show_reset'    => true,
								'show_alpha'    => true,
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