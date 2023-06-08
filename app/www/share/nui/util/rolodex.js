
class Rolodex {
/*//
@date 2023-03-31
this is a goofy dumb way to do a slide show system by constantly promoting dom
elements to the top of their stack.
//*/

	constructor(selector, delay=3000) {

		this.element = jQuery(selector);
		this.items = this.element.find('.FlipperItem');

		setInterval(this.next.bind(this), delay);

		return;
	};

	next() {

		this.element
		.prepend(
			this.element
			.find('.FlipperItem:last')
		);

		return;
	};
};

export default Rolodex;
