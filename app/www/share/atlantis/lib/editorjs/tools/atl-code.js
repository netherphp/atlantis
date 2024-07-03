
class AtlCode {
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
			title: 'Code...',
			icon: '<i class="mdi mdi-code-braces"></i>'
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(data) {

		this.element = null;
		this.data = data.data;

		this.inLang = null;
		this.inCode = null;

		////////

		this.init();

		return;
	};

	init() {

		if(typeof this.data.lang === 'undefined')
		this.data.lang = null;

		if(typeof this.data.code === 'undefined')
		this.data.code = null;

		return;
	};

	render() {

		let self = this;

		////////

		this.inLang = (
			jQuery('<select />')
			.attr('type', 'text')
			.attr('readonly', 'readonly')
			.addClass('form-select')
			.append(`<option value="text">Plain Text</option>`)
			.append(`<option value="php">PHP</option>`)
		);

		this.inCode = (
			jQuery('<textarea />')
			.attr('placeholder', 'Code...')
			.attr('rows', 12)
			.attr('spellcheck', 'false')
			.addClass('form-control ff-mono')
			.on('keydown', function(ev){
				ev.stopPropagation();
				return;
			})
		);

		////////

		if(this.data.lang) {
			this.inLang.val(this.data.lang);
		}

		if(this.data.code) {
			this.inCode.text(this.data.code);
		}

		this.element = (
			jQuery('<div />')
			.addClass('pt-2')
			.addClass('atl-editorjs-codeinput')
			.addClass('row tight justify-content-center')
			.append(
				jQuery('<div />')
				.addClass('col-12 mb-2')
				.append(this.inLang)
			)
			.append(
				jQuery('<div />')
				.addClass('col-12 mb-0')
				.append(this.inCode)
			)
		);

		this.bootAceEditor();

		return this.element.get()[0];
	};

	save(data) {

		return {
			lang: this.inLang.val(),
			code: this.inCode.val(),
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	bootAceEditor() {

		return;
	};

};

export default AtlCode;
