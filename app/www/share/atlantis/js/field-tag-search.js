import API from '/share/nui/api/json.js';
import Util from '/share/nui/util.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateSearchBin = `
<div class="atl-field-tag-search-results pos-relative">
	<div class="position-absolute pos-bottom pos-left w-100">
		<div class="row tight flex-wrap"></div>
	</div>
</div>
`;

let TemplateSearchItem = `
<div class="col-auto mb-2">
	<button class="btn btn-primary is-an-item"></button>
</div>
`;

let TemplateSelectBin = `
<div class="atl-field-tag-search-selected pt-2">
	<div class="row tight flex-wrap"></div>
</div>
`;

let TemplateSelectItem = `
<div class="col-auto mb-2">
	<button class="btn btn-outline-primary is-an-item"></button>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class FieldTagSearch {

	constructor(el) {

		this.searchVerb = 'SEARCH';
		this.searchURL = '/api/tag/entity';
		this.searchDelayMS = 250;

		////////

		if(typeof el === 'string')
		el = jQuery(el);

		// elements.
		this.element = el;
		this.elSubtagParent = null;
		this.searchBin = null;
		this.selectBin = null;

		// misc.
		this.searchTimer = null;

		////////

		this.initDocObjects();
		this.initDocEvents();

		return;
	};

	initDocObjects() {

		this.elSubtagParent = jQuery(
			this.element.attr('data-subtag-parent')
		);

		console.log(this.element.attr('data-subtag-parent'));
		console.log(this.elSubtagParent);

		if(this.elSubtagParent.length === 0)
		this.elSubtagParent = null;

		else
		this.elSubtagParent.on('change', this.onSearchCommit.bind(this));

		////////

		this.selectBin = jQuery(TemplateSelectBin);
		this.selectBin.addClass('d-none');
		this.element.after(this.selectBin);

		this.searchBin = jQuery(TemplateSearchBin);
		this.searchBin.addClass('d-none');
		this.element.before(this.searchBin);

		return;
	};

	initDocEvents() {

		this.element.on('keyup', this.onSearchKeyUp.bind(this));

		return;
	};

	disarmSearchTimer() {

		if(this.searchTimer === null)
		return;

		clearInterval(this.searchTimer);
		this.searchTimer = null;

		return;
	};

	resetSearchTimer() {

		this.disarmSearchTimer();

		this.searchTimer = setTimeout(
			this.onSearchCommit.bind(this),
			this.searchDelayMS
		);

		return;
	};

	getSearchQuery() {

		return jQuery.trim(this.element.val());
	};

	getSelectedTags() {

		let tags = [];

		// tags that aleady exist.

		(this.selectBin.find('.row button[data-tag-id]'))
		.each(function(){
			let that = jQuery(this);
			tags.push(parseInt(that.attr('data-tag-id')));
			return;
		});

		return tags;
	};

	getNewTags() {

		let tags = [];

		// tags we are inventing now.

		(this.selectBin.find('.row button[data-tag-name]'))
		.each(function(){
			let that = jQuery(this);
			tags.push(that.attr('data-tag-name'));
			return;
		});

		return tags;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onSearchKeyUp() {

		this.resetSearchTimer();

		return;
	};

	onSearchCommit() {

		let self = this;
		let api = new API.Request(this.searchVerb, this.searchURL);
		let data = { 'Query': this.getSearchQuery(), 'Type': 'tag' };

		if(this.elSubtagParent !== null)
		data.ParentID = parseInt(this.elSubtagParent.val() || 0);

		(api.send(data))
		.then(function(result) {
			self.prepareSearchBin(result);

			for(let tag of result.payload.Tags) {
				let row = self.prepareSearchItem(tag);

				if(self.hasInSelectBin(tag.ID)) {
					self.toggleSearchButtonState(row);
				}

				self.addToSearchBin(row);
			}

			////////

			if(self.searchBin.find(`button[data-tag-match="${result.payload.Query.toLowerCase()}"]`).length === 0) {
				let row = self.prepareSearchNew(result);
				self.addToSearchBin(row);
			}

			self.toggleSearchBin();

			return;
		})
		.catch(api.catch);

		return;
	};

	onSearchItemSelect(btn) {

		let self = this;
		let tid = btn.attr('data-tag-id');
		let row = null;

		// do not add duplicate buttons for tags we have already selected.

		if(this.selectBin.find(`[data-tag-id=${tid}]`).length > 0)
		return;

		// create a new button item for the box of selected tags.

		row = jQuery(TemplateSelectItem);

		(row.find('button'))
		.attr('data-tag-id', tid)
		.text(btn.text())
		.on('click', function() {
			self.onSelectItemRemove(jQuery(this));
			return false;
		});

		this.addToSelectBin(row);

		// change the state of the search button to show it has been
		// selected.

		btn.toggleClass('btn-primary btn-outline-primary');

		this.element.val('');
		(self.searchBin.find('.mdi-close').parent()).trigger('click');

		return;
	};

	onSearchItemNew(btn) {

		let self = this;
		let row = jQuery(TemplateSelectItem);
		let tname = btn.attr('data-tag-name');

		row.find('button')
		.attr('data-tag-name', tname)
		.text(`+ ${tname}`)
		.on('click', function() {
			self.onSelectItemRemove(jQuery(this));
			return false;
		});

		btn.parent().remove();

		this.selectBin.find('.row').append(row);
		this.toggleSelectBin();
		this.toggleSearchBin();

		return;
	};

	onSelectItemRemove(btn) {

		// remove the parent holding the button.

		btn.parent().remove();

		// hide or show the drawer.

		this.toggleSelectBin();

		return;
	};

	prepareSearchBin(result) {

		let self = this;

		(this.searchBin.find('.row'))
		.empty()
		.append(`<div class="col fw-bold">RESULTS (${result.payload.Total}):</div>`)
		.append('<div class="col-auto fw-bold"><button class="btn btn-dark px-3 py-1"><i class="mdi mdi-close mr-0"></i></button></div>')
		.append('<div class="col-12 mb-2">');

		(self.searchBin.find('.mdi-close').parent())
		.on('click', function(){
			self.searchBin.find('.row').empty();
			self.toggleSearchBin();
			return;
		});

		return;
	};

	prepareSearchItem(tag) {

		let self = this;
		let row = jQuery(TemplateSearchItem);

		row.find('button')
		.attr('data-tag-id', tag.ID)
		.attr('data-tag-match', tag.Name.toLowerCase())
		.text(tag.Name)
		.on('click', function() {
			self.onSearchItemSelect(jQuery(this));
			self.toggleSearchBin();
			return false;
		});

		return row;
	};

	prepareSearchNew(result) {

		let self = this;
		let row = jQuery(TemplateSearchItem);

		row.find('button')
		.toggleClass('btn-primary btn-secondary')
		.attr('data-tag-name', result.payload.Query)
		.text(`+ ${result.payload.Query}`)
		.on('click', function() {
			self.onSearchItemNew(jQuery(this));
			return false;
		});

		return row;
	};

	addToSearchBin(row) {

		(this.searchBin.find('.row'))
		.append(row);

		return;
	};

	addToSelectBin(row) {

		(this.selectBin.find('.row'))
		.append(row);

		this.toggleSelectBin();

		return;
	};

	hasInSearchBin(tid) {

		return this.searchBin.find(`[data-tag-id=${tid}]`).length > 0;
	};

	hasInSelectBin(tid) {

		return this.selectBin.find(`[data-tag-id=${tid}]`).length > 0;
	};

	toggleSearchBin() {

		if(this.searchBin.find('.row button.is-an-item').length === 0) {
			this.searchBin.addClass('d-none');
			return;
		}

		this.searchBin.removeClass('d-none');

		return;
	};

	toggleSearchButtonState(row) {

		(row.find('button'))
		.toggleClass('btn-primary btn-outline-primary');

		return;
	};

	toggleSelectBin() {

		if(this.selectBin.find('.row button.is-an-item').length === 0) {
			this.selectBin.addClass('d-none');
			return;
		}

		this.selectBin.removeClass('d-none');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static New(el) {

		let that = jQuery(el);
		let old = that.data('FieldTagSearch');

		if(typeof old === 'object')
		return old;

		////////

		let search = new FieldTagSearch(that);

		that.data('FieldTagSearch', search);

		return search;
	};

	static OnDocReady() {

		jQuery('.atl-field-tag-search')
		.each(function(){ FieldTagSearch.New(this); });

		return;
	};

	static FieldHTML() {

		let out = `
			<input type="text" class="form-control atl-field-tag-search" placeholder="Tag Search..." />
		`;

		return out;
	};

	static TopicSelectorHTML(topics) {

		let out = '';

		out += '<select class="form-select">';
		out += '</select>';

		return out;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default FieldTagSearch;
