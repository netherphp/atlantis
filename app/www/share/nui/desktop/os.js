////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateOperatingSystemHTML = `
<div id="" class="atl-dtop-os o-0 g-0">
</div>
`;

let TemplateDesktopHTML = `
<div class="row align-items-stretch g-0 h-100">
	<div class="col-auto pos-relative" data-dock="taskbar"></div>
	<div class="col pos-relative" data-dock="desktop"></div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class OS {

	static Framework = null;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(selector) {

		this.id = null;
		this.element = null;
		this.container = null;
		this.dmgr = null;
		this.taskbar = null;
		this.apps = [];

		this.configDefaults = {
			'OS.WindowInactiveClass': 'atl-dtop-desktop-window-inactive-dim'
		};

		this.styleVarDefaults = { };

		this.name = 'NUI Desktop';
		this.version = 'v1';

		this._elementGenID();
		this._elementBuild();
		this._elementDock(selector);
		this._dmgrBuild();
		this._fillStyleVars();

		(this.container)
		.append(this.element);

		//this.appInstall(StartApp);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	_elementGenID() {

		this.id = `atl-dtop-os-${crypto.randomUUID()}`;

		return this;
	};

	_elementBuild() {

		this.element = jQuery(TemplateOperatingSystemHTML);

		return this;
	};

	_elementDock(into) {

		this.container = jQuery(into);

		////////

		this.container.append(this.element);
		this.container.parent().addClass('sitemenu-body-lock h-100');

		////////

		return this;
	};

	_dmgrBuild() {

		this.dsplit = jQuery(TemplateDesktopHTML);
		this.dmgr = new OS.Framework.DesktopManager;
		this.taskbar = new OS.Framework.Taskbar;

		let winInactiveClass = this.fetch('OS.WindowInactiveClass');
		let tbsplit = this.dsplit.find('[data-dock=taskbar]');
		let dksplit = this.dsplit.find('[data-dock=desktop]')

		if(!winInactiveClass)
		winInactiveClass = 'atl-dtop-desktop-window-inactive-dim';

		////////

		this.taskbar.setOS(this);
		this.dmgr.createDesktop(true);

		(this.dmgr)
		.resetWindowInactiveClass()
		.pushWindowInactiveClass(winInactiveClass);

		////////

		tbsplit.append(this.taskbar.element)
		dksplit.append(this.dmgr.element);

		this.element.append(this.dsplit);

		return;
	};

	_fillStyleVars() {

		let vars = {
			'--atl-dtop-cfg-colour-nullary': null,
			'--atl-dtop-cfg-colour-primary': null
		};

		// keep the default values around.

		this.styleVarDefaults = this.fetchStyleVarList(Object.keys(vars));

		// fill in the overwrites.

		for(const k of Object.keys(this.styleVarDefaults))
		this.pushStyleVar(k, (this.fetch(k) || this.styleVarDefaults[k]));

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	appInstall(app) {

		if(typeof app.id === 'undefined')
		app = new app;

		////////

		app.onInstall(this);

		this.apps.push(app);

		return app;
	};

	appInstallFromModule(url) {

		return (
			import(url)
			.then((ns)=> this.appInstall(ns.default))
		);
	};

	appLaunchByID(appID) {

		let found = null;

		////////

		for(const app of this.apps) {
			if(app.id !== appID)
			continue;

			found = app;
			break;
		}

		////////

		if(found !== null)
		found.onLaunch(this);

		return this;
	};

	appLaunchByIdent(appIdent) {

		let found = null;

		////////

		for(const app of this.apps) {
			if(app.ident !== appIdent)
			continue;

			found = app;
			break;
		}

		////////

		if(found !== null)
		found.onLaunch(this);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setName(name) {

		this.name = name;

		return this;
	};

	setVersion(version) {

		this.version = version;

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	save(key, value) {

		value = JSON.stringify(value);

		localStorage.setItem(key, value);
		console.log(`[OS.save] ${key} ${value}`);

		return;
	};

	fetch(key) {

		console.log(`[OS.fetch] ${key} ${localStorage.getItem(key)}`);

		return JSON.parse(localStorage.getItem(key));
	};

	delete(key) {

		console.log(`[OS.delete] ${key}`);

		localStorage.removeItem(key);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	fetchStyleVar(key) {

		let cvarOrigin = document.querySelector(':root');
		let cvarStyle = window.getComputedStyle(cvarOrigin);

		return cvarStyle.getPropertyValue(key);
	};

	fetchStyleVarList(keys) {

		let cvarOrigin = document.querySelector(':root');
		let cvarStyle = window.getComputedStyle(cvarOrigin);
		let output = {};

		////////

		for(const k in keys)
		output[keys[k]] = cvarStyle.getPropertyValue(keys[k]);

		////////

		return output;
	};

	pushStyleVar(key, val) {

		let cvarOrigin = document.querySelector(':root');
		let cvarStyle = window.getComputedStyle(cvarOrigin);

		cvarOrigin.style.setProperty(key, val);

		return this;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default OS;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
