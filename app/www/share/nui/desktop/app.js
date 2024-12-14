/*//////////////////////////////////////////////////////////////////////////////
// NUI Desktop Application /////////////////////////////////////////////////////

class Example extends App {

	constructor() {
		super();

		this.name = 'Example App';
		this.ident = 'local.example.app';
		this.icon = 'mdi mdi-alien';

		return;
	};

	onInstall(os) {
		super.onInstall(os);
		super.pushToTaskbar();

		return;
	};

	onLaunch(os) {
		super.onLaunch(os);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////*/

import Util from '../util.js';
import OpSys from './os.js';
import Taskbar from './taskbar.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class App {

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

		(this)
		._elementGenID()
		._elementBuild();

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

		console.log(`[App.onInstall] ${this.name} ${this.id}`);

		this.setOS(os);

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

		if(os instanceof OpSys)
		this.os = os;

		else
		this.os = null;

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

	pushToTaskbar() {

		if(Util.NotInstanceOf(this.os, OpSys))
		return false;

		if(Util.NotInstanceOf(this.os.taskbar, Taskbar))
		return false;

		this.taskbarItem = this.os.taskbar.addItem(
			this.ident, this.icon, this.name
		);

		return true;
	};

};

export default App;
