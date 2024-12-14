////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import DesktopManager from './manager.js';
import Taskbar from './taskbar.js';
import StartApp from './apps/start.js';

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

	/**
	 * @property {Taskbar} taskbar
	 */

	constructor(selector) {

		this.id = null;
		this.element = null;
		this.container = null;
		this.dmgr = null;
		this.taskbar = null;
		this.apps = [];

		this.name = 'NUI Desktop';
		this.version = 'v1';

		this._elementGenID();
		this._elementBuild();
		this._elementDock(selector);
		this._dmgrBuild();

		(this.container)
		.append(this.element);

		this.appInstall(StartApp);

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
		this.dmgr = new DesktopManager;
		this.taskbar = new Taskbar;

		////////

		this.taskbar.setOS(this);
		this.dmgr.createDesktop(true);

		////////

		this.dsplit.find('[data-dock=taskbar]').append(this.taskbar.element)
		this.dsplit.find('[data-dock=desktop]').append(this.dmgr.element);

		this.element.append(this.dsplit);

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

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default OS;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
