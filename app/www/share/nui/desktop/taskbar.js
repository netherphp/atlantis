////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from './__main.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateTaskbarHTML = `
<div id="" class="atl-dtop-taskbar">
	<section data-taskbar-bin="start"></section>
	<div></div>
	<section class="pos-relative">
		<div class="pos-absolutely scroll-y">
			<div data-taskbar-bin="open"></div>
		</div>
	</section>
	<div></div>
	<section data-taskbar-bin="end"></section>
</div>
`;

let TemplateTaskbarItemHTML = `
<div id="" class="atl-dtop-taskbar-item ta-center">
	<em class="mdi mdi-television-shimmer"></em>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Taskbar {

	static get EvContextName() { return 'atl-dtop-taskbar'; };

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor() {

		this.id = null;
		this.element = null;
		this.elIconList = null;
		this.items = [];
		this.os = null;

		////////

		this.binStart = null;
		this.binOpen = null;
		this.binEnd = null;

		////////

		this.eventHandlers = {
			'atl-dtop-app-installed': this.onAppInstalled.bind(this),
			'atl-dtop-window-show':   this.onWindowShow.bind(this),
			'atl-dtop-window-quit':   this.onWindowQuit.bind(this)
		};

		////////

		(this)
		.#generateElementID()
		.#generateElementStructure();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#generateElementID() {

		this.id = `atl-dtop-taskbar-${crypto.randomUUID()}`;

		return this;
	};

	#generateElementStructure() {

		this.element = jQuery(TemplateTaskbarHTML);
		this.element.attr('id', this.id);

		this.elIconList = this.element.find('[data-taskbar-bin="start"]');

		////////

		this.binStart = this.element.find('[data-taskbar-bin="start"]');
		this.binOpen = this.element.find('[data-taskbar-bin="open"]');
		this.binEnd = this.element.find('[data-taskbar-bin="end"]');

		////////

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setOS(os) {

		if(this.os)
		this.unregisterAllEvents();

		////////

		this.os = os;
		this.registerAllEvents();

		return;
	};

	registerAllEvents() {

		let origin = Taskbar.EvContextName;

		////////

		for(const eName in this.eventHandlers)
		this.os.registerEvent(`${eName}.${origin}`, this.eventHandlers[eName]);

		////////

		return;
	};

	unregisterAllEvents() {

		let origin = Taskbar.EvContextName;

		////////

		for(const eName in this.eventHandlers)
		this.os.unregisterEvent(`${eName}.${origin}`);

		////////

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onAppInstalled(jEv, { app }) {

		if(app.pinToTaskbar) {
			let item = new TaskbarItem(this, app);

			this.items.push(item);

			if(app.pinToTaskbar === 'start') {
				this.binStart.append(item.element);
			}

			if(app.pinToTaskbar === 'end') {
				this.binEnd.append(item.element);
			}
		}

		return;
	};

	onWindowShow(jEv, { win }) {

		if(win.showOnTaskbar) {
			let item = new TaskbarItem(this, win);

			this.items.push(item);
			this.binOpen.append(item.element);
		}

		return;
	};3

	onWindowQuit(jEv, { win }) {

		if(win.showOnTaskbar) {
			this.items = this.items.filter(function(item) {

				if(item.win) {
					if(item.win.id === win.id) {
						item.element.remove();
						return false;
					}
				}

				return true;
			});
		}

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class TaskbarItem {

	constructor(taskbar, something) {

		this.id = null;
		this.parent = taskbar;
		this.app = null;
		this.win = null;

		this.element = null;
		this.elIcon = null;

		////////

		if(something.id.match('-dtop-window-')) {
			this.win = something;
			this.app = something.app;
		}

		if(something.id.match('-dtop-app-')) {
			this.win = null;
			this.app = something;
		}

		////////

		(this)
		.#generateElementID()
		.#generateElementStructure()
		.#bindElementEvents();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#generateElementID() {

		this.id = `atl-dtop-taskbar-item-${crypto.randomUUID()}`;

		return this;
	};

	#generateElementStructure() {

		this.element = jQuery(TemplateTaskbarItemHTML);
		this.element.attr('id', this.id);

		this.elIcon = this.element.find('em');
		this.elIcon.removeClass('mdi mdi-television-shimmer');

		if(this.win)
		this.elIcon.addClass(this.win.icon);

		if(this.app)
		this.elIcon.addClass(this.app.icon);

		return this;
	};

	#bindElementEvents() {

		(this.element)
		.on('click', this.onClick.bind(this))
		.on('auxclick', this.onAuxClick.bind(this))
		.on('contextmenu', this.onContextMenu.bind(this));

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onClick(jEv) {

		return this.onLeftClick(jEv);
	};

	onAuxClick(jEv) {

		if(jEv.originalEvent.button === 1)
		return this.onMiddleClick(jEv);

		if(jEv.originalEvent.button === 2)
		return this.onRightClick(jEv);

		////////

		return false;
	};

	onContextMenu(jEv) {

		// literally just stop the right click menus from popping up
		// so that the aux click handler can handle our custom right
		// click menus.

		return false;
	};

	////////////////////////////////
	////////////////////////////////

	onLeftClick(jEv) {

		let ev = jEv.originalEvent;

		//console.log(`[Taskbar.onLeftClick] ${this.id}}`);

		if(this.win) {
			this.win.bringToTop();
			return false;
		}

		if(this.app) {
			this.parent.os.appLaunchByIdent(this.app.ident);
			return false;
		}

		return false;
	};

	onMiddleClick(jEv) {

		let ev = jEv.originalEvent;

		//console.log(`[Taskbar.onMiddleClick]  ${this.id}}`);

		return false;
	};

	onRightClick(jEv) {

		let ev = jEv.originalEvent;

		//console.log(`[Taskbar.onRightClick]  ${this.id}}`);

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	destroy() {

		for(const item in this.parent.items) {
			if(this.parent.items[k].id !== this.id)
			continue;

			this.parent.items[k] = null;
			this.element.remove();
		}

		this.parent.items = this.parent.items.filter((v)=> v !== null);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setParent(tbar) {

		this.parent = tbar;

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export { Taskbar, TaskbarItem };

////////////////////////////////////////////////////////////////////////////////
export default Taskbar; ////////////////////////////////////////////////////////
