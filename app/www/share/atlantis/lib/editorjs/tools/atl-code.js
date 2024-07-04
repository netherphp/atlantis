import API from '/share/nui/api/json.js';

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

		this.inLang = (
			jQuery('<select />')
			.attr('type', 'text')
			.attr('readonly', 'readonly')
			.addClass('form-select')
		);

		this.inTheme = (
			jQuery('<select />')
			.attr('type', 'text')
			.attr('readonly', 'readonly')
			.addClass('form-select')
		);

		this.inCode = (
			jQuery('<textarea />')
			.attr('placeholder', 'Code...')
			.attr('rows', 12)
			.attr('spellcheck', 'false')
			.addClass('form-control ff-mono')
			.on('keydown', function(ev) {
				if(ev.originalEvent.key == 'Tab') {
					ev.preventDefault();

					this.setRangeText(
						'\t',
						(this.selectionStart),
						(this.selectionEnd),
						'end'
					);
				}

				this.focus();
				ev.stopPropagation();

				return;
			})
		);

		////////

		this.populateLang();
		this.populateCode();

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

	async populateCode() {

		if(this.data.code) {
			this.inCode.text(this.data.code);
		}

		return;
	};

	async populateLang() {

		let url = '/share/atlantis/lib/ace/ace.json';
		let output = await fetch(url);
		let data = await output.json();

		for(const item in data.Modes)
		this.inLang.append(
			jQuery('<option />')
			.attr('value', item)
			.text(data.Modes[item])
		);

		if(this.data.lang) {
			this.inLang.val(this.data.lang);
		}

		return;
	};

};

export default AtlCode;
