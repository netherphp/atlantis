////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class App {

	static Framework = null;

	static get EvWindowAnimEnd() { return 'animationend.atl-dtop-app-winanim'; }

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor() {

		this.id = null;
		this.ident = null;
		this.name = 'Application';
		this.icon = 'mdi mdi-television-shimmer';
		this.element = null;
		this.windows = [];
		this.os = null;

		this.taskbarItem = null;

		// if this app should show up in any desktop management lists
		// as an app the user can directly launch.

		this.listed = true;

		////////

		this.onConstruct();

		(this)
		._elementGenID()
		._elementBuild();

		this.onReady();

		////////

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	_elementGenID() {

		this.id = `atl-dtop-app-${crypto.randomUUID()}`;

		return this;
	};

	_elementBuild() {

		this.element = jQuery('<div />');

		////////

		this.element.attr('id', this.id);
		this.element.addClass('atl-dtop-app');

		////////

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onConstruct() {

		return this;
	};

	onReady() {

		return this;
	};

	onInstall(os) {

		console.log(`[App.onInstall] ${this.name} ${this.id}`);

		this.setOS(os);

		////////

		if(this.os) {
			if(this.taskbarItem === true)
			this.pushToTaskbar(false);
		}

		////////

		return;
	};

	onLaunch(os) {

		console.log(`[App.onLaunch] ${this.name} ${this.id}`);

		return;
	};

	onWindowAnim(jEv) {

		console.log(`[App.onWindowAnim] ${this.name} ${this.id} ${jEv.originalEvent.animationName}`);

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setName(name) {

		this.name = name;

		return this;
	};

	setIdent(ident) {

		this.ident = ident;

		return this;
	};

	setIcon(icon) {

		this.icon = icon;

		return this;
	};

	setListed(enable) {

		this.listed = enable;

		return this;
	};

	setOS(os) {

		this.os = os;

		return this;
	};

	////////////////////////////////
	////////////////////////////////

	isListed() {

		return (this.listed === true);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////


	bindWindowAnim(win) {

		win.element.on(
			this.constructor.EvWindowAnimEnd,
			this.onWindowAnim.bind(this)
		);

		return this;
	};

	pushToTaskbar(later=false) {

		if(later) {
			if(this.taskbarItem)
			this.taskbarItem.destroy();

			this.taskbarItem = true;
			return true;
		}

		////////

		if(!this.os)
		return false;

		if(!this.os.taskbar)
		return false;

		this.taskbarItem = this.os.taskbar.addItem(
			this.ident, this.icon, this.name
		);

		return true;
	};

};

export default App;
