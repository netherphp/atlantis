import API from '/share/nui/api/json.js';
import ModalDialog from '/share/nui/modules/modal/modal.js';

let DialogTemplate = `
<div class="row">
	<div class="col-12 mb-4">
		<div class="fw-bold text-uppercase">Search</div>
		<div class="mb-2">
			<select class="form-select eri-type-selector">
				<option value="any">All Profiles</option>
			</select>
		</div>
		<div class="mb-2">
			<input name="Query" type="text" class="form-control" />
		</div>
		<div class="TagsQuery"></div>
	</div>
	<div class="col-12">
		<div class="fw-bold text-uppercase">Currently Selected</div>
		<div class="TagsCurrent">
			<span class="btn btn-block btn-dark clickthru opacity-75 mb-2">
				<i class="mdi mdi-fw mdi-loading mdi-spin mr-2"></i>
				Loading
			</span>
		</div>
		<div class="TagsNew">

		</div>
	</div>
</div>
`;

let SearchResultTemplate = ``;

class Dialog
extends ModalDialog {

	constructor(entityUUID, tags, opt={}) {
		super(DialogTemplate);

		this.uuid = entityUUID;
		this.tags = tags;

		this.parentType = 'Profile.Entity';
		this.childType = 'Profile.Entity';
		this.parentChild = false;

		// list existing relationships.

		this.listVerb = 'RELGET';
		this.listURL = '/api/eri/entity';

		// add new relationship

		this.saveVerb = 'POST';
		this.saveURL = '/api/eri/entity';

		// search for new objects to relate to.

		this.searchVerb = 'SEARCH';
		this.searchURL = '/api/profile/entity';

		// check for filter sets to make searching easier.

		this.filtersVerb = 'FILTERS';
		this.filtersURL = '/api/profile/entity';

		////////

		this.bakeOptions(opt);

		if(this.childType !== 'Profile.Entity') {
			this.element.find('.eri-type-selector').remove();
		}

		this.addButton('Cancel', 'btn-dark', 'cancel');
		this.addButton('Save', 'btn-primary', 'accept');

		////////

		this.type = null;
		this.sort = 'title-az';

		this.query = this.element.find('input[name=Query]');
		this.querybin = this.element.find('.TagsQuery');
		this.tagbin = this.element.find('.TagsCurrent');
		this.newbin = this.element.find('.TagsNew');

		this.timerQuery = null;

		this.query.on('keyup', this.onSearchKeyPress.bind(this));

		////////

		// TODO lock ui

		let f1 = this.fetchCurrentTags();
		let f2 = this.fetchFilterTypes();

		Promise.all([f1, f2]).then(function(){
			// TODO unlock ui
			console.log('tee hee');
			return;
		});

		return;
	};

	fetchCurrentTags() {

		let self = this;

		return new Promise(function(next, fail) {

			let api = new API.Request(self.listVerb, self.listURL);

			(api.send({ UUID: self.uuid, Type: self.childType }))
			.then(self.onTagFetch.bind(self))
			.then(next)
			.catch(api.catch);

			return;
		});
	};

	fetchFilterTypes() {

		let self = this;

		return new Promise(function(next, fail) {

			let api = new API.Request(self.filtersVerb, self.filtersURL);

			(api.send({ UUID: self.uuid, Type: self.childType }))
			.then(self.onFilterFetch.bind(self))
			.then(next)
			.catch(api.catch);

			return;
		});
	};

	//#[Common\Meta\Date('2023-12-14')]
	bakeOptions(opt) {

		this.setTitle('Related Profiles');

		if(typeof opt.title === 'string')
		this.setTitle(opt.title);

		if(typeof opt.parentChild !== 'undefined')
		this.parentChild = opt.parentChild;

		if(typeof opt.parentType === 'string')
		this.parentType = opt.parentType;

		if(typeof opt.childType === 'string')
		this.childType = opt.childType;

		if(typeof opt.searchVerb === 'string')
		this.searchVerb = opt.searchVerb;

		if(typeof opt.searchURL === 'string')
		this.searchURL = opt.searchURL;

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onSearchKeyPress(ev) {

		if(this.timerQuery)
		clearTimeout(this.timerQuery);

		this.timerQuery = setTimeout(
			this.onSearchTrigger.bind(this),
			250
		);

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onSearchTrigger() {

		let val = jQuery.trim(this.query.val());
		let api = new API.Request(this.searchVerb, this.searchURL);

		(api.send({ Q: val, TagsAll: this.tags, Sort: this.sort }))
		.then(this.updateTagsQuery.bind(this))
		.catch(api.catch);

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onSearchTagClick(btn) {

		let self = this;
		let id = btn.attr('data-id');
		let uuid = btn.attr('data-uuid');

		(btn)
		.remove()
		.removeClass('btn-primary')
		.addClass('btn-dark mb-1')
		.off('click.tagdiag')
		.on('click.tagdiag', ()=> self.onTagClick(btn));

		(btn.find('.mdi-plus'))
		.removeClass('mdi-plus')
		.addClass('mdi-minus');

		this.tagbin.find('span').remove();
		this.tagbin.append(btn);

		this.query.val('');
		this.querybin.empty();

		this.query.focus();

		console.log(`[onSearchTagClick] ${id} ${uuid}`);

		return;
	};

	onTagFetch(result) {

		let output = jQuery('<div />');
		let self = this;

		if(!result.payload.length)
		return this.updateTagsNone();

		for(let tag of result.payload) {
			output.append(
				jQuery('<button />')
				.addClass('btn btn-dark btn-block ta-left tt-none mb-1')
				.attr('data-id', tag.ID)
				.attr('data-uuid', tag.UUID)
				.attr('data-title', tag.Title)
				.html(`<i class="mdi mdi-minus"></i> ${tag.Title}`)
				.on('click.tagdiag', function() {
					self.onTagClick(jQuery(this));
					return;
				})
			);
		}

		(this.tagbin)
		.empty()
		.append(output.children());

		this.query.focus();

		return;
	};

	onFilterFetch(result) {

		// populate filter dropdown with choices.

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onTagClick(btn) {

		let id = btn.attr('data-id');
		let uuid = btn.attr('data-uuid');

		btn.remove();

		if(this.tagbin.find('.btn').length === 0)
		this.updateTagsNone();

		console.log(`[onTagClick] ${id} ${uuid}`);
		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	updateTagsQuery(result) {

		let output = jQuery('<div />');
		let self = this;

		////////

		for(let item of result.payload) {
			let title = item.Title;

			if(item.AddressState && item.AddressCity)
			title = `${title} (${item.AddressCity}, ${item.AddressState})`;

			else if(item.AddressState)
			title = `${title} (${item.AddressState})`;

			output.append(
				jQuery('<div />')
				.addClass('row tight flex-nowrap mb-1')
				.append(
					jQuery('<div />')
					.addClass('col')
					.append(
						jQuery('<button />')
						.attr('data-id', item.ID)
						.attr('data-uuid', item.UUID)
						.addClass('btn btn-primary btn-block ta-left')
						.html(`<div class="tt-none"><i class="mdi mdi-plus"></i> ${title}</div>`)
						.on('click', function() {
							self.onSearchTagClick( jQuery(this) );
							return;
						})
					)
				)
				.append(
					jQuery('<div />')
					.addClass('col-auto')
					.append(
						jQuery('<a />')
						.attr('href', item.PageURL)
						.attr('target', '_blank')
						.addClass('btn btn-secondary')
						.html('<i class="mdi mdi-open-in-new"></i>')
					)
				)
			);
		}

		////////

		(this.querybin)
		.empty()
		.append(output.children());

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	updateTagsNone() {

		(this.tagbin)
		.empty()
		.append(
			jQuery('<span />')
			.addClass('btn btn-dark btn-block opacity-30 clickthru mb-2')
			.html('No profiles found')
		);

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onAccept() {

		let api = new API.Request(this.saveVerb, this.saveURL);

		let output = {
			ParentChild: this.parentChild ? 1 : 0,
			ParentType: this.parentType,
			ParentUUID: this.uuid,
			ChildType:  this.childType,
			ChildUUID:  []
		};

		let itids = 0;
		let inames = 0;

		(this.tagbin.find('button.btn'))
		.each(function(it) {
			console.log(it);

			output[`ChildUUID[${inames}]`] = this.dataset.uuid;

			inames += 1;
			return;
		});

		console.log(`profiles for ${this.uuid}: ${output.length}`);
		console.log(output);

		(api.send(output))
		.then(this.onDone)
		.catch(api.catch);

		return;
	};

	//#[Common\Meta\Date('2023-12-07')]
	onDone() {

		location.reload();

		return;
	};

};

export default Dialog;
