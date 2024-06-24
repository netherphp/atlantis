import Uploader from '/share/nui/modules/uploader/uploader.js';

class AtlImageChooser {
/*//
@date 2020-10-11
provides a custom block plugin for editor.js for writing blocks of code into
a piece of content using codemirror as the code syntax magic thing.
//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static get isInline() {

		return false;
	};

	static get toolbox() {

		return {
			title: 'Image...',
			icon: '<i class="mdi mdi-image"></i>'
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(data) {

		this.element = null;
		this.data = data;

		this.btnUpload = null;
		this.btnChoose = null;
		this.inURL = null;
		this.inCaption = null;
		this.inAltText = null;

		////////

		if(typeof this.data.id === 'undefined')
		this.data.imageID = null;

		if(typeof this.data.url === 'undefined')
		this.data.imageURL = null;

		if(typeof this.data.caption === 'undefined')
		this.data.caption = '';

		if(typeof this.data.altText === 'undefined')
		this.data.altText = '';

		if(typeof this.data.primary === 'undefined')
		this.data.primary = false;

		if(typeof this.data.gallery === 'undefined')
		this.data.gallery = false;

		return;
	};

	render() {

		this.btnUpload = (
			jQuery('<button />')
			.attr('type', 'button')
			.addClass('btn btn-primary btn-block')
			.html('<i class="mdi mdi-cloud-upload"></i> Upload')
		);

		this.btnChoose = (
			jQuery('<button />')
			.attr('type', 'button')
			.addClass('btn btn-secondary btn-block')
			.html('<i class="mdi mdi-view-gallery"></i> Library')
		);

		this.inURL = (
			jQuery('<input />')
			.attr('type', 'text')
			.attr('readonly', 'readonly')
			.addClass('form-control w-100')
		);

		this.inCaption = (
			jQuery('<input />')
			.attr('type', 'text')
			.attr('placeholder', 'Caption...')
			.addClass('form-control w-100')
		);

		this.imgPreview = (
			jQuery('<div />')
			.addClass("ratiobox ultrawide wallpapered rounded")
		);

		this.element = (
			jQuery('<div />')
			.addClass('atl-editorjs-imgupl')
			.addClass('row tight justify-content-center')
			.append(
				jQuery('<div />')
				.addClass('col-auto')
				.append(this.btnUpload)
			)
			.append(
				jQuery('<div />')
				.addClass('col-auto')
				.append(this.btnChoose)
			)
			.append(
				jQuery('<div />')
				.addClass('col')
				.append(this.inURL)
			)
			.append(
				jQuery('<div />')
				.addClass('col-12 mb-2')
			)
			.append(
				jQuery('<div />')
				.addClass('col-12 mb-2')
				.append(this.imgPreview)
			)
			.append(
				jQuery('<div />')
				.addClass('col-12 mb-0')
				.append(this.inCaption)
			)
		);

		return this.element.get()[0];
	};

	save(data) {

		return {
			imageID: this.data.imageID,
			imageURL: this.data.imageURL,
			caption: this.data.caption,
			altText: this.data.altText,
			gallery: this.data.gallery,
			primary: this.data.primary
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

export default AtlImageChooser;
