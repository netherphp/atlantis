
/*

this class provides a fading slide show type widget function. given a structure like
the following it will reformat them to be a slide deck that fade from one to the
next in a looping manner.

<div class="Fader" style="display:none;">
	<h1 class="FaderItem" data-fader-wallpaper="[optional]">One</h1>
	<h1 class="FaderItem" data-fader-wallpaper="[optional]">Two</h1>
	<h1 class="FaderItem" data-fader-wallpaper="[optional]">Three</h1>
	<h1 class="FaderItem" data-fader-wallpaper="[optional]">Four</h1>
</div>

if a FaderItem has a data-fader-wallpaper attribute then it will be rewrapped a little
more so that that image can be used as the background for that specific slide.

*/

function MergeProperties(Dom, sub) {
/*//
@argv object Override, object Original
will overwrite the original properties from the original object with
the properties from the Override object. of course, if they dont exist
in the Original they will be created too.
//*/

	if(typeof Dom !== 'object')
	return;

	jQuery.each(Dom,function(Prop, Val){
		sub[Prop] = Val;
		return;
	});

	return;
};

class Fader {

	constructor(Opt) {
	/*//
	@date 2021-02-23
	//*/

		this.Config = {
			'SelectorElement': '.Fader',
			'SelectorItems': '> .FaderItem',
			'Time': 3000,
			'Auto': true,
			'Height': 50,
			'Auto': true
		};

		MergeProperties(Opt,this.Config);

		this._Init();
		return;
	};

	_Init() {
	/*//
	@date 2021-02-23
	//*/

		this.Element = null;
		this.Pager = null;
		this.Interval = null;
		this.Current = -1;
		this.AutoTimeout = null;

		this._Init_InitItems();
		this._Init_WrapItems();
		this._Init_InitPager();
		this._Init_InitHover();
		this._Init_MakeVisible();

		if(this.Config.Auto)
		this.Begin();

		return;
	};

	_Init_InitItems() {
	/*//
	@date 2021-02-23
	build our references to the fader root element and its items.
	0
	//*/

		// find the root element.

		this.Element = (
			(this.Config.SelectorElement instanceof jQuery)?
			(this.Config.SelectorElement):(jQuery(this.Config.SelectorElement))
		);

		console.log(`Fader.Init: ID ${this.Element.attr('id')}`);

		// find the items.

		this.Items = this.Element.find(this.Config.SelectorItems);

		console.log(`Fader.Init: Item Count ${this.Items.length}`);

		return;
	};

	_Init_WrapItems() {
	/*//
	@date 2021-02-23
	creates a wrapper and moves the items into it that creates the
	stacking effect of the slides.
	//*/

		this.Items
		.each(function(){

			let Container = (
				jQuery('<div />')
				.addClass('FaderItemContain')
				.addClass('FaderItemOut')
			);

			// handle wallpapering the slide.

			let ImageURL = jQuery(this).attr('data-fader-wallpaper');
			let ImageBlur = jQuery(this).attr('data-fader-wallpaper-blur');
			let ImagePos = jQuery(this).attr('data-fader-wallpaper-pos') || 'center center';

			if(typeof ImageURL === 'string' && ImageURL.length > 0) {
				let Wallpaper = (
					jQuery('<div />')
					.addClass('position-absolutely wallpapered')
					.css({
						'background-image': `url(${ImageURL})`,
						'background-size': 'cover',
						'background-position': ImagePos
					})
				);

				if(ImageBlur)
				Wallpaper.css({
					'filter': `blur(${ImageBlur})`,
					'transform': `scale(1.1)`
				});

				Container.prepend(Wallpaper);
			}

			// put the container after the item.

			jQuery(this)
			.after(Container);

			// move the item into the container;

			Container
			.append(jQuery(this));

			return;
		});

		return;
	};

	_Init_InitPager() {
	/*//
	@date 2022-03-08
	create the pagination element and bullets.
	//*/

		this.Pager = (
			jQuery('<div />')
			.addClass('FaderPager')
		);

		let Iter = 0;

		for(const Item of this.Items) {
			this.Pager.append(
				jQuery('<div />')
				.addClass('FaderPagerBullet')
				.on('click', this.Goto.bind(this, Iter))
			);

			Iter += 1;
		}

		this.Element
		.addClass('FaderPaginates')
		.append(this.Pager);

		return;
	}

	_Init_InitHover(){
	/*//
	@date 2022-03-08
	handle hovering pausing auto.
	//*/

		let self = this;

		(this.Element)
		.on('mouseover', function(){

			if(self.AutoTimeout) {
				clearInterval(self.AutoTimeout);
				self.AutoTimeout = null;
			}

			return;
		})
		.on('mouseleave', function(){

			if(self.Config.Auto) {
				self.AutoTimeout = setTimeout(
					(self.Next).bind(self),
					self.Config.Time
				);
			}

			return;
		});

		return;
	};

	_Init_MakeVisible() {
	/*//
	@date 2021-02-23
	if the element was cheesed to not show until it was ready then
	clear that stuff up now.
	//*/

		this.Element
		.removeClass('d-none')
		.css('display', 'initial');

		return;
	};

	Begin() {
	/*//
	@date 2021-02-23
	lets go alreadayyyyyyyyyyy
	//*/

		this.Next();
		return;
	};

	Goto(Iter) {
	/*//
	@date 2022-03-08
	goto a specific item.
	//*/

		this.Current = Iter - 1;
		this.Next();

		return;
	};

	Next() {
	/*//
	@date 2021-02-23
	switch to the next item manually.
	//*/

		// clear the timeout for reset later.

		if(this.AutoTimeout) {
			clearInterval(this.AutoTimeout);
			this.AutoTimeout = null;
		}

		// determine the next slide.

		this.Current = ((this.Current + 1) % this.Items.length);

		// all of the slides need to be hidden except the one slide that
		// needs to be displayed.

		(this.Items)
		.parent()
		.removeClass('FaderItemIn')
		.addClass('FaderItemOut');

		(this.Items)
		.eq(this.Current)
		.parent()
		.addClass('FaderItemIn')
		.removeClass('FaderItemOut');

		// some resize code but i don't remember why.

		if(this.Element.hasClass('FaderResizes')) {
			let Height = this.Items.eq(this.Current).outerHeight() + this.Config.Height;
			console.log(`Fader.Next: FaderResizes ${Height}`);

			this.Element.parent().css('min-height',`${Height}px`);
		}

		// handle the pager update.

		if(this.Pager !== null) {
			this.Pager
			.find('.Active')
			.removeClass('Active');

			this.Pager
			.find(`:nth-of-type(${this.Current+1})`)
			.addClass('Active');
		}

		// handle allowing auto advance.

		if(this.Config.Auto)
		this.AutoTimeout = setTimeout((this.Next).bind(this), this.Config.Time);

		return;
	};

};

export default Fader;
