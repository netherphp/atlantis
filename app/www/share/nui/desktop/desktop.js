////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Desktop {

	static Framework = null;

	constructor() {

		this.id = null;
		this.element = null;
		this.windows = [];

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

		this.id = `atl-dtop-desktop-${crypto.randomUUID()}`;

		return this;
	};

	_elementBuild() {

		this.element = jQuery('<div />');

		this.element.attr('id', this.id);
		this.element.addClass('atl-dtop-desktop');

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	addWindow(win, x=null, y=null) {

		win.setParent(this.element);

		////////

		if(x !== null && y !== null)
		win.setPosition(x, y);

		////////

		this.element.append(win.element);

		return this;
	};

};

export default Desktop;
