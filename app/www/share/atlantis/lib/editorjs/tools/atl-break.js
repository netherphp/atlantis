class AtlBreak {
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
			title: 'HR Break.',
			icon: '<i class="mdi mdi-format-page-break"></i>'
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(data) {

		this.element = null;
		this.data = data.data;

		if(typeof this.data.mode === 'undefined')
		this.data.mode = 'line';

		return;
	};

	render() {

		this.element = jQuery('<hr class="atl-editorjs-hr" />');
		this.btnLine = null;
		this.btnEmpty = null;

		this.element.addClass(this.data.mode);

		return this.element.get()[0];
	};

	renderSettings() {

		this.settings = jQuery('<div />');

		(this.settings)
		.append(
			this.btnLine = jQuery('<div />')
			.addClass('ce-popover-item')
			.addClass(this.data.mode === 'line' ? 'ce-popover-item--active' : '')
			.append(
				jQuery('<div />')
				.addClass('ce-popover-item__icon')
				.html('<i class="mdi mdi-minus"></i>')
			)
			.append(
				jQuery('<div />')
				.addClass('ce-popover-item__title')
				.text('Line Break')
			)
			.on('click', this.onSetLineBreak.bind(this))
		)
		.append(
			this.btnEmpty = jQuery('<div />')
			.addClass('ce-popover-item')
			.addClass(this.data.mode === 'empty' ? 'ce-popover-item--active' : '')
			.append(
				jQuery('<div />')
				.addClass('ce-popover-item__icon')
				.html('<i class="mdi mdi-drag-horizontal"></i>')
			)
			.append(
				jQuery('<div />')
				.addClass('ce-popover-item__title')
				.text('Space Break')
			)
			.on('click', this.onSetSpaceBreak.bind(this))
		);

		return this.settings.get()[0];
	};

	save(data) {

		return {
			mode: this.data.mode
		};
	};

	onSetLineBreak() {

		this.data.mode = 'line';

		(this.element)
		.addClass('line')
		.removeClass('empty');

		this.btnLine.addClass('ce-popover-item--active');
		this.btnEmpty.removeClass('ce-popover-item--active');

		return;
	};

	onSetSpaceBreak() {

		this.data.mode = 'empty';

		this.element
		.addClass('empty')
		.removeClass('line');

		this.btnEmpty.addClass('ce-popover-item--active');
		this.btnLine.removeClass('ce-popover-item--active');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

export default AtlBreak;