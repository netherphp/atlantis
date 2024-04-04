
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class StackManagerConfig {

	constructor(autorun=true, datakey='sitemenu') {
		this.autorun = autorun;
		this.datakey = datakey;
		return;
	};

	static FromObject(obj) {

		let output = new StackManagerConfig;

		for(const key of Object.keys(obj))
		if(typeof output[key] !== undefined)
		output[key] = obj[key];

		return output;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class StackItem {

	constructor(item) {
		this.element = jQuery(item);
		this.bsapi = new bootstrap.Offcanvas(this.element);
		return;
	};

	isOpen() {

		return this.element.hasClass('show');
	};

	isVisible() {

		return (false
			|| this.element.hasClass('show')
			|| this.element.hasClass('showing')
		);
	};

	isHidden() {

		return !this.isVisible();
	};

	show() {

		if(!this.element.hasClass('show'))
		this.bsapi.show();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class StackManager {

	constructor(selector='#SiteMenu', conf={}) {

		console.log(`[SiteMenu.StackManager] ${selector}`);

		this.element = jQuery(selector);
		this.conf = StackManagerConfig.FromObject(conf);
		this.io = null;
		this.items = null;

		// the amount the deck is offset each time a new card from the
		// stack is displayed.

		this.xbase = 16;

		// the z-index to start at so that we can always have things on
		// top of things. this is actually defined as the max as the loop
		// processes the items backwards.

		this.zbase = 42999;

		// default method for causing the deck shifting is width. which
		// abuses the css variable in bs along with a haha with padding to
		// avoid resizing of content.

		this.mode = 'width';

		this.useBodyLockCSS = true;
		this.useBodyLockJS = false;

		////////

		if(this.conf.autorun)
		this.run();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setXBase(x) {

		this.xbase = x;
		return this;
	};

	setZBase(z) {

		this.zbase = z;
		return this;
	};

	setMode(mode) {

		this.mode = mode;
		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getStackIterator() {

		return Object.values(this.items);
	};

	getStackFromPath(path, prev=null) {

		return path.split('/');
	};

	restackItemAt(key) {

		let tmp = this.items[key];
		delete this.items[key];

		this.items[key] = tmp;

		return;
	};

	isBodyLongEnough() {

		return (jQuery('body').height() > jQuery(window).height());
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	bindStackItems(items) {

		this.unbindStackItems();

		this.items = items;
		this.io = new MutationObserver(
			(this.onStackItemClassChanged)
			.bind(this)
		);

		////////

		for(const i in this.items)
		this.io.observe(this.items[i].element[0], {
			attributes: true
		});

		////////

		jQuery(`[data-${this.conf.datakey}]`)
		.bind('click', this.onSiteMenuUnfold.bind(this));

		let closers = this.element.find(`[data-${this.conf.datakey}-close]`);
		closers.bind('click', this.onSiteMenuClose.bind(this));

		////////

		return items;
	};

	unbindStackItems() {

		if(this.io instanceof MutationObserver)
		this.io.disconnect();

		this.io = null;
		this.items = null;

		return;
	};

	findStackItems() {

		let output = { };

		////////

		(this.element)
		.find('> .offcanvas')
		.each((idx, el)=> output[el.id] = new StackItem(el));

		////////

		return output;
	};

	updateStackItems() {

		let items = this.getStackIterator().reverse();
		let one = false;

		let mult = 0;
		let padd = 0;
		let lite = 1.0;

		for(const item of items) {

			if(item.element.hasClass('hiding'))
			continue;

			if(!item.element.hasClass('showing'))
			if(!item.element.hasClass('show'))
			continue;

			one = true;
			padd = mult * this.xbase;
			mult += 1;

			if(this.mode === 'width') {
				item.element.css({
					'width': `calc(var(--bs-offcanvas-width) + ${padd}px)`,
					'padding-right': `${padd}px`,
					'filter': `brightness(${lite})`,
					'z-index': `${(this.zbase - mult)}`,
				});
			}

			lite -= 0.1;
		}

		this.backdrop(one);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	backdrop(enable) {
	/*//
	@date 2023-10-21
	toggle the self-managed backdrop on or off. does its best to prevent you
	from doing stupid so it should be safe to bash without stacking infinite
	layers.
	//*/

		let drops = this.element.find('.offcanvas-backdrop');
		let drop = null;

		if(enable === true && drops.length === 0) {
			this.element.append(
				drop = jQuery('<div />')
				.addClass('offcanvas-backdrop fade')
			);

			setTimeout(()=> drop.addClass('show'), 0);

			if(this.useBodyLockCSS) {
				if(this.isBodyLongEnough())
				jQuery('html').addClass('sitemenu-body-lock');
			}

			if(this.useBodyLockJS) {
				jQuery('body').attr('data-sitemenu-scroll-pos', jQuery(window).scrollTop());

				jQuery(window)
				.on('scroll.sitemenu', function(ev) {
					ev.preventDefault();

					window.scroll({
						top: 0, left: 0,
						behavior: 'instant'
					});

					return false;
				});
			}

			return;
		}

		if(enable === false) {
			drops.removeClass('show');

			if(this.useBodyLockCSS) {
				jQuery('html').removeClass('sitemenu-body-lock');
			}

			if(this.useBodyLockJS) {
				jQuery('body').removeAttr('data-sitemenu-scroll-pos');
				jQuery(window).off('scroll.sitemenu');
			}

			setTimeout(()=> drops.remove(), 200);
			return;
		}

		return;
	};

	unfold(id) {
	/*//
	@date 2023-10-21
	open given menu by id. the menu's data-sitemenu-path property can define
	the stack of menus that would have lead up to it had it been walked
	organically in string path form.
	//*/

		if(id.indexOf('/') >= 0)
		return this.open(id);

		if(typeof this.items[id] === 'undefined')
		throw `menu not found: ${id}`;

		////////

		let path = id;
		let item = this.items[id];
		let prefix = jQuery.trim(item.element.attr(`data-${this.conf.datakey}-path`));

		////////

		if(prefix !== '')
		path = `${prefix}/${id}`;

		this.update(this.getStackFromPath(path));

		return;
	};

	open(stack) {
	/*//
	@date 2023-10-21
	open the given stack of menus in string or array of form in the order
	they are are provided.
	//*/

		if(typeof stack === 'string')
		stack = this.getStackFromPath(stack);

		if(!Array.isArray(stack))
		throw 'why is stack not an array?';

		////////

		this.update(stack);

		return;
	};

	update(stack) {
	/*//
	@date 2023-10-21
	trigger the actual dom updates for the specified stack.
	//*/

		for(const key of stack)
		if(this.items[key] instanceof StackItem) {
			this.restackItemAt(key);

			if(this.items[key].isHidden())
			this.items[key].show();
		}

		// this is kind of an excess ping but allows for the surprise
		// reordering of the stuff.

		this.updateStackItems();

		return;
	};

	run() {
	/*//
	@date 2023-10-21
	execute the series of events that makes this widget begin operations.
	//*/

		this.bindStackItems(
			this.findStackItems()
		);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onStackItemClassChanged(muts, from) {

		let doeet = false;

		for(let mut of muts) {
			if(mut.type !== 'attributes')
			continue;

			if(mut.attributeName !== 'class')
			continue;

			doeet = true;
			break;
		}

		if(doeet)
		this.updateStackItems();

		return false;
	};

	onSiteMenuClose(jEv) {

		let parent = jEv.target.parentElement;

		while(parent) {
			if(parent.classList.contains('offcanvas'))
			break;

			parent = parent.parentElement;
		}

		if(parent && this.items[parent.id])
		this.items[parent.id].bsapi.hide();

		return false;
	};

	onSiteMenuUnfold(jEv) {

		let that = jQuery(jEv.currentTarget);
		let path = that.attr(`data-${this.conf.datakey}`);

		console.log(path);

		this.unfold(path);

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let Namespace = {
	Manager:       StackManager,
	ManagerConfig: StackManagerConfig,
	Item:          StackItem
};

export default StackManager;
