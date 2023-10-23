/*
<div class="swiper-supercontainer">
	<div id="CatSlideIssues" class="swiper">
		<div class="swiper-wrapper">
			<div class="swiper-slide">
				Slide Content Here
			</div>
			...
		</div>
		<div class="swiper-buttons">
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
		</div>
		<div class="swiper-pager">
			<div class="swiper-pagination"></div>
		</div>
	</div>
</div>
*/

let TemplateSliderButtons = `
<div class="swiper-buttons">
	<div class="swiper-button-prev"></div>
	<div class="swiper-button-next"></div>
</div>
`;

let TemplateSliderPager = `
<div class="swiper-pager">
	<div class="swiper-pagination"></div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Slider {

	constructor(selector, config={}) {

		this.setup = null;
		this.element = null;
		this.api = null;

		////////

		this.prepareConfig(config);
		this.prepareElement(selector);
		this.init();

		return;
	};

	prepareConfig(config) {

		if(typeof config !== 'object')
		config = { };

		////////

		this.setup = new SliderConfig(config);

		(this.setup)
		.setAfterInitFunc(this.onAfterInit.bind(this))
		.setPagerBulletFunc(null);

		return;
	};

	prepareElement(selector) {

		this.element = jQuery(selector);

		// make sure we have markup for nav if enabled.

		if(typeof this.setup.navigation === 'object') {
			if(this.element.find('.swiper-buttons').length === 0)
			this.element.append(TemplateSliderButtons);
		}

		// make sure we have markup for pager if enabled.

		if(typeof this.setup.pagination === 'object') {
			if(this.element.find('.swiper-pager').length === 0)
			this.element.append(TemplateSliderPager);

			this.setup.setPagerBulletFunc(this.onRenderBullet.bind(this));
		}

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	init() {

		this.api = new Swiper(this.element[0], this.setup);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onRenderBullet(i, cname) {

		let out = SliderPageBullet.Square(i, cname);

		return out;
	};

	onAfterInit() {

		// move some controls to be easier to style after they are all
		// rigged up to everything.

		if(this.element.parent().hasClass('swiper-supercontainer')) {
			//(this.element)
			//.find('.swiper-buttons')
			//.appendTo(this.element.parent());

			(this.element)
			.find('.swiper-pager')
			.appendTo(this.element.parent());
		}

		// and ready the element to be seen.

		this.element.removeClass('swiper-coldboot');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static FromElement(selector, config) {

		let setup = new SliderConfig(config);
		let out = new Slider(selector, setup);

		return out;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SliderConfig {

	constructor(overrides) {

		// managed by setSlideCount()
		this.slidesPerView = 1;
		this.breakpoints = null;

		// managed by setSlideGap()
		this.spaceBetween = 0;

		// self-managed substructures.
		this.on = null;
		this.navigation = null;
		this.pagination = null;

		(this)
		.prepareSubstructs()
		.setSlideGap()
		.setSlideCount()
		.setPagerShow()
		.setNavShow()
		.apply(overrides);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	apply(overrides={}) {

		if(typeof overrides !== 'object')
		return this;

		for(const key in overrides) {
			this[key] = overrides[key];
		}

		return this;
	};

	prepareSubstructs() {

		this.on = new SliderConfigEventStruct;
		this.navigation = new SliderConfigNavStruct;
		this.pagination = new SliderConfigPagerStruct;

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setSlideCount(max=3, min=1, mid=null) {

		if(min > max)
		[ min, max ] = [ max, min ];

		min = Math.max(min, 1);
		max = Math.min(max, 16);
		mid = (
			mid === null
			? (Math.round((max - min) / 2) + min)
			: (Math.max(min, Math.min(max, mid)))
		);

		console.log(`[SlideCount] max: ${max}, mid: ${mid}, min: ${min}`);

		////////

		this.slidesPerView = min;

		this.breakpoints = {
			576: { slidesPerView: mid },
			768: { slidesPerView: max }
		};

		return this;
	};

	setSlideGap(dist=24) {

		this.spaceBetween = dist;

		return this;
	};

	setNavShow(enable=true) {

		this.navigation.setEnabled(enable);

		return this;
	};

	setPagerShow(enable=true) {

		this.pagination.setEnabled(enable);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setAfterInitFunc(func) {

		this.on.setAfterInitFunc(func);

		return this;
	};

	setPagerBulletFunc(func) {

		this.pagination.setBulletFunc(func);

		return this;
	};

};

class SliderConfigEventStruct {

	constructor() {

		// values swiper uses.
		this.afterInit = null;

		return;
	};

	setAfterInitFunc(func) {

		this.afterInit = null;

		if(typeof func === 'function')
		this.afterInit = func;

		return this;
	};

};

class SliderConfigNavStruct {

	constructor() {

		// values swiper uses.
		this.nextEl = null;
		this.prevEl = null;

		// our own values.
		this.nextSelector = null;
		this.prevSelector = null;

		(this)
		.setNextSelector()
		.setPrevSelector()
		.setEnabled();

		return;
	};

	setEnabled(enable=true) {

		if(enable) {
			this.nextEl = this.nextSelector;
			this.prevEl = this.prevSelector;
		}

		else {
			this.nextEl = false;
			this.prevEl = false;
		}

		return this;
	};

	setNextSelector(selector='.swiper-button-next') {

		this.nextSelector = selector;

		return this;
	};

	setPrevSelector(selector='.swiper-button-prev') {

		this.prevSelector = selector;

		return this;
	};

};

class SliderConfigPagerStruct {

	constructor() {

		// values swiper uses.
		this.el = false;
		this.clickable = false;
		this.renderBullet = null;

		(this)
		.setSelector()
		.setBulletFunc(SliderPageBullet.Default)
		.setEnabled();

		return;
	};

	setEnabled(enable=true) {

		if(enable) {
			this.el = this.selector;
			this.clickable = true;
		}

		else {
			this.el = false;
			this.clickable = false;
		}

		return this;
	};

	setSelector(selector='.swiper-pagination') {

		this.selector = selector;

		return this;
	};

	setBulletFunc(func) {

		this.renderBullet = null;

		if(typeof func === 'function')
		this.renderBullet = func;

		return this;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SliderPageBullet {

	static Default(i, cname) {
		return `<span class="${cname} swiper-pager-bullet-circle" data-slide-key="${i}"></span>`;
	};

	static Square(i, cname) {
		return `<span class="${cname} swiper-pager-bullet-square" data-slide-key="${i}"></span>`;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let Namespace = {
	Slider:      Slider,
	Config:      SliderConfig,
	PageBullet:  SliderPageBullet,
	FromElement: Slider.FromElement.bind(null)
};

export default Namespace;
