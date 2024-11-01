=== WPCL Beaver Extender ===
Contributors: gaiusinvictus
Donate link: https://www.wpcodelabs.com
Tags: beaver builder, beaver builder addon
Requires at least: 4.0.1
Requires PHP: 5.6
Tested up to: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Additional Beaver Builder modules and functionality

== Description ==

Beaver Extender is a free plugin that extends the power of Beaver Builder with several custom drag-and-drop modules, including:

- Google Maps (requires google maps API key)
- Buttons
- Code Blocks (for displaying code snippets)
- Headings
- Icons
- Iframe
- Shortcodes
- Seperators
- Tabs
- Gravity Forms
- Blockquotes

Additionally, it extends the core row and columns to include row/column seperators. It also includes a "Content Block" post type and widget, which can be used to design widget areas, headers, footers, and other resuable blocks within Beaver Builder, and include them in sidebars!

It also extends all core elements (rows/columns) and modules to allow custom css/scss on per-module basis, rapididly speeding up complex styling.

== Installation ==

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Beaver Extender'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `beaver-extender.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `beaver-extender.zip`
2. Extract the `beaver-extender` directory to your computer
3. Upload the `beaver-extender` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Changelog ==

= 1.1.7 =
- Small style update for buttons module

= 1.1.5 =
- Updated gravity forms modules to allow field values
- Moved content blocks out of appearence tabs, so editors can view

= 1.1.3 =
- Updated button styles to prevent accidental override
- Added button specific ID and class, for compatibility with other plugins
- Fixed prism enqueue bug
- Refactored scss, webpack, and gulp

= 1.1.0 =
- Updated modules to use 2.2 unit and diminsion fields
- Removed additional animations, since they are included in the core plugin in v2.2
- Upgraded from grunt to webpack for the build process

= 1.0.0 =
Initial Release