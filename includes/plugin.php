<?php

/**
 * The main plugin file definition
 * This file isn't instatiated directly, it acts as a shared parent for other classes
 * @link    https://www.wpcodelabs.com
 * @since   1.0.0
 * @package wpcl_beaver_extender
 */

namespace Wpcl\Be;

class Plugin {

	/**
	 * Plugin Name
	 * @since 1.0.0
	 * @access protected
	 * @var (string) $name : The unique identifier for this plugin
	 */
	public static $name = 'wpcl_beaver_extender';

	/**
	 * Plugin Version
	 * @since 1.0.0
	 * @access protected
	 * @var (string) $version : The version number of the plugin, used to version scripts / styles
	 */
	public static $version = '1.1.7';

	/**
	 * Plugin Options
	 * @since 1.0.0
	 * @access protected
	 * @var (array) $settings : The array that holds plugin options
	 */
	protected $loader;

	/**
	 * Instances
	 * @since 1.0.0
	 * @access protected
	 * @var (array) $instances : Collection of instantiated classes
	 */
	protected static $instances = array();

	/**
	 * Registers our plugin with WordPress.
	 */
	public static function register( $class_name = null ) {
		// Get called class
		$class_name = !is_null( $class_name ) ? $class_name : get_called_class();
		// Instantiate class
		$class = $class_name::get_instance( $class_name );
		// Create API manager
		$class->loader = \Wpcl\Be\Loader::get_instance();
		// Register stuff
		$class->loader->register( $class );
		// Return instance
		return $class;
	}

	/**
	 * Gets an instance of our class.
	 */
	public static function get_instance( $class_name = null ) {
		// Use late static binding to get called class
		$class = !is_null( $class_name ) ? $class_name : get_called_class();
		// Get instance of class
		if( !isset(self::$instances[$class] ) ) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}

	/**
	 * Constructor
	 * @since 1.0.0
	 * @access protected
	 */
	protected function __construct() {
		// Nothing to do here at this time
	}

	/**
	 * Helper function to use relative URLs
	 * @since 1.0.0
	 * @access protected
	 */
	public static function url( $url = '' ) {
		return plugin_dir_url( BEAVER_EXTENDER_ROOT ) . ltrim( $url, '/' );
	}

	/**
	 * Helper function to use relative paths
	 * @since 1.0.0
	 * @access protected
	 */
	public static function path( $path = '' ) {
		return plugin_dir_path( BEAVER_EXTENDER_ROOT ) . ltrim( $path, '/' );
	}

	public function burn_baby_burn() {
		// If FLBuilder is less than minimum, register notice
		if( !defined( 'FL_BUILDER_VERSION' ) || version_compare( '2.2', FL_BUILDER_VERSION, '>=' ) ) {
			add_action( 'admin_notices', function() {
				$class = 'notice notice-error';
				$message = __( 'WPCL Beaver Extender Requires Beaver Builder Version 2.2 or Higher', 'wpcl_beaver_extender' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			});
			return;
		}

		/**
		 * Universal Classes
		 * @var array
		 */
		$classes = array(
			'Admin',
			'ContentBlock',
			'Widgets',
			'Frontend',
		);

		/**
		 * FL Builder Extensions
		 * @var array
		 */
		$extensions = array(
			'Animations',
			'Presets',
			'Scss',
			'Seperators',
			'Spacing',
		);

		/**
		 * Register core plugin classes
		 */
		foreach( $classes as $class ) {
			// Append namespace
			$class = '\\Wpcl\\Be\\Classes\\' . $class;
			// Register
			$class::register();
		}
		/**
		 * Register flbuilder extensions
		 */
		foreach( $extensions as $extension ) {
			// Append namespace
			$extension = '\\Wpcl\\Be\\Extensions\\' . $extension;
			// Register
			$extension::register();
		}
		/**
		 * Add a registration hook for beaver builder modules
		 *
		 * We need to delay registration until after we know all other
		 * plugins are fully loaded, in order to prevent errors
		 */
		add_action( 'init', array( $this, 'register_modules' ) );

	}

	public function register_modules() {

		// We can bail if beaver builder isn't present
		if( !class_exists( 'FLBuilder' ) ) {
			return;
		}

		$modules = array(

			'BEBlockQuote',
			'BEButton',
			'BECodeblock',
			'BEGmaps',
			'BEGravityForms',
			'BEHeading',
			'BEHorizontalRule',
			'BEIcon',
			'BEIconGroup',
			'BEIframe',
			'BEMenu',
			'BESimpleMenu',
			'BEShortCode',
			'BETabs',

		);

		foreach( $modules as $slug ) {
			/**
			 * Append namespace
			 */
			$class = "\\Wpcl\\Be\\Modules\\{$slug}";
			/**
			 * Create the module
			 */
			$modules[$slug] = new $class();
			/**
			 * Register the module
			 */
			$modules[$slug]->register_module();
		}

	}

} // end class