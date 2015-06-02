
var ImageShortcake = {

	listeners: {
		/**
		 * Callback for the "linkto" attribute field.
		 *
		 * Display the "Custom Link" field if and only if the "linkto" field is "custom"
		 */
		linkto: function( changed, collection, shortcode ) {
			var customLinkField = _.find( collection, function( viewModel ) { return 'url' === viewModel.model.get('attr'); } );

			if ( changed.value === 'custom' ) {
				customLinkField.$el.show()
			} else {
				customLinkField.$el.val('').hide();
			}
		}
	}

}

/**
 * If using a recent enough version of Shortcake (0.4.0 and up),
 * attach these listeners to the attributes.
 *
 */
if ( typeof wp.shortcake !== 'undefined' && typeof wp.shortcake.hooks !== 'undefined' ) {

	wp.shortcake.hooks.addAction( 'img.linkto', ImageShortcake.listeners.linkto );

}
