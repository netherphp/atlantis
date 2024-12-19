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
		this.singleInstance = false;

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

	onInstall(os) {

		//console.log(`[App.onInstall] ${this.name} (${this.id})`);

		this.setOS(os);

		////////

		if(this.os) {
			if(this.taskbarItem === true)
			this.pushToTaskbar(false);
		}

		////////

		this.onInstalled();

		return;
	};

	onLaunch(os) {

		console.log(`[App.onLaunch] ${this.name} (${this.id})`);

		////////

		if(this.singleInstance) {
			if(this.windows.length !== 0)
			this.windows[0].bringToTop();

			else
			this.onLaunchSingle();
		}

		else {
			this.onLaunchInstance();
		}

		////////

		return false;
	};

	onWindowAnim(jEv) {

		let name = jEv.originalEvent.animationName;
		let win = this.findWindowByElement(jEv.target);

		////////

		console.log(`[App.onWindowAnim] ${this.name} (${this.id}) ${name}`);

		if(name === 'nui-window-show')
		this.onWindowShown(jEv, win);

		////////

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// these these events are designed to be overridden by the userland
	// application to perform whatever actions are needed.

	onConstruct() {

		console.log(`[App.onConstruct] ${this.name} (${this.id})`);

		// applications should overwrite this if needed.

		return;
	};

	onReady() {

		console.log(`[App.onReady] ${this.name} (${this.id})`);

		// applications should overwrite this if needed.

		return;
	};

	onInstalled() {

		console.log(`[App.onInstalled] ${this.name} (${this.id})`);

		return;
	};

	onLaunchSingle() {

		console.log(`[App.onLaunchSingle] ${this.name} (${this.id})`);

		// applications should overwrite this to spawn their windows
		// and push those windows into this.windows.

		return;
	};

	onLaunchInstance() {

		console.log(`[App.onLaunchInstance] ${this.name} (${this.id})`);

		// applications should overwrite this to spawn their windows
		// and push those windows into this.windows.

		return;
	};

	onWindowShown(jEv, win) {

		console.log(`[App.onWindowShown] ${this.name} (${this.id}) ${win.id}`);

		// applications should overwrite this to spawn their windows
		// and push those windows into this.windows.

		return;
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

	setSingleInstance(enable) {

		this.singleInstance = enable;

		return this;
	};

	setTaskbarItem(enable) {

		this.taskbarItem = enable;

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

	findWindow(win) {

		for(const w in this.windows)
		if(this.windows[w].id === win.id)
		return win;

		return false;
	};

	findWindowByElement(el) {

		let elid = jQuery(el).attr('id');

		////////

		for(const w in this.windows)
		if(this.windows[w].element.attr('id') === elid)
		return this.windows[w];

		////////

		return false;
	};

	registerWindow(win) {

		let found = this.findWindow(win);

		////////

		if(!found) {
			this.windows.push(win);
			this.pushToDesktop(win);
			this.bindWindowAnim(win);
		}

		console.log(`[App.registerWindow] windows: ${this.windows.length}`);

		return this;
	};

	unregisterWindow(win) {

		this.windows = this.windows.filter(
			(w)=> w.id !== win.id
		);

		console.log(`[App.unregisterWindow] windows: ${this.windows.length}`);

		return this;
	};

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

	pushToDesktop(win) {

		if(!this.os)
		return false;

		(this.os.dmgr.current)
		.addWindow(win);

		return true;
	};

};

export default App;
