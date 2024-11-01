jQuery( document ).ready(function($) {

	var editors, original_mode_path;

	if( typeof FLBuilder === 'object' && typeof ace === 'object' ) {
		/**
		 * Get the origin mode path so we know what to reset to
		 * @type {[type]}
		 */
		original_mode_path = ace.config.get( 'modePath' );
		/**
		 * Create the editors
		 */
		FLBuilder.addHook( 'settings-form-init', function() {

			editors = jQuery.map( $( '.fl-builder-settings:visible' ).find( '.be-code-editor' ), function( el ) {
				return new BEScssField(el);
			});

		});
	}

	function BEScssField( textarea ) {
		var $textarea, $editdiv, editor;

		var _bindEvents = function() {
			editor.getSession().on( 'change', function( e ) {
				$textarea.val( editor.getSession().getValue() ).trigger( 'change' );
			} );
		};

		var _setOptions = function() {

			editor.setOptions( {
				enableBasicAutocompletion: true,
				enableLiveAutocompletion: true,
				enableSnippets: false,
				showLineNumbers: true,
				showFoldWidgets: false,
				minLines: 1,
				maxLines: 30,
			} );
			// Set the value
			editor.getSession().setValue( $textarea.val() );
			// Set the new path for our mode files
			ace.config.set( 'basePath', be_ui_field_scss.baseurl + 'js/ace/src-min' );
			ace.config.set( 'modePath', be_ui_field_scss.baseurl + 'js/ace/src-min' );
			ace.require( 'ace/ext/language_tools' );
			// Set teh mode
			editor.session.setMode( 'ace/mode/scss' );
			// Reset the path
			ace.config.set( 'basePath', original_mode_path );
			ace.config.set( 'modePath', original_mode_path );
		};

		var _init = function() {
			// Create object from textarea and hide
			$textarea = $( textarea ).hide();
			// Create and insert div for editor to live
			$editdiv  = $( '<div>', { id : $textarea.attr( 'id' ) + '-editor' } ).insertAfter( $textarea );
			// Create editor
			editor = ace.edit( $editdiv.attr( 'id' ) );
			// Set options
			_setOptions();
			// Bind events
			_bindEvents();
			// Set the parent for resize
			$textarea.closest( '.fl-field' ).data( 'editor', editor );
		};
		_init();
	}
});