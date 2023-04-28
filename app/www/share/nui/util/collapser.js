
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

		return;
	};

};

export default Collapser;
