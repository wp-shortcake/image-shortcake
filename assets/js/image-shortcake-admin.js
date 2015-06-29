
var ImageShortcake = {

	listeners: {
		/**
		 * Callback for the "attachment" attribute field.
		 *
		 * When selecting an attachment for an [img], try to populate image
		 * shortcode attribute fields (alt, caption, description,etc.) from
		 * the attachment data.
		 */
		attachment: function( changed, collection, shortcode ) {
			if ( typeof changed.value === 'undefined' || typeof window.sui.data.idCache[ changed.value ] === 'undefined' ) {
				return;
			}
			var attachment = window.sui.data.idCache[ changed.value ];

			var altField = _.find( collection, function( viewModel ) { return 'alt' === viewModel.model.get('attr'); } );
			var captionField = _.find( collection, function( viewModel ) { return 'caption' === viewModel.model.get('attr'); } );

			if ( ! altField.model.get('value') && attachment.alt ) {
				altField.$el.find('[name="alt"]').val( attachment.alt );
			}

			if ( ! captionField.model.get('value') && attachment.caption ) {
				captionField.$el.find('[name="caption"]').val( attachment.caption );
			}

		},

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

	wp.shortcake.hooks.addAction( 'img.attachment', ImageShortcake.listeners.attachment );
	wp.shortcake.hooks.addAction( 'img.linkto',     ImageShortcake.listeners.linkto     );

}
