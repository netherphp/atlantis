
class AtlDropSorter {

	constructor(selector) {

		this.element = jQuery(selector);
		this.items = this.element.find('.atl-dropsort-item');
		this.btnSave = this.element.find('.atl-dropsort-cmd-save');

		this.currentItem = null;
		this.currentRow = null;

		this.bindDragDropElements();

		console.log(`[AtlDropSorter] ${this.element.attr('id')}`);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	bindDragDropElements() {

		let self = this;

		// drag drop any row handle onto any other row handle.

		(this.items.find('.atl-dropsort-item-handle'))
		.on('dragstart', function(ev) {
			return self.OnItemDragStart(ev.originalEvent, jQuery(this));
		})
		.on('dragend', function(ev) {
			return self.OnItemDragEnd(ev.originalEvent, jQuery(this));
		})
		.on('dragenter', function(ev) {
			return self.OnItemDragEnter(ev.originalEvent, jQuery(this));
		})
		.on('dragover', function(ev) {
			return self.OnItemDragOver(ev.originalEvent, jQuery(this));
		})
		.on('dragleave', function(ev) {
			return self.OnItemDragLeave(ev.originalEvent, jQuery(this));
		})
		.on('drop', function(ev) {
			return self.OnItemDrop(ev.originalEvent, jQuery(this));
		});

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	OnItemDragStart(ev, itm) {

		let id = itm.attr('data-id');
		let row = this.element.find(`.atl-dropsort-item[data-id=${id}]`);
		let rpos = { x: row.width(), y: (row.height() / 2) };

		////////

		this.btnSave.removeClass('btn-success');
		this.btnSave.addClass('btn-primary');

		ev.dataTransfer.effectAllowed = 'move';
		ev.dataTransfer.dropEffect = 'move';
		ev.dataTransfer.setData('text/plain', '');
		ev.dataTransfer.setDragImage(row.get(0), rpos.x, rpos.y);

		itm.css('opacity', '0.4');

		this.currentItem = itm;
		this.currentRow = row;

		////////

		console.log(`drag start ${id}`);

		return;
	};

	OnItemDragEnd(ev, itm) {

		let id = itm.attr('data-id');
		let row = this.element.find(`.atl-dropsort-item[data-id=${id}]`);

		////////

		itm.css('opacity', '1.0');

		this.currentItem = null;
		this.currentRow = null;

		////////

		console.log(`drag end ${id}`);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	OnItemDragEnter(ev, itm) {
		ev.preventDefault();

		let id = itm.attr('data-id');
		let row = this.element.find(`.atl-dropsort-item[data-id=${id}]`);

		row.addClass('droptarget');

		return true;
	};

	OnItemDragLeave(ev, itm) {
		ev.preventDefault();

		let id = itm.attr('data-id');
		let row = this.element.find(`.atl-dropsort-item[data-id=${id}]`);

		row.removeClass('droptarget');

		return true;
	};

	OnItemDragOver(ev, itm) {

		ev.preventDefault();
		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	OnItemDrop(ev, itm) {

		let id = itm.attr('data-id');
		let row = this.element.find(`.atl-dropsort-item[data-id=${id}]`);

		let cpos = this.currentRow.position().top;
		let dpos = row.position().top;

		////////

		// checking if we are dragging from above or below the item seems
		// to be working just fine for doing a natural feeling "drop it in
		// this spot" check.

		if(cpos > dpos)
		row.before(this.currentRow);
		else
		row.after(this.currentRow);

		////////

		(this.items)
		.removeClass('droptarget');

		(this.currentRow)
		.addClass('moved');

		ev.preventDefault();

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	get() {

		let output = [];

		(this.items)
		.each(function() {
			output.push(jQuery(this).attr('data-id'));
			return;
		});

		return output;
	}

	lock(update=true) {

		(this.element)
		.css('pointer-events', 'none');

		(this.items)
		.addClass('o-30');

		if(update)
		this.update();

		return this.get();
	}

	update() {

		let num = 1;

		// get a freshly ordered list.

		this.items = this.element.find('.atl-dropsort-item');

		// update the ui.

		(this.items)
		.each(function() {
			let that = jQuery(this);

			that.find('.atl-dropsort-item-num').text(num);

			num += 1;
			return;
		});

		return;
	}

	unlock() {

		let self = this;
		let offset = 50;
		let iter = 0;

		(this.items)
		.each(function() {
			let that = jQuery(this);

			setTimeout(
				(()=> that.removeClass('droptarget moved o-30')),
				(iter * offset)
			);

			iter += 1;

			if(iter === self.items.length) {
				setTimeout(
					function(){
						self.btnSave.removeClass('btn-primary');
						self.btnSave.addClass('btn-success');
						return;
					},
					(iter * offset)
				);
			}

			return;
		});

		(this.element)
		.css('pointer-events', 'all');

		return;
	}

};

export default AtlDropSorter;
