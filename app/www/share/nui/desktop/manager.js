////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Manager {

	static Framework = null;

	constructor() {

		this.id = null;
		this.element = null;
		this.stack = [];
		this.current = null;

		////////

		(this)
		._elementGenID()
		._elementBuild();

		////////

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	_elementGenID() {

		this.id = `atl-dtop-mgr-${crypto.randomUUID()}`;

		return this;
	};

	_elementBuild() {

		this.element = jQuery('<div />');

		////////

		this.element.attr('id', this.id);
		this.element.addClass('atl-dtop-manager');

		////////

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	createDesktop(setCurrent=false) {

		let d = new Manager.Framework.Desktop;

		////////

		this.stack.push(d);
		this.element.append(d.element);

		if(setCurrent)
		this.current = d;

		////////

		return d;
	};

};

export default Manager;
