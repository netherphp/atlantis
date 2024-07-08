
class Item {

	/**
	 * @param {Object}                    argv
	 * @param {string|jQuery|HTMLElement} argv.selector
	 * @param {string|null}               argv.text
	 * @param {string|null}               argv.cursor
	 * @param {boolean}                   argv.cursorKeep
	 */
	constructor({ selector, text=null, cursor='|', cursorKeep=false }) {

		this.element = jQuery(selector);
		this.text = text ?? this.element.attr('data-title');

		this.cursor = cursor;
		this.cursorKeep = cursorKeep;

		this.firstWait = 750;
		this.typeWait = 75;
		this.afterWait = 750;

		////////

		this.msgbox = null;
		this.cursorbox = null;

		return;
	};

	/**
	 * @param {function} then
	 * @return {Item}
	 */
	typeifyThen(then) {

		let self = this;
		let chars = this.text.split('');
		let c = 0;
		let loop = null;
		let after = null;

		////////

		loop = function() {
			let msg = chars.slice(0, c++).join('');
			let wait = (c === 1 ? self.firstWait : self.typeWait);

			self.msgbox.html(msg);

			// if we are not done then stage up the next iteration.
			// i have learned my lessons regarding this in skyrim.

			if(c <= chars.length)
			return setTimeout(loop, wait);

			// however if we are then then stage up the promise to be
			// finally fullfilled after the specified duration.

			return setTimeout(after, self.afterWait);
		};

		after = function() {
			if(!self.cursorKeep)
			self.cursorbox.remove();

			then();
			return;
		};

		////////

		(this.element)
		.append(
			(this.msgbox = jQuery('<span />'))
			.addClass('typeify-message')
		)
		.append(
			(this.cursorbox = jQuery('<span />'))
			.addClass('typeify-cursor')
			.attr('data-typeify-cursor', this.cursor)
			.html('&ZeroWidthSpace;')
		);

		if(!this.cursor)
		this.cursorbox.addClass('d-none');

		////////

		loop();

		return this;
	};

	/**
	 * @return {Promise}
	 */
	run() {

		return new Promise(
			this.typeifyThen.bind(this)
		);
	};

};

class Typeify {

	/**
	 * @param {Object}                    argv
	 * @param {string|jQuery|HTMLElement} argv.selector
	 * @param {string}                    argv.cursor
	 * @param {boolean}                   argv.cursorKeep
	 * @param {boolean|Number}            argv.autorun
	 */
	constructor({ selector, cursor='|', cursorKeep=false, autorun=false }) {

		this.element = jQuery(selector);
		this.id = this.element.attr('id');

		this.items = [];
		this.cursor = cursor;
		this.cursorKeep = cursorKeep;

		////////

		console.log(this);

		if(autorun !== false && autorun !== null)
		this.autorun(autorun);

		return;
	};

	/**
	 * @param {Number} delayMs
	 * @return {Typeify}
	 */
	autorun(delayMs) {

		delayMs = parseInt(delayMs);
		console.log(`Typeify(${this.id}) autorun (delay: ${delayMs}ms)`);

		setTimeout(
			(()=> this.fetchItems().run()),
			delayMs
		);

		return this;
	};

	/**
	 * @return {Typeify}
	 */
	fetchItems() {

		let self = this;

		////////

		this.items = [];

		(this.element.find('[data-typeify]'))
		.each(function() {

			self.items.push(new Item({
				selector:   this,
				cursor:     self.cursor,
				cursorKeep: false
			}));

			return;
		});

		console.log(`Typeify(${this.id}) Found ${this.items.length} data-typeify Items`);

		return this;
	};

	/**
	 * @return {Typeify}
	 */
	setCursor(val) {

		for(let item of this.items)
		item.cursor = val;

		return this;
	};

	/**
	 * @return {Typeify}
	 */
	setCursorKeep(val) {

		let item = null;

		for(item of this.items)
		item.cursorKeep = false;

		////////

		if(item)
		item.cursorKeep = val;

		return this;
	};

	/**
	 * @return void
	 */
	async run() {

		this
		.setCursor(this.cursor)
		.setCursorKeep(this.cursorKeep);

		////////

		for(const item of this.items)
		await item.run();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export { Typeify, Item };
export default Typeify;
