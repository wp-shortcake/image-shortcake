
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

wp.shortcake.hooks.addAction( 'img.linkto', ImageShortcake.listeners.linkto );
