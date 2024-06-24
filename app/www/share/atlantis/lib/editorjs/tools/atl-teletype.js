class AtlTeletype {
/*//
@date 2020-10-11
provides a custom block plugin for editor.js for writing blocks of code into
a piece of content using codemirror as the code syntax magic thing.
//*/

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static get isInline() {
		return true;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor() {

		return;
	};

	render() {

		this.btn = jQuery('<button />');
		this.btn.addClass('ce-inline-tool');
		this.btn.attr('type', 'button');
		this.btn.html('<i class="mdi mdi-format-page-break"></i>');

		return this.btn.get()[0];
	};

	surround(range) {

		return;
	};

	checkState(selection) {

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

export default AtlTeletype;
