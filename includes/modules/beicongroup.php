<?php

namespace Wpcl\Be\Modules;

use \Wpcl\Be\Classes\Utilities as Utilities;

use \Wpcl\Be\Plugin as Plugin;

class BEIconGroup extends \FLBuilderModule implements \Wpcl\Be\Interfaces\Action_Hook_Subscriber, \Wpcl\Be\Interfaces\Filter_Hook_Subscriber {

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
			'name'          	=> __( 'Icon Group', 'wpcl_beaver_extender' ),
			'description'   	=> __( 'Display a group of linked Font Awesome icons.', 'wpcl_beaver_extender' ),
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
			array( "be_markup_attr_be-icon-group" => array( 'icon_group_atts', 10, 3 ) ),
		);
	}

	public function icon_group_atts( $atts, $context, $args ) {
		if( $args['instance'] !== $this ) {
			return $atts;
		}

		if( $this->settings->group_type !== false && !empty( $this->settings->group_type ) ) {
			$atts['class'] .= sprintf( ' group-%s', $this->settings->group_type );
		}

		if( $this->settings->group_alignment !== false && !empty( $this->settings->group_alignment ) ) {
			$atts['class'] .= sprintf( ' group-align-%s', $this->settings->group_alignment );
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

		Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-icon-group", 'instance' => $module ) );

			// Render individual icons
			foreach ( $settings->icons as $icon ) {

				if ( ! is_object( $icon ) ) {
					continue;
				}

				Utilities::markup( array( 'open' => '<div %s>', 'context' => "be-icon-group-item", 'instance' => $module ) );

				$icon_settings = array(
					'icon'           => $icon->icon,
					'link'           => $icon->link,
					'link_target'    => isset( $icon->link_target ) ? $icon->link_target : '_blank',
					'text'           => $icon->text,
					'icon_alignment' => !empty( $icon->icon_alignment ) ? $icon->icon_alignment : $settings->icon_alignment,
					'text_placement' => !empty( $icon->text_placement ) ? $icon->text_placement : $settings->text_placement,
				);

				\FLBuilder::render_module_html( 'beicon', $icon_settings );

				Utilities::markup( array( 'close' => '</div>', 'context' => "be-icon-group-item", 'instance' => $module ) );
			}

		Utilities::markup( array( 'close' => '</div>', 'context' => "be-icon-group", 'instance' => $module ) );
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

		$group_rules = \Wpcl\Be\Modules\BEIcon::get_css( $settings );

		foreach( array( 'top', 'right', 'bottom', 'left' ) as $pos ) {
			if( !empty( $settings->{"item_margin_{$pos}"} ) ) {
				$group_rules['item_margin']['desktop']["margin-{$pos}"] = $settings->{"item_margin_{$pos}"} . $settings->item_margin_unit;
			}
			if( !empty( $settings->{"item_margin_{$pos}_medium"} ) ) {
				$group_rules['item_margin']['medium']["margin-{$pos}"] = $settings->{"item_margin_{$pos}_medium"} . $settings->item_margin_medium_unit;
			}
			if( !empty( $settings->{"item_margin_{$pos}_responsive"} ) ) {
				$group_rules['item_margin']['responsive']["margin-{$pos}"] = $settings->{"item_margin_{$pos}_responsive"} . $settings->item_margin_responsive_unit;
			}
		}

		/**
		 * Do border / radius
		 */
		\FLBuilderCSS::border_field_rule( array(
			'settings' 	=> $settings,
			'setting_name' 	=> 'border_radius',
			'selector' 	=> ".fl-node-{$id} .icon-container",
		) );

		$css = '';


		if( array_filter( $group_rules['base'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:before,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon:before"  => $group_rules['base'],
			) );
		}
		if( array_filter( $group_rules['hover'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon:hover,
				 .fl-node-{$id}.fl-module-beicongroup a:hover .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:focus,
				 .fl-node-{$id}.fl-module-beicongroup a:focus .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:hover:before,
				 .fl-node-{$id}.fl-module-beicongroup a:hover .be-icon:before,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:focus:before,
				 .fl-node-{$id}.fl-module-beicongroup a:focus .be-icon:before"  => $group_rules['hover'],
			) );
		}

		if( isset( $group_rules['margins']['desktop'] ) && !empty( $group_rules['margins']['desktop'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-container" => $group_rules['margins']['desktop'],
			) );
		}

		$global_settings  = \FLBuilderModel::get_global_settings();

		/**
		 * Medium output
		 */
		if( array_filter( $group_rules['medium'] ) ) {
			$medium = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:before,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon:before"  => $group_rules['medium'],
			) );

			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$medium}}";
		}
		if( isset( $group_rules['margins']['medium'] ) && !empty( $group_rules['margins']['medium'] ) ) {
			$margin_medium = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-container" => $group_rules['margins']['medium'],
			) );
			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$margin_medium}}";
		}

		/**
		 * Responsive output
		 */
		if( array_filter( $group_rules['responsive'] ) ) {
			$responsive = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup .be-icon:before,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon,
				 .fl-node-{$id}.fl-module-beicongroup a .be-icon:before"  => $group_rules['responsive'],
			) );

			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$responsive}}";
		}
		if( isset( $group_rules['margins']['responsive'] ) && !empty( $group_rules['margins']['responsive'] ) ) {
			$margin_responsive = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-container" => $group_rules['margins']['responsive'],
			) );
			$css .= "@media (max-width: {$global_settings->responsive_breakpoint}px){{$margin_responsive}}";
		}

		/**
		 * Item margins
		 */
		if( isset( $group_rules['item_margin']['desktop'] ) && !empty( $group_rules['item_margin']['desktop'] ) ) {
			$css .= Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item" => $group_rules['item_margin']['desktop'],
			) );
		}
		if( isset( $group_rules['item_margin']['medium'] ) && !empty( $group_rules['item_margin']['medium'] ) ) {
			$item_margin_medium = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item" => $group_rules['item_margin']['medium'],
			) );
			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$item_margin_medium}}";
		}
		if( isset( $group_rules['item_margin']['responsive'] ) && !empty( $group_rules['item_margin']['responsive'] ) ) {
			$item_margin_responsive = Utilities::be_css( array(
				".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item" => $group_rules['item_margin']['responsive'],
			) );
			$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$item_margin_responsive}}";
		}

		// echo $css;

		// /**
		//  * Generate CSS for individual icons
		//  * @var [type]
		//  */
		foreach( $settings->icons as $index => $icon ) {

			$rules = \Wpcl\Be\Modules\BEIcon::get_css( $icon );

			$count = $index + 1;

			/**
			 * Do border / radius
			 */
			\FLBuilderCSS::border_field_rule( array(
				'settings' 	=> $settings,
				'setting_name' 	=> 'border_radius',
				'selector' 	=> ".fl-node-{$id} .be-icon-group-item:nth-of-type({$count}) .icon-container",
			) );

			if( array_filter( $rules['base'] ) ) {
				$css .= Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon:before"  => $rules['base']
				) );
			}
			if( array_filter( $rules['hover'] ) ) {
				$css .= Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:hover,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a:hover .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:focus,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a:focus .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:hover:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a:hover .be-icon:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:focus:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a:focus .be-icon:before"  => $rules['hover'],
				) );
			}

			if( isset( $rules['margins']['desktop'] ) && !empty( $rules['margins']['desktop'] ) ) {
				$css .= Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon-container" => $rules['margins']['desktop'],
				) );
			}

			/**
			 * Medium output
			 */
			if( array_filter( $rules['medium'] ) ) {
				$medium = Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon:before"  => $rules['medium'],
				) );

				$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$medium}}";
			}
			if( isset( $rules['margins']['medium'] ) && !empty( $rules['margins']['medium'] ) ) {
				$margin_medium .= Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon-container" => $rules['margins']['medium'],
				) );
				$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$margin_medium}}";
			}

			/**
			 * Responsive output
			 */
			if( array_filter( $rules['responsive'] ) ) {
				$responsive = Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon:before,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon,
					 .fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) a .be-icon:before"  => $rules['responsive'],
				) );

				$css .= "@media (max-width: {$global_settings->medium_breakpoint}px){{$responsive}}";
			}
			if( isset( $rules['margins']['responsive'] ) && !empty( $rules['margins']['responsive'] ) ) {
				$margin_responsive .= Utilities::be_css( array(
					".fl-node-{$id}.fl-module-beicongroup .be-icon-group-item:nth-of-type({$count}) .be-icon-container" => $rules['margins']['responsive'],
				) );
				$css .= "@media (max-width: {$global_settings->responsive_breakpoint}px){{$margin_responsive}}";
			}

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
		/**
		 * Register the module and its form settings.
		 */
		\FLBuilder::register_module( __CLASS__, array(
			'icons'         => array(
				'title'         => __( 'Icons', 'fl-builder' ),
				'sections'      => array(
					'general'       => array(
						'title'         => '',
						'fields'        => array(
							'icons'         => array(
								'type'          => 'form',
								'label'         => __( 'Icon', 'fl-builder' ),
								'form'          => 'beicongroup_form', // ID from registered form below
								'preview_text'  => 'icon', // Name of a field to use for the preview text
								'multiple'      => true,
							),
							'group_type'         => array(
								'type'          => 'select',
								'label'         => __( 'Group Type', 'fl-builder' ),
								'default'       => 'horizontal',
								'options'       => array(
									'horizontal'        => __( 'Horizontal', 'fl-builder' ),
									'vertical'          => __( 'Vertical', 'fl-builder' ),
								),
								'preview'       => array(
									'type'          => 'refresh',
								),
								'toggle'        => array(
								    'horizontal'      => array(
								        'fields'        => array( 'group_alignment' )
								    ),
								)
							),
							'group_alignment'         => array(
								'type'          => 'select',
								'label'         => __( 'Group Alignment', 'fl-builder' ),
								'default'       => 'horizontal',
								'options'       => array(
									'center'        => __( 'Center', 'fl-builder' ),
									'left'          => __( 'Left', 'fl-builder' ),
									'right'         => __( 'Right', 'fl-builder' ),
								),
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
							'item_margin' => array(
								'type'        => 'dimension',
								'label'       => 'Item Margin',
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

		/**
		 * Register a settings form to use in the "form" field type above.
		 */
		\FLBuilder::register_settings_form( 'beicongroup_form' , array(
			'title' => __( 'Add Icon', 'fl-builder' ),
			'tabs'  => array(
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
											'type'          => 'none',
										),
										'connections'   => array( 'url' ),
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
										'preview'		=> array(
											'type'			=> 'text',
											'selector'		=> '.be-icon-text',
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
			),
		));
	}
}