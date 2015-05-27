
/**
 * Callback for the "linkto" attribute field.
 *
 * Display the "Custom Link" field if and only if the "linkto" field is "custom"
 */
function imgShortcakeLinktoListener( changed, collection ) {
	var customLinkField = _.find( this, function( viewModel ) { return 'url' === viewModel.model.get('attr'); } );

	if ( changed.value === 'custom' ) {
		customLinkField.$el.show()
	} else {
		customLinkField.$el.val('').hide();
	}
}
