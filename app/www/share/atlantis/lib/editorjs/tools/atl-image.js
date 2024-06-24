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
				.addClass('col-12 mb-4')
			)
			.append(
				jQuery('<div />')
				.addClass('col-12')
				.append(this.imgPreview)
			)
		);

		return this.element.get()[0];
	};

	save(data) {

		return {

		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

export default AtlImageChooser;
