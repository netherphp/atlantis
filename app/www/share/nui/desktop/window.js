////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import Vec2  from '../units/vec2.js?v=20241216a';
import Util  from '../util.js?v=20241216a';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateWindowHTML = `
<div id="" class="atl-dtop-win atl-dtop-win-init d-none">
	<header>
		<div class="row align-items-center g-0 p-0 m-0 flex-nowrap">
			<div class="col-auto px-2">
				<em class="mdi mdi-solid"></em>
			</div>
			<div class="col pr-2">
				<span>Window Title</span>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn atl-dtop-win-action atl-dtop-win-action-cancel" data-win-action="win-center">
					<em class="mdi mdi-fit-to-screen"></em>
				</button>
			</div>
			<div class="d-none col-auto">
				<button class="atl-dtop-btn atl-dtop-win-action atl-dtop-win-action-cancel" data-win-action="win-fit">
					<em class="mdi mdi-image-size-select-small"></em>
				</button>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn atl-dtop-win-action atl-dtop-win-action-cancel" data-win-action="win-min">
					<em class="mdi mdi-window-minimize"></em>
				</button>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn atl-dtop-win-action atl-dtop-win-action-cancel" data-win-action="win-max">
					<em class="mdi mdi-window-maximize"></em>
				</button>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn atl-dtop-win-action atl-dtop-win-action-cancel" data-win-action="cancel">
					<em class="mdi mdi-window-close"></em>
				</button>
			</div>
		</div>
	</header>
	<section></section>
	<footer>
		<div class="row g-0">
			<div class="col mx-0 px-0">
				<div class="row flex-nowrap justify-content-end align-items-center g-1" data-dock="buttons"></div>
			</div>
		</div>
	</footer>
	<button class="atl-dtop-win-resizehandle">
		<em class="mdi mdi-resize-bottom-right"></em>
	</button>
	<div class="atl-dtop-win-overlay d-none">
		<div class="pos-absolute pos-v-center pos-h-center">
			<div class="ta-center">
				<i class="mdi mdi-loading mdi-spin-fast fs-most-large"></i>
			</div>
			<div class="ta-center fw-bold tt-upper">
				Loading...
			</div>
		</div>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// all of the properties are stacked within constructor because safari from
// 2019 is still out in the wild and it still didn't support property syntax
// at all yet by then.

class Window {

	static Framework = null;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static get ActionAccept() { return 'accept'; };
	static get ActionCancel() { return 'cancel'; };
	static get ActionWinMin() { return 'win-min'; };
	static get ActionWinMax() { return 'win-max'; };

	static get EvTouchDownMove() { return 'touchstart.atl-dtop-win-move'; };
	static get EvTouchDownSize() { return 'touchstart.atl-dtop-win-resize'; };
	static get EvMouseDownMove() { return 'mousedown.atl-dtop-win-move'; };
	static get EvMouseDownSize() { return 'mousedown.atl-dtop-win-resize'; };
	static get EvWindowAction() { return 'click.atl-dtop-win-action'; }
	static get EvWindowActionTouch() { return 'touchstart.atl-dtop-win-action'; }
	static get EvWindowAnimEnd() { return 'animationend.atl-dtop-win-anim'; }

	static get SelectHandleMove() { return '> header'; };
	static get SelectHandleSize() { return '.atl-dtop-win-resizehandle'; };
	static get SelectWindowAction() { return '[data-win-action]'; }

	static get PrefixElementID() { return 'atl-desktop-window'; };

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(app=null) {

		this.id = null;
		this.ident = null;
		this.title = null;

		this.pos = new Vec2;
		this.size = new Vec2;

		this.element = null;
		this.parent = null;
		this.actions = {};
		this.app = null;
		this.os = null;

		this.elHeader = null;
		this.elTitle = null;
		this.elTitleIcon = null;
		this.elBody = null;
		this.elFooter = null;
		this.elFooterBtnBox = null;

		this.delaySavePosition = 250;
		this.delaySaveSize = 250;

		this.dampSetPosition = null;
		this.dampSetSize = null;

		this.enableResize = false;
		this.enableMove = false;
		this.enableMax = false;
		this.enableMin = false;
		this.userMoved = false;
		this.userSized = false;
		this.beenShown = false;

		////////

		(this)
		.#generateElementID()
		.#elementBuild()
		.#elementBind();

		////////

		if(app)
		this.setAppAndBake(app);

		////////

		this.setInitialSizing();

		this.onConstruct();
		this.onReady();

		return;
	};

	static New({ title }) {

		let o = new Window;

		o.setTitle(title);

		return o;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#generateElementID() {

		let prefix = this.constructor.PrefixElementID;
		let uuid = crypto.randomUUID();

		this.id = `${prefix}-${uuid}`;

		return this;
	};

	#elementBuild() {

		let self = this;

		////////

		this.element = jQuery(TemplateWindowHTML);
		this.element.attr('id', this.id);

		this.elHeader = this.element.find('header');
		this.elBody = this.element.find('section');
		this.elFooter = this.element.find('footer');

		this.elTitle = this.elHeader.find('span:first');
		this.elTitleIcon = this.elHeader.find('em:first');
		this.elFooterBtnBox = this.elFooter.find('[data-dock=buttons]');

		this.elOverlay = this.element.find('.atl-dtop-win-overlay');

		this.setAction('accept', this.onWindowActionAccept);
		this.setAction('cancel', this.onWindowActionCancel);

		////////

		return this;
	};

	#elementBind() {

		let self = this;

		////////

		(this.element)
		.on(
			this.constructor.EvMouseDownMove,
			this.constructor.SelectHandleMove,
			this.onTitleBarMouseDown.bind(this)
		)
		.on(
			this.constructor.EvTouchDownMove,
			this.constructor.SelectHandleMove,
			this.onTitleBarMouseDown.bind(this)
		)
		.on(
			this.constructor.EvMouseDownSize,
			this.constructor.SelectHandleSize,
			this.onResizeMouseDown.bind(this)
		)
		.on(
			this.constructor.EvTouchDownSize,
			this.constructor.SelectHandleSize,
			this.onResizeMouseDown.bind(this)
		)
		.on(
			this.constructor.EvWindowAction,
			this.constructor.SelectWindowAction,
			this.onWindowAction.bind(this)
		)
		.on(
			this.constructor.EvWindowActionTouch,
			this.constructor.SelectWindowAction,
			this.onWindowAction.bind(this)
		)
		.on(
			'click',
			this.onWindowClick.bind(this)
		)
		.on(
			this.constructor.EvWindowAnimEnd,
			this.onAnimationEnd.bind(this)
		);

		this.setMovable(true);
		this.setResizable(true);

		return;
	};

	getEventName(type) {

		let name = `${type}.${this.id}`;

		return name;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onConstruct() {

		return;
	};

	onReady() {

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onTitleBarMouseDown(jEv) {

		if(!this.enableMove || this.isMaximised())
		return false;

		////////

		let self = this;
		let ev = jEv.originalEvent;
		let offset = null;

		////////

		if(typeof ev.touches !== 'undefined')
		offset = (
			(new Vec2(ev.touches[0].clientX, ev.touches[0].clientY))
			.sub(this.getPosVec())
		);

		else
		offset = (
			(new Vec2(ev.clientX, ev.clientY))
			.sub(this.getPosVec())
		);

		////////

		this.element.addClass('atl-dtop-transition-none');
		this.bringToTop();

		jQuery(window)
		.on(this.getEventName('mousemove'), function(jEv) {
			self.onTitleBarMouseMove(jEv, offset);
			return false;
		})
		.on(this.getEventName('mouseup'), function(jEv) {
			self.onTitleBarMouseUp(jEv, offset);
			return false;
		})
		.on(this.getEventName('touchmove'), function(jEv) {
			self.onTitleBarMouseMove(jEv, offset);
			return false;
		})
		.on(this.getEventName('touchend'), function(jEv) {
			self.onTitleBarMouseUp(jEv, offset);
			return false;
		});

		////////

		return false;
	};

	onTitleBarMouseUp(jEv, oVec) {

		let ev = jEv.originalEvent;

		////////

		this.element.removeClass('atl-dtop-transition-none');

		jQuery(window)
		.off(this.getEventName('mousemove'))
		.off(this.getEventName('mouseup'))
		.off(this.getEventName('touchmove'))
		.off(this.getEventName('touchend'));

		////////

		return false;
	};

	onTitleBarMouseMove(jEv, oVec) {

		let ev = jEv.originalEvent;

		////////

		this.userMoved = true;

		if(typeof ev.touches !== 'undefined') {
			this.setPosition(
				(ev.touches[0].clientX - oVec.x),
				(ev.touches[0].clientY - oVec.y)
			);
		}

		else {
			this.setPosition(
				(ev.clientX - oVec.x),
				(ev.clientY - oVec.y)
			);
		}

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onResizeMouseDown(jEv) {

		if(!this.enableResize || this.isMaximised())
		return;

		////////

		let self = this;
		let ev = jEv.originalEvent;
		let offset = null;

		////////

		if(typeof ev.touches !== 'undefined') {
			offset = new Vec2(ev.touches[0].offsetX, ev.touches[0].offsetY);
		}

		else {
			offset = new Vec2(ev.offsetX, ev.offsetY);
		}

		offset = this.getOffsetClientCoords(offset.mult(0.5));

		////////

		(this.element)
		.addClass('atl-dtop-transition-none')
		.addClass('atl-dtop-ignore-input-all');

		this.setSize();
		this.bringToTop();

		jQuery(window)
		.on(this.getEventName('mousemove'), function(jEv) {
			self.onResizeMouseMove(jEv, offset);
			return;
		})
		.on(this.getEventName('mouseup'), function(jEv) {
			self.onResizeMouseUp(jEv, offset);
			return;
		})
		.on(this.getEventName('touchmove'), function(jEv) {
			self.onResizeMouseMove(jEv, offset);
			return;
		})
		.on(this.getEventName('touchend'), function(jEv) {
			self.onResizeMouseUp(jEv, offset);
			return;
		});

		////////

		return false;
	};

	onResizeMouseUp(jEv, oVec) {

		let ev = jEv.originalEvent;

		////////

		this.element.removeClass('atl-dtop-ignore-input-all');

		jQuery(window)
		.off(this.getEventName('mousemove'))
		.off(this.getEventName('mouseup'))
		.off(this.getEventName('touchmove'))
		.off(this.getEventName('touchup'));

		////////

		return false;
	};

	onResizeMouseMove(jEv, oVec) {

		let ev = jEv.originalEvent;
		let cur = null;

		////////

		if(typeof jEv.originalEvent.touches !== 'undefined')
		cur = jEv.originalEvent.touches[0];
		else
		cur = jEv.originalEvent;

		////////

		let x = Math.abs(cur.clientX - this.pos.x) + oVec.x;
		let y = Math.abs(cur.clientY - this.pos.y) + oVec.y;

		this.userSized = true;

		this.setSize(x, y);

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onWindowClick(jEv) {

		if(!this.element.is(':last-of-type'))
		this.bringToTop();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onWindowAction(jEv) {

		let ev = jEv.originalEvent;
		let el = jQuery(ev.target);
		let action = el.attr('data-win-action');

		ev.stopPropagation();
		ev.preventDefault();

		////////

		if(typeof this.actions[action] === 'function') {
			(this.actions[action]).call(this, ev, el);
			return false;
		}

		////////

		if(action === 'win-max') {
			this.element.toggleClass('maximise');
			return false;
		}

		if(action === 'win-fit') {
			this.resizeToFit();
			return false;
		}

		if(action === 'win-center') {
			this.centerInParent();
			return false;
		}

		console.log(`unhandled window action: ${action}`);
		return false;
	};

	onWindowActionAccept() {

		console.log(`[Window.onWindowActionAccept] ${this.id}`);
		this.quit();

		return;
	};

	onWindowActionCancel() {

		console.log(`[Window.onWindowActionCancel] ${this.id}`);
		this.quit();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onAnimationEnd(jEv) {

		let ev = jEv.originalEvent;

		////////

		if(ev.animationName === 'nui-window-quit') {
			this.destroy();
			return;
		}

		if(ev.animationName === 'nui-window-hide') {
			this.onHide();
			this.element.addClass('d-none');
			this.element.removeClass('hiding');
			this.onHidden();
			return;
		}

		if(ev.animationName === 'nui-window-show') {
			this.onShow();
			this.element.removeClass('showing');
			this.element.removeClass('atl-dtop-win-init');
			this.onShown();
			return;
		}

		////////

		return;
	};

	onShow(jEv) {

		return;
	};

	onShown(jEv) {

		return;
	};

	onHide(jEv) {

		return;
	};

	onHidden(jEv) {

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	addButton(title, action=Window.ActionAccept, altStyle=false) {

		let btn = jQuery('<button />');

		////////

		(btn)
		.attr('data-win-action', action)
		.addClass('atl-dtop-btn atl-dtop-win-action')
		.addClass(`atl-dtop-win-action-${action}`)
		.text(title);

		if(altStyle)
		btn.addClass('atl-dtop-btn-alt');

		////////

		this.elFooterBtnBox.append(
			jQuery('<div />')
			.addClass('col-auto')
			.append(btn)
		);

		return;
	};

	setAction(name, callable) {

		this.actions[name] = callable.bind(this);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	loadPosition(x=null, y=null) {

		//if(this.loadPositionFromOS())
		//return this;

		////////

		if(x !== null && y !== null)
		this.setPosition(x, y);

		////////

		return this;
	};

	loadPositionFromOS() {

		let pos = null;

		////////

		if(!this.os || !this.ident)
		return false;

		////////

		pos = this.os.fetch(`${this.ident}.pos`);

		if(!pos)
		return false;

		if(typeof pos.x === 'undefined')
		return false;

		if(typeof pos.y === 'undefined')
		return false;

		////////

		this.setPosition(pos.x, pos.y);

		return true;
	}

	loadSize(w=null, h=null, unit='px') {

		//if(this.loadSizeFromOS())
		//return this;

		////////

		if(w !== null && h !== null)
		this.setSize(w, h, unit);

		////////

		return this;
	};

	loadSizeFromOS() {

		let size = null;

		////////

		if(!this.os || !this.ident)
		return false;

		////////

		size = this.os.fetch(`${this.ident}.size`);

		if(!size)
		return false;

		if(typeof size.x === 'undefined')
		return false;

		if(typeof size.y === 'undefined')
		return false;

		////////

		this.setSize(size.x, size.y);

		return true;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	// find anything with data-win-input=which

	getInputElement(which) {

		let el = this.element.find(`[data-win-input="${which}"]`);

		return el;
	};

	getInputValue(which) {

		let el = this.getInputElement(which);

		return el.val();
	};

	// find anything with data-win-output=which

	getOutputElement(which) {

		let el = this.element.find(`[data-win-output="${which}"]`);

		return el;
	};

	getOutputValue(which) {

		let el = this.getOutputElement(which);

		return el.val();
	};

	// take into consideration the client offset of the desktop and modify
	// any client values received to account for it.

	getOffsetClientCoords(oVec) {

		let offset = this.element.parent().offset();

		// i was surprised that the jQuery offset was working to convey
		// that for things nested in flexbox. figured the offset would be
		// relative 0,0. if we are revisiting this in the future then rip.

		return oVec.sub(Window.Framework.Vec2.FromOffset(offset));
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	show() {

		this.element.addClass('showing');
		this.element.removeClass('d-none');

		// emit event app can listen for.

		return this;
	};

	showHeader() {

		this.elHeader.show();

		return this;
	};

	showFooter() {

		this.elFooter.show();

		return this;
	};

	showOverlay() {

		this.elOverlay.removeClass('d-none')

		return this;
	};

	hide() {

		this.element.addClass('d-none');

		// emit event app can listen for.

		return this;
	};

	hideHeader() {

		this.elHeader.hide();

		return this;
	};

	hideFooter() {

		this.elFooter.hide();

		return this;
	};

	hideOverlay() {

		this.elOverlay.addClass('d-none')

		return this;
	};

	maximise() {

		this.element.addClass('maximise');

		return this;
	};

	destroy() {

		this.element.remove();

		if(this.app)
		this.app.unregisterWindow(this);

		return this;
	};

	bringToTop() {

		if(this.parent === null)
		return true;

		////////

		// if this becomes laggy with many windows rethink it with zindex
		// but tbh this will probably be fine.

		(this.parent)
		.append(this.element);

		////////

		return this;
	};

	quit() {

		this.element.addClass('quitting');

		return;
	};

	////////////////////////////////
	////////////////////////////////

	pushToDesktop() {

		if(!this.os)
		return false;

		(this.os.dmgr.current)
		.addWindow(this);

		return true;
	};

	////////////////////////////////
	////////////////////////////////

	getPosVec() {

		// i did this thinking it was wise to not make it too easy to just
		// overwrite data in the working reference.

		return new Vec2(this.pos.x, this.pos.y);
	};

	setPosition(x=null, y=null) {

		if(x === null || y === null) {
			x = parseFloat(this.element.left());
			y = parseFloat(this.element.top());
		}

		this.element.css({
			'left': `${x}px`,
			'top': `${y}px`
		});

		this.pos.x = x;
		this.pos.y = y;

		////////

		return this;

		if(this.dampSetPosition)
		clearInterval(this.dampSetPosition);

		if(this.os && this.ident)
		this.dampSetPosition = setTimeout(
			(()=> this.os.save(`${this.ident}.pos`, this.getPosVec())),
			this.delaySavePosition
		);

		////////

		return this;
	};

	setPositionBasedOn(what=null) {

		//console.log(`[Window.setPositionBasedOn] ${this.id}`);
		//console.log(what);

		if(what instanceof Window)
		if(what.isUserMoved()) {
			(this)
			.setUserMoved()
			.setPosition(
				(what.pos.x + 16),
				(what.pos.y + 16),
			);

			return this;
		}

		////////

		this.centerInParent();

		return this;
	};

	////////////////////////////////
	////////////////////////////////

	getSizeVec() {

		// i did this thinking it was wise to not make it too easy to just
		// overwrite data in the working reference.

		return new Vec2(this.size.w, this.size.h);
	};

	setSize(w=null, h=null, unit='px') {

		if(w === null && h === null) {
			w = this.element.outerWidth();
			h = this.element.outerHeight();
		}

		if(typeof w === 'string') {
			let f = w.match(/^(\d+)(.+?)$/);

			if(f && f.length) {
				w = parseFloat(f[0]);
				unit = f[1];
			}
		}

		if(typeof h === 'string') {
			let f = h.match(/^(\d+)(.+?)$/);

			if(f && f.length) {
				w = parseFloat(f[0]);
				unit = f[1];
			}
		}

		this.element.css({
			'width': `${w}${unit}`,
			'height': `${h}${unit}`
		});

		this.size.w = w;
		this.size.h = h;
		this.size.unit = unit;

		return this;

		////////

		if(this.dampSetSize)
		clearInterval(this.dampSetSize);

		if(this.os && this.ident)
		this.dampSetSize = setTimeout(
			(()=> this.os.save(`${this.ident}.size`, this.getSizeVec())),
			this.delaySaveSize
		);

		////////

		return this;
	};

	setSizeAuto() {

		(this.element)
		.css({
			'width': `auto`,
			'height': `auto`
		});

		return this;
	};

	setUserMoved(state=true) {

		this.userMoved = state;

		return this;
	};

	setUserSized(state=true) {

		this.userMoved = state;

		return this;
	};

	setInitialSizing() {

		if(this.os) {
			let automax = this.os.fetch('OS.WindowAutoMaximise');

			if(automax === 1) {
				if(window.innerWidth < window.innerHeight)
				this.maximise();
				return;
			}

			if(automax === 2) {
				this.maximise();
				return;
			}
		}

		this.resizeToFit();

		return;
	};

	centerInParent() {

		if(this.parent === null) {
			console.log(`[Window.centerInParent] ${this.id} has no parent`);
			return;
		}

		let cw = this.parent.width() / 2;
		let ch = this.parent.height() / 2;

		this.setSize();

		cw -= (this.size.w / 2);
		ch -= (this.size.h / 2);

		this.setPosition(cw, ch);

		return;
	};

	resizeToFit() {

		this.element.addClass('autosize-temp');
		this.setSizeAuto();
		this.setSize();
		this.element.removeClass('autosize-temp');

		return this;
	};

	////////////////////////////////
	////////////////////////////////

	setTitle(t) {

		this.title = t;

		this.elTitle.text(this.title);

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

	setParent(parent) {

		this.parent = parent;

		return this;
	};

	setIcon(icon) {

		(this.elTitleIcon)
		.removeClass(this.elTitleIcon.prop('class'))
		.addClass(icon);

		return this;
	};

	setOS(os) {

		this.os = os;

		return this;
	};

	setApp(app) {

		this.app = app;

		if(app && app.os)
		this.setOS(app.os);

		return this;
	};

	setAppAndBake(app) {

		this.setApp(app);

		if(this.app) {
			this.setIdent(app.ident);
			this.setIcon(app.icon);
			this.setTitle(app.name);

			if(this.app.os)
			this.setOS(app.os);
		}

		return this;
	};

	setBody(content) {

		(this.elBody)
		.empty()
		.append(content);

		return this;
	};

	////////////////////////////////
	////////////////////////////////

	setResizable(enable) {

		let handles = this.element.find('.atl-dtop-win-resizehandle');

		////////

		this.enableResize = enable;

		if(enable)
		handles.removeClass('d-none');

		else
		handles.addClass('d-none');

		////////

		return this;
	};

	setMovable(enable) {

		this.enableMove = enable;

		return this;
	};

	setMaxable(enable) {

		let handles = this.element.find('[data-win-action="win-max"]');

		////////

		this.enableMax = enable;

		if(enable)
		handles.removeClass('d-none');

		else
		handles.addClass('d-none');

		return this;
	};

	setMinable(enable) {

		let handles = this.element.find('[data-win-action="win-min"]');

		////////

		this.enableMin = enable;

		if(enable)
		handles.removeClass('d-none');

		else
		handles.addClass('d-none');

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	isMaximised() {

		return this.element.hasClass('maximise');
	};

	isMinimised() {

		return this.element.hasClass('minimise');
	};

	isUserMoved() {

		return this.userMoved;
	};

	isUserSized() {

		return this.userSized;
	};

};

export default Window;
