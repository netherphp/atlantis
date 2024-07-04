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
		this.data = data.data;
		this.upload = null;

		this.imgPreview = null;
		this.btnUpload = null;
		this.btnChoose = null;
		this.inURL = null;
		this.inCaption = null;
		this.inAltText = null;

		////////

		this.init();

		return;
	};

	init() {

		if(typeof this.data.imageID === 'undefined')
		this.data.imageID = null;

		if(typeof this.data.imageURL === 'undefined')
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

		let self = this;

		////////

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
			.addClass('form-control o-50 w-100')
		);

		this.inCaption = (
			jQuery('<input />')
			.attr('type', 'text')
			.attr('placeholder', 'Caption...')
			.addClass('form-control w-100')
		);

		////////

		this.imgPreview = (
			jQuery('<img />')
			.addClass('d-none')
		);

		////////

		this.upload = new Uploader(this.btnUpload, {
			conf: '/api/file/upload',
			onSuccess: this.onUpload.bind(this)
		});

		if(this.data.imageURL) {
			this.inURL.val(this.data.imageURL);
			this.imgPreview.attr('src', this.data.imageURL);
			this.imgPreview.removeClass('d-none');
		}

		if(this.data.caption) {
			this.inCaption.val(this.data.caption);
		}

		if(this.data.altText) {
			this.inAltText.val(this.data.altText);
		}

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
				.addClass('col-12 mb-2 ta-center')
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
			caption: this.inCaption.val(),
			altText: this.data.altText,
			gallery: this.data.gallery,
			primary: this.data.primary
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onUpload(result) {

		if(result.payload.Type !== 'img') {
			alert('Upload was not an image?');
			return;
		}

		let id = result.payload.ID;
		let url = result.payload.URL.replace('original.', 'lg.');

		this.data.imageID = id;
		this.data.imageURL = url;

		this.imgPreview.attr('src', url);
		this.upload.dialog.destroy();

		return;
	}

};

export default AtlImageChooser;
