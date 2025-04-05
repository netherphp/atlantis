/*//

<button class="atl-togglebtn btn">
	<input type="hidden" value="1" />
	<i class="atl-togglebtn-icon-loading mdi mdi-dots-horizontal"></i>
	<i class="atl-togglebtn-icon-off mdi mdi-checkbox-blank-outline ></i>
	<i class="atl-togglebtn-icon-on mdi mdi-checkbox-marked"></i>
	<span>Toggle Button</span>
</button>

button default state (choose one):
	* set the inner input value to 0 or 1
	* apply the atl-togglebtn-on or atl-togglebtn-off classes.

button options:
	* data-input: the input to target with the value if you didn't put one inside the button.
	* data-bclass-on: css classes to use when the button is on.
	* data-bclass-off: css classes to use when the button is off.
	* give the inner input a name to query it in form processors.

//*/

class ToggleButton {

	constructor(selector) {

		this.selector = selector;
		this.element = jQuery(selector);

		////////

		this.classStateOn   = 'atl-togglebtn-on';
		this.classButtonOn  = this.element.attr('data-bclass-on') || 'btn-primary';
		this.classStateOff  = 'atl-togglebtn-off';
		this.classButtonOff = this.element.attr('data-bclass-off') || 'btn-secondary';

		this.inputName = this.element.attr('data-input') || null;
		this.inputElement = null;

		this.findInputElement();
		this.applyDefaultState();
		this.onReady();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	findInputElement() {

		if(this.#findInputElementInternal())
		return;

		if(this.#findInputElementByName())
		return;

		////////

		this.inputName = null;
		this.inputElement = null;

		return;
	};

	#findInputElementInternal() {

		this.inputElement = this.element.find('input[type="hidden"]');

		if(this.inputElement.length !== 0) {
			console.log(`[ToggleBtn.findInputElement] ${this.inputElement.attr('name')} (internal)`);
			return true;
		}

		this.inputElement = null;

		return false;
	};

	#findInputElementByName() {

		if(this.inputName !== null) {
			this.inputElement = jQuery(`input[name="${this.inputName}"]`);

			if(this.inputElement.length !== 0) {
				console.log(`[ToggleBtn.findInputElement] ${this.inputElement.attr('name')} (by name)`);
				return true;
			}
		}

		this.inputElement = null;

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	applyDefaultState() {

		(this.element)
		.removeClass('atl-togglebtn-loading')
		.on('click', this.onClick.bind(this));

		////////

		if(this.#applyDefaultStateByInputValue())
		return;

		if(this.#applyDefaultStateByClass())
		return;

		////////

		this.setState(false);

		return;
	};

	#applyDefaultStateByInputValue() {

		if(this.inputElement !== null) {
			this.setState(!!parseInt(this.inputElement.val()));
			return true;
		}

		return false;
	};

	#applyDefaultStateByClass() {

		if(this.element.hasClass(this.classStateOn)) {
			this.setState(true);
			return true;
		}

		if(this.element.hasClass(this.classStateOff)) {
			this.setState(false);
			return true;
		}

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setState(state) {

		if(state) {
			(this.element)
			.addClass(`${this.classStateOn} ${this.classButtonOn}`)
			.removeClass(`${this.classStateOff} ${this.classButtonOff}`)
		}

		else {
			(this.element)
			.addClass(`${this.classStateOff} ${this.classButtonOff}`)
			.removeClass(`${this.classStateOn} ${this.classButtonOn}`)
		}

		if(this.inputElement)
		this.inputElement.val(state ? 1 : 0);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onReady() {

		let value = this.element.attr('data-value') || 0;

		////////

		value = parseInt(value);

		if(value === 1) {
			if(!this.element.hasClass(this.classButtonOn))
			this.onClick();
		}

		return;
	};

	onClick() {

		let state = !this.element.hasClass(this.classButtonOn);

		this.setState(state);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {

		jQuery('.atl-togglebtn')
		.each((k, v)=> new ToggleButton(v));

		return;
	};

};

export default ToggleButton;
