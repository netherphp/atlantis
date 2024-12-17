////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

/**
 * @class
 * @member {string} CacheBuster
 */

class Framework {
/*//
this class presents itself with a static styled api despite not truely being
a static system, because when pulled in it should be treated as a namespace or
stack of consts. it is not designed to be interacted with, but rather an
authourative index.
//*/


	constructor() {

		/** @type {string} */
		this.CacheBuster = null;

		/** @type {OS} */
		this.System = null;

		/** @type {Desktop} */
		this.Desktop = null;

		/** @type {DesktopManager} */
		this.DesktopManager = null;

		/** @type {App} */
		this.App = null;

		/** @type {Window} */
		this.Window = null;

		/** @type {Taskbar} */
		this.Taskbar = null;

		////////

		this.Files = {
			'System':         './os.js',
			'Desktop':        './desktop.js',
			'DesktopManager': './manager.js',
			'App':            './app.js',
			'Window':         './window.js',
			'Taskbar':        './taskbar.js'
		};

		////////

		this.#determineModuleURL();
		this.#determineCacheBuster();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#determineModuleURL() {

		// import Whatever from 'whatever.js?v=42';

		this.url = import.meta.url;

		return;
	};

	#determineCacheBuster() {

		let qm = this.url.indexOf('?');
		let qv = null;

		// no query data was passed to the module.

		if(qm === -1) {
			this.CacheBuster = crypto.randomUUID();
			return;
		}

		// no version info was passed to the module.

		qv = new URLSearchParams(this.url.substring(qm));

		if(!qv.has('v')) {
			this.CacheBuster = cyrpto.randomUUI();
			return;
		}

		// a version was passed to the module.

		this.CacheBuster = qv.get('v');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	load(onReady=null) {

		let self = this;
		let loaders = [];
		let waitzord = null;

		////////

		for(const k in this.Files) {
			let url = `${this.Files[k]}?v=${this.CacheBuster}`;
			let modlod = import(url);

			// hydrate the property on this namespace with the same name
			// after the script file loads.
			modlod.then(function(m) {
				self[k] = m.default;
				self[k].Framework = self;
				return self[k];
			});

			loaders.push(modlod);
		}

		////////

		waitzord = Promise.all(loaders);
		waitzord.then((v)=> this);

		if(typeof onReady === 'function')
		waitzord.then(onReady);

		return waitzord;
	};

	static LoadOld(onReady=null) {

		let main = new NUIDesktop;
		let loaders = [];

		////////

		for(const k in main.files) {
			let url = `${main.files[k]}?v=${main.cacheBuster}`;
			let modlod = import(url);

			// hydrate the property on this namespace with the same name
			// after the script file loads.
			modlod.then((m)=> (main[k] = m.default));

			loaders.push(modlod);
		}

		////////

		let waitzord = Promise.all(loaders).then((v)=> main);

		waitzord.then((v)=> main);

		if(typeof onReady === 'function')
		waitzord.then(onReady);

		return waitzord;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default new Framework; //////////////////////////////////////////////////
