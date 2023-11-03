
class NumberRoller {

	constructor(Item) {
	/*//
	@date 2021-05-04
	//*/

		if(Item instanceof jQuery)
		this.Item = Item;
		else
		this.Item = jQuery(Item);

		this.Begin = parseInt(this.Item.attr('data-nr-begin'));
		this.End = parseInt(this.Item.attr('data-nr-end'));
		this.Delay = parseInt(this.Item.attr('data-nr-delay')) || 25;
		this.Step = parseInt(this.Item.attr('data-nr-step')) || 1;
		this.Format = parseInt(this.Item.attr('data-nr-format')) || 0;
		this.Formatter = new Intl.NumberFormat('en-US');
		this.Current = this.Begin;

		return;
	};

	Run() {
	/*//
	@date 2021-05-04
	//*/

		setTimeout(
			this.Iter.bind(this),
			this.Delay
		);

		return;
	};

	Iter() {
	/*//
	@date 2021-05-04
	//*/

		this.Current += this.Step;

		if(this.Current > this.End)
		this.Current = this.End;

		if(this.Format)
		this.Item.text(this.Formatter.format(this.Current));
		else
		this.Item.text(this.Current);

		if(this.Current < this.End)
		setTimeout(this.Iter.bind(this),this.Delay);

		return;
	}

	static Find(ParentSelector='body', ChildSelector='.NumberRoller') {
	/*//
	@date 2021-05-04
	find all the things that should get number rolled and give us an
	array of all of them.
	//*/

		let Found = new Array;
		let Container = jQuery(ParentSelector);

		Container
		.find(ChildSelector)
		.each((Iter,Item)=> Found.push(new NumberRoller(Item)));

		return Found;
	};

}

export default NumberRoller;
