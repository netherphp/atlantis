
class ToggleButton {

	constructor(id) {

		this.selector = id;
		this.element = jQuery(id);
		this.input = null;

		this.classStateOn = 'atl-togglebtn-on';
		this.classStateOff = 'atl-togglebtn-off';
		this.classStateStyleOn = this.element.attr('data-cstyle-on') || 'btn-primary';
		this.classStateStyleOff = this.element.attr('data-cstyle-off') || 'btn-secondary';

		this.inputName = this.element.attr('data-input') || null;

		if(this.inputName !== null) {
			this.input = jQuery(`input[name="${this.inputName}"]`);

			if(this.input.length === 0)
			this.input = null;
		}

		(this.element)
		.on('click', this.onClick.bind(this));

		this.setStartState();
		this.onReady();

		return;
	};

	setStartState() {

		if(this.element.hasClass(this.classStateOn))
		this.element.addClass(this.classStateStyleOn);

		if(this.element.hasClass(this.classStateOff))
		this.element.addClass(this.classStateStyleOff);

		this.element.removeClass('atl-togglebtn-loading');

		return;
	};

	onClick() {

		(this.element)
		.toggleClass(this.classStateStyleOff)
		.toggleClass(this.classStateStyleOn);

		////////

		let state = this.element.hasClass(this.classStateStyleOn);

		////////

		if(this.input !== null)
		this.input.val(state ? 1 : 0);

		////////

		if(state) {
			(this.element)
			.addClass('atl-togglebtn-on')
			.removeClass('atl-togglebtn-off');
		}

		else {
			(this.element)
			.addClass('atl-togglebtn-off')
			.removeClass('atl-togglebtn-on');
		}

		return;
	};

	onReady() {

		let value = this.element.attr('data-value') || 0;

		////////

		value = parseInt(value);

		if(value === 1) {
			if(!this.element.hasClass(this.classStateStyleOn))
			this.onClick();
		}

		return;
	};

};

export default ToggleButton;
