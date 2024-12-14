////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import OpSys from './os.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateTaskbarHTML = `
<div id="" class="atl-dtop-taskbar">
	<div class="flex flex-column align-items-center"></div>
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

	constructor() {

		this.id = null;
		this.element = null;
		this.elIconList = null;
		this.items = [];
		this.os = null;

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

		this.elIconList = this.element.find('div.flex');

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	addItem(appID, icon, name) {

		let item = new TaskbarItem(appID, icon, name);

		item.setParent(this);

		this.items.push(item);
		this.elIconList.append(item.element);

		return item;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setOS(os) {

		if(os instanceof OpSys)
		this.os = os;

		if(os === null)
		this.os = null;

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class TaskbarItem {

	constructor(appID, icon, name) {

		this.id = null;
		this.element = null;
		this.parent = null;
		this.elIcon = null;

		this.app = appID;
		this.icon = icon;
		this.name = name;

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
		this.elIcon.addClass(this.icon);

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

		console.log(`left click ${this.id}}`);

		this.parent.os.appLaunchByIdent(this.app);


		return false;
	};

	onMiddleClick(jEv) {

		let ev = jEv.originalEvent;

		console.log(`middle click ${this.id}}`);

		return false;
	};

	onRightClick(jEv) {

		let ev = jEv.originalEvent;

		console.log(`right click ${this.id}}`);

		return false;
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

export default Taskbar;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
