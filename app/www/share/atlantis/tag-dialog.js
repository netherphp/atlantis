import API from '/share/nui/api/json.js';
import ModalDialog from '/share/nui/modules/modal/modal.js';

let TagDialogTemplate = `
<div class="row">
	<div class="col-12 mb-4">
		<div class="fw-bold text-uppercase">Search</div>
		<div class="mb-2">
			<input name="Query" type="text" class="form-control" />
		</div>
		<div class="TagsQuery"></div>
	</div>
	<div class="col-12">
		<div class="fw-bold text-uppercase">Current Tags</div>
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

class TagDialog
extends ModalDialog {

	constructor(entityUUID, tagLinkType) {
		super(TagDialogTemplate);

		this.setTitle('Tags');
		this.addButton('Cancel', 'btn-dark', 'cancel');
		this.addButton('Save', 'btn-primary', 'accept');

		this.uuid = entityUUID;
		this.type = tagLinkType;

		this.query = this.element.find('input[name=Query]');
		this.querybin = this.element.find('.TagsQuery');
		this.tagbin = this.element.find('.TagsCurrent');
		this.newbin = this.element.find('.TagsNew');

		this.timerQuery = null;

		this.query.on('keyup', this.onSearchKeyPress.bind(this));

		let api = new API.Request('TAGSGET', '/api/media/entity');

		(api.send({ EntityUUID: this.uuid, Type: this.type }))
		.then(this.onTagFetch.bind(this))
		.catch(api.catch);

		return;
	};

	onSearchKeyPress(ev) {

		if(this.timerQuery)
		clearTimeout(this.timerQuery);

		this.timerQuery = setTimeout(
			this.onSearchTrigger.bind(this),
			250
		);

		return;
	};

	onSearchTrigger() {

		let val = jQuery.trim(this.query.val());
		let api = new API.Request('SEARCH', '/api/media/tag');

		(api.send({ Query: val }))
		.then(this.updateTagsQuery.bind(this))
		.catch(api.catch);

		return;
	};

	onSearchTagClick(btn) {

		let tid = btn.attr('data-tag-id');

		btn
		.remove()
		.removeClass('btn-primary')
		.addClass('btn-secondary text-transform-none')
		.off('click.tagdiag')
		.on('click.tagdiag', ()=> this.onTagClick(btn));

		btn.find('.mdi-plus')
		.removeClass('mdi-plus')
		.addClass('mdi-minus');

		this.tagbin.find('span').remove();
		this.tagbin.append(btn);

		this.query.val('');
		this.querybin.empty();

		console.log(`[onSearchTagClick] tag remove: ${tid}`);
		return;
	};

	onTagFetch(result) {

		let output = jQuery('<div />');
		let self = this;

		if(!result.payload.Tags.length)
		return this.updateTagsNone();

		for(let tag of result.payload.Tags) {
			output.append(
				jQuery('<button />')
				.addClass('btn btn-dark text-transform-none mb-2 mr-2')
				.attr('data-tag-id', tag.ID)
				.attr('data-tag-key', tag.Name.toLowerCase())
				.attr('data-tag-name', tag.Name)
				.html(`<i class="mdi mdi-fw mdi-minus"></i> ${tag.Name}`)
				.on('click.tagdiag', function() {
					self.onTagClick(jQuery(this));
					return;
				})
			);
		}

		(this.tagbin)
		.empty()
		.append(output.children());

		return;
	};

	onTagClick(btn) {

		let tid = btn.attr('data-tag-id');

		btn.remove();

		if(this.tagbin.find('.btn').length === 0)
		this.updateTagsNone();

		console.log(`[onTagClick] tag remove: ${tid}`);
		return;
	};

	updateTagsQuery(result) {

		let output = jQuery('<div />');
		let self = this;
		let query = jQuery.trim(this.query.val());
		let querybin = jQuery('<div />');

		////////

		for(let tag of result.payload.Tags) {
			if(this.tagbin.find(`[data-tag-id=${tag.ID}]`).length > 0)
			continue;

			let styleclass = 'btn-primary';

			if(tag.Type === 'site')
			styleclass = 'btn-danger';

			output.append(
				jQuery('<button />')
				.addClass(`btn ${styleclass} text-transform-none mb-2 mr-2`)
				.attr('data-tag-id', tag.ID)
				.attr('data-tag-key', tag.Name.toLowerCase())
				.attr('data-tag-name', tag.Name)
				.html(`<i class="mdi mdi-fw mdi-plus"></i> ${tag.Name}`)
				.on('click.tagdiag', function() {
					self.onSearchTagClick(jQuery(this));
					return;
				})
			);
		}

		console.log(query);

		if(output.find(`[data-tag-key="${query.toLowerCase()}"]`).length === 0)
		if(this.tagbin.find(`[data-tag-key="${query.toLowerCase()}"]`).length === 0)
		output.append(
			jQuery('<button />')
			.addClass('btn btn-secondary text-transform-none mb-2 mr-2')
			.attr('data-tag-id', 'null')
			.attr('data-tag-key', query.toLowerCase())
			.attr('data-tag-name', query)
			.html(`<i class="mdi mdi-fw mdi-plus"></i> ${query}`)
			.on('click.tagdiag', function() {
				self.onSearchTagClick(jQuery(this));
				return;
			})
		);

		////////

		(this.querybin)
		.empty()
		.append(output.children());

		return;
	};

	updateTagsNone() {

		(this.tagbin)
		.empty()
		.append(
			jQuery('<span />')
			.addClass('btn btn-dark btn-block opacity-75 clickthru mb-2')
			.html('No tags found')
		);

		return;
	};

	onAccept() {

		let api = new API.Request('TAGSPATCH', '/api/media/entity');

		let output = {
			EntityUUID: this.uuid,
			EntityType: this.type
		};

		let itids = 0;
		let inames = 0;

		this.tagbin.find('button.btn')
		.each(function(it) {
			console.log(it);

			if(this.dataset.tagId !== 'null') {
				output[`TagID[${itids}]`] = this.dataset.tagId;
				itids += 1;
				return;
			}

			output[`TagName[${inames}]`] = this.dataset.tagName;
			inames += 1;
			return;
		});

		console.log(`tags for ${this.uuid} ${this.type}: ${output.length}`);
		console.log(output);

		(api.send(output))
		.then(this.onDone)
		.catch(api.catch);

		return;
	};

	onDone() {

		location.reload();

		return;
	};

};

export default TagDialog;
