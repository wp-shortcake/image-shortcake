/*
 * Callback to manipulate the attachment details and two column template, onReady.
 */
function updateStringsForImageShortcake() {
	var attachmentDetailsTemplate = jQuery("#tmpl-attachment-details");

	if ( 0 === attachmentDetailsTemplate.length ) {
		return;
	}

	var attachmentDetailsHtml = attachmentDetailsTemplate.html(),
		attachmentDetailsTwoColumnTemplate = jQuery("#tmpl-attachment-details-two-column"),
		attachmentDetailsTwoColumnHtml = attachmentDetailsTwoColumnTemplate.html(),

		customCaption = '<# if( "image" === data.type ) { #>' +
						'<span class="name">' +
						image_shortcake_strings.caption +
						'</span>' +
						'<# } #>' +
						'<textarea {{ maybeReadOnly }}>{{ data.caption }}</textarea>' +
						'<div class="image-shortcake-warning">' +
						image_shortcake_strings.warning +
						'.</div>' +
						'<style scoped>.image-shortcake-warning { display: block;float: right;width: 65%;margin-bottom: 5px;font-style: italic; }@media (max-width: 900px) { .image-shortcake-warning { width: 100%;} } </style>';

	/**
	 * Use string methods hack.
	 */
		var newHtml = attachmentDetailsHtml.replace(/(<label class="setting" data-setting="caption">)[\w\W]*?(<\/label>)/,"$1 " + customCaption + " $2");
		newHtml = newHtml.replace(/(<label class="setting" data-setting="description">[\w\W]*?<\/label>)/,"<# if( 'image' === data.type ) { #>$1<# } #>");
		attachmentDetailsTemplate.text( newHtml );

		newHtml = attachmentDetailsTwoColumnHtml.replace(/(<label class="setting" data-setting="caption">)[\w\W]*?(<\/label>)/,"$1 " + customCaption + " $2");
		newHtml = newHtml.replace(/(<label class="setting" data-setting="description">[\w\W]*?<\/label>)/,"<# if( 'image' === data.type ) { #>$1<# } #>");
		attachmentDetailsTwoColumnTemplate.text( newHtml );
};


function overloadMediaSendToEditor() {

	if ( 'undefined' === typeof wp ||
		 'undefined' === typeof wp.media ||
		 'undefined' === typeof wp.media.editor ||
		 'undefined' === typeof wp.media.editor.send ||
		 'undefined' === typeof wp.media.editor.send.attachment ) {
		return;
	}

	// Back up the default function
	var defaultSend = wp.media.editor.send.attachment;

	// Replace it with this new send function
	wp.media.editor.send.attachment = function( props, attachment ) {

		// If not image, return default function
		if ( 'image' !== attachment.type ) {
			return defaultSend( props, attachment);
		}

		// Back up old wp.media.post and redefine it so as to modify the
		// attachment data before sending it.
		var oldMediaPost = wp.media.post;

		wp.media.post = function( endpoint, postData ) {
			wp.media.post = oldMediaPost;

			postData.attachment.linkto = props.link;

			if ( props.linkUrl && 'custom' === props.link ) {
				postData.attachment.url = props.linkUrl;
			}

			return wp.media.post( endpoint, postData );
		};

		return defaultSend( props, attachment );
	};
};

jQuery(document).ready(function(){
	updateStringsForImageShortcake();
	overloadMediaSendToEditor();
});

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
			if ( typeof changed.value === 'undefined' ) {
				return;
			}

			var attachment = sui.views.editAttributeFieldAttachment.getFromCache( changed.value );

			if ( attachment ) {

				var attrView = sui.views.editAttributeField,
					altField = attrView.getField( collection, 'alt' ),
					captionField = attrView.getField( collection, 'caption' );

				if ( ! altField.getValue() && attachment.alt ) {
					altField.$el.find('[name="alt"]').val( attachment.alt );
				}

				if ( ! captionField.getValue() && attachment.caption ) {
					captionField.$el.find('[name="caption"]').val( attachment.caption );
				}
			}
		},

		/**
		 * Callback for the "linkto" attribute field.
		 *
		 * Display the "Custom Link" field if and only if the "linkto" field is "custom"
		 */
		linkto: function( changed, collection, shortcode ) {
			var customLinkField = sui.views.editAttributeField.getField( collection, 'url' );

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

