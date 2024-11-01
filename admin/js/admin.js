jQuery( document ).ready(function( $ ) {

	var typeSelect = $('#type-select');

	showOptions();

	typeSelect.on('change', function(){
		showOptions();
	});

	function showOptions( ){
		if( typeSelect.length > 0 ){
			var val = typeSelect.val();
			$('.type-options').hide();
			$('#type-options-'+val).show();
		}
	}


	if ( typeof $.datepicker !== 'undefined' ){
		$('input.datepicker').datepicker();
	}


	if ( jQuery.fn.select2 ){
		$('select.select2').select2();
	}


	$('.set_media_image').click(function(e) {
		e.preventDefault();

		var button = $(this);
		var wrapper = button.closest('.media-wrapper');
		var input = wrapper.find('input');
		var imgWrapper = wrapper.find('.image-preview-wrapper');
		var img = imgWrapper.find('img');
		var fileNameEl = wrapper.find('.file-name');
		var buttonRemove = wrapper.find('.remove_media_image');

		var mimetype = input.attr('data-mimetype');

		var mediaUploader = wp.media.frames.file_frame = wp.media({
			multiple: false,
			library: {
				type: mimetype,
			},
		});

		mediaUploader.on('select', function() {
			var attachment = mediaUploader.state().get('selection').first().toJSON();
			input.val(attachment.id);
			if( attachment.sizes ){
				var src = attachment.sizes.thumbnail.url;
			}
			else {
				var src = attachment.icon;
			}

			if( img.length > 0 ){
				img.attr('src', src);
			}
			else {
				imgWrapper.html('<img src="' + src + '" />');
			}
			var filename = attachment.filename;
			fileNameEl.text(filename);
			buttonRemove.show();
		});

		mediaUploader.open();
	});

	$('.remove_media_image').click(function(e) {
		e.preventDefault();

		var button = $(this);
		var wrapper = button.closest('.media-wrapper');
		var input = wrapper.find('input');
		var img = wrapper.find('.image-preview-wrapper img');
		var fileNameEl = wrapper.find('.file-name');

		input.val('');
		img.remove();
		fileNameEl.text('');
		button.hide();
	});


	var exportType = $('#export_type');
	if( exportType.length ) {
		var exportLanguages = $('#export_languages_wrapper');

		function changeExportLanguagesVisibility(){
			if( exportType.val() == 'sv' ){
				exportLanguages.show();
			}
			else {
				exportLanguages.hide();
			}
		}

		exportType.change(function(){
			changeExportLanguagesVisibility();
		});

		changeExportLanguagesVisibility();
	}

});