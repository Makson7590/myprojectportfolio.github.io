/*	
 * Olark wrapper for jQuery mmenu
 * Include this file after including the jquery.mmenu plugin for default Olark support.
 */


(function( $ ) {

	var _PLUGIN_ = 'mmenu';

	//	Add olark id to th noPageSelector array
	$[ _PLUGIN_ ].configuration.offCanvas.noPageSelector.push( '#olark' );

})( jQuery );