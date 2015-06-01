
/**
 * Callback for the "linkto" attribute field.
 *
 * Display the "Custom Link" field if and only if the "linkto" field is "custom"
 */
function imgShortcakeLinktoListener( changed, collection, shortcode ) {
	var customLinkField = _.find( collection, function( viewModel ) { return 'url' === viewModel.model.get('attr'); } );

	if ( changed.value === 'custom' ) {
		customLinkField.$el.show()
	} else {
		customLinkField.$el.val('').hide();
	}
}

wp.shortcake.hooks.addAction( 'img.linkto', imgShortcakeLinktoListener );
