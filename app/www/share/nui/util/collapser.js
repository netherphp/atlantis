
/*
<div class="Collapser">
	<header>
		<div>
			Title
		</div>
		<span class="Indicator"><i class="mdi mdi-plus-thick"></i></span>
	</header>
	<section>
		Content
	</section>
</div>

jQuery(function(){

	jQuery('.Collapser')
	.each(function(){
		new Collapser(this);
		return;
	});

});
*/

class Collapser {

	constructor(element) {

		this.element = jQuery(element);
		this.header = this.element.find('header:first');
		this.content = this.element.find('section:first');

		////////

		if(!this.element.hasClass('Collapser'))
		this.element.addClass('Collapser');

		////////

		(this.header)
		.on('click', this.onClick.bind(this));

		return;
	};

	onClick() {

		this.element.toggleClass('Open');

		return false;
	};

	static Boot(selector) {

		let cdef = this.prototype;

		console.log(selector);

		jQuery(selector)
		.each(function(){
			new cdef.constructor(this);
			return;
		});

		return;
	};

	static WhenDocumentReady() {

		let cdef = this.prototype;

		jQuery('.atl-collapser')
		.each(function(){
			new cdef.constructor(this);
			return;
		});

		return;
	};

};

export default Collapser;
