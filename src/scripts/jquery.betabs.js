( function($) {

	$.fn.BETabs = function() {
	    return this.each( function( index, el ) {
	        return new BETab( el );
	    });

	    function BETab( el ) {
	    	var $el, $outer_tabs, $inner_tabs, $panels;

	    	var _init = function() {
	    		$el = $( el );

	    		$outer_tabs = $.map( $el.find( '.be-tabs-labels .be-tabs-label' ), function( tab, index ) {
	    			return $( tab ).on( 'click', 'a', { index : index }, _doTab );
	    		});

	    		$inner_tabs = $.map( $el.find( '.be-tabs-panels .be-tabs-label' ), function( tab, index ) {
	    			return $( tab ).on( 'click', 'a', { index : index }, _doTab );
	    		});

	    		$panels = $.map( $el.find( '.be-tabs-panel' ), function( panel ) {
	    			return $( panel );
	    		});

	    	};

	    	var _doTab = function( event ) {
	    		event.preventDefault();

	    		for( var i in $panels ) {
	    			if( i == event.data.index ) {
	    				$panels[i].addClass( 'active' );
	    				$outer_tabs[i].addClass( 'active' );
	    				$inner_tabs[i].addClass( 'active' );
	    			} else {
	    				$panels[i].removeClass( 'active' );
	    				$outer_tabs[i].removeClass( 'active' );
	    				$inner_tabs[i].removeClass( 'active' );
	    			}
	    		}
	    	};

	    	_init();
	    }

	};

})(jQuery);