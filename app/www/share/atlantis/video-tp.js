import API        from '../nui/api/json.js';
import DialogUtil from '../nui/util/dialog.js';
import TagDialog  from '../atlantis/tag-dialog.js';

class Video {

	constructor(id, uuid) {

		this.id = id;
		this.uuid = uuid;

		this.endpoint = '/api/media/video-tp';
		this.tagType = 'videotp';

		console.log(`Video { ID: ${this.id}, UUID: ${this.uuid} }`);

		return;
	};

	////////////////
	////////////////

	onEditDetails(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			maximise: true,
			title: 'Edit Video Description',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('editor-html', 'Details', 'Details', jQuery('#VideoDetails').html())
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('PATCH', self.endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.fillByRequest(
			'GET', self.endpoint,
			{ ID: self.id },
			true
		);

		return false;
	};

	onEditInfo(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Edit Video Info',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, this.id),
				new DialogUtil.Field('text', 'URL', 'URL'),
				new DialogUtil.Field('text', 'Title', 'Title'),
				new DialogUtil.Field('date', 'DatePosted', 'Date Released')
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('PATCH', self.endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.fillByRequest(
			'GET', this.endpoint,
			{ ID: this.id },
			true
		);

		return false;
	};

	onEditTags(ev) {

		let diag = new TagDialog(this.uuid, this.tagType);
		diag.show();

		return false;
	};

	onEditEnable(ev, state) {

		let self = this;
		let title = 'Edit Video Status';
		let msg = 'Enable this video?'

		if(state === 0) {
			msg = 'Disable this video?';
		}

		let diag = new DialogUtil.Window({
			show: true,
			title: title,
			labelAccept: 'Yes',
			body: msg,
			onAccept: function() {

				let data = { ID: self.id, Enabled: state };
				let api = new API.Request('PATCH', self.endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		return false;
	};

	onDelete(ev, pUUID=null) {

		let self = this;
		let diag = null;

		////////

		diag = new DialogUtil.Window({
			title: 'Confirm Video Delete',
			labelAccept: 'Yes',
			body: (''
				+ '<div class="mb-2">Are you sure you want to delete this video?</div>'
				+ `<div class="fst-italic mb-2"><q data-field="Title">${self.id}</q></div>`
				+ `<div class="fw-bold text-danger">This cannot be undone.</div>`
			),
			onAccept: function() {

				let data = { ID: self.id };
				let api = new API.Request('DELETE', self.endpoint, data);

				if(pUUID !== null)
				data['ParentUUID'] = pUUID;

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.fillByRequest(
			'GET', this.endpoint,
			{ ID: this.id },
			true,
			function(d, result) {
				d.body.find('[data-field=Title]').text(result.payload.Title);
				return;
			}
		);

		return;
	};

	////////////////
	////////////////

	static WhenDocumentReady() {

		jQuery('[data-videotp-cmd=new]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;
			let eTagID = that.attr('data-tag-id') ?? null;
			let eChildType = that.attr('data-parent-type') ?? null;
			let eChildUUID = that.attr('data-parent-uuid') ?? null;

			Video.WhenOnNew(eID, eUUID, eTagID, eChildType, eChildUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=new2]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;
			let eTagID = that.attr('data-tag-id') ?? null;
			let eOtherType = that.attr('data-other-type') ?? null;
			let eOtherUUID = that.attr('data-other-uuid') ?? null;

			Video.WhenOnNew2(eID, eUUID, eTagID, eOtherType, eOtherUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=edit]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;

			if(eID)
			Video.WhenOnEdit(eID, eUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=tags]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;

			if(eID)
			Video.WhenOnEditTags(eID, eUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=details]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;

			if(eID)
			Video.WhenOnEditDetails(eID, eUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=delete]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;
			let pUUID = that.attr('data-delete-from') ?? null;

			if(eID)
			Video.WhenOnDelete(eID, eUUID, pUUID);

			return false;
		});

		jQuery('[data-videotp-cmd=disable]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;

			if(eID)
			Video.WhenOnEditEnable(eID, eUUID, 0);

			return false;
		});

		jQuery('[data-videotp-cmd=enable]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;

			if(eID)
			Video.WhenOnEditEnable(eID, eUUID, 1);

			return false;
		});

		return;
	};

	static WhenOnNew(eID, eUUID, eTagID, eChildType, eChildUUID) {

		let v = new Video(eID, eUUID);
		let endpoint = v.endpoint;

		////////

		new DialogUtil.Window({
			show: true,
			title: 'Add Video By URL',
			labelAccept: 'Add',
			fields: [
				new DialogUtil.Field('hidden', 'TagID', null, eTagID),
				new DialogUtil.Field('hidden', 'ParentType', null, eChildType),
				new DialogUtil.Field('hidden', 'ParentUUID', null, eChildUUID),
				new DialogUtil.Field('text', 'URL'),
				new DialogUtil.Field('text', 'Title'),
				new DialogUtil.Field('date', 'DatePosted', 'Date')
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('POST', endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		return;
	};

	static WhenOnNew2(eID, eUUID, eTagID, eOtherType, eOtherUUID) {

		let v = new Video(eID, eUUID);
		let endpoint = v.endpoint;

		////////

		var TemplateVideoByURL = `
		<div class="row tight">
			<div class="col-12 mb-2">
				<div class="fw-bold">Video URL:</div>
				<input type="text" name="URL" class="form-control dialog-video-new-url" placeholder="YouTube, Vimeo, or TikTok" />
			</div>
			<div class="col-12 mb-2">
				<div class="fw-bold">Title:</div>
				<input type="text" name="Title" class="form-control dialog-video-new-title" />
			</div>
			<div class="col-12 mb-2">
				<div class="fw-bold">Date:</div>
				<input type="date" name="Date" class="form-control dialog-video-new-date" pattern="\d{4}-\d{2}-\d{2}" />
			</div>
			<div class="col-12 dialog-video-new-output">

			</div>
		</div>
		`;

		var TemplateVideoDupResult = `
		<div class="row tight align-items-center mb-2">
			<div class="col-4">
				<div class="ratiobox widescreen wallpapered cover rounded" style="background-image: url(%ImageURL%);">
					<a href="%PageURL%" target="_blank" class="position-absolutely"></a>
				</div>
			</div>
			<div class="col">
				<div class="fw-bold"><a href="%PageURL%" target="_blank">%Title%</a></div>
			</div>
			<div class="col-auto">
				<a href="%PageURL%" target="_blank" class="btn btn-secondary">
					<i class="mdi mdi-open-in-new mr-0"></i>
				</a>
			</div>
		</div>
		`;

		var TemplateVideoBySearch = `
		<div class="row tight">
			<div class="col-12 mb-4">
				<div class="fw-bold">Search:</div>
				<input type="text" name="URL" class="form-control dialog-video-search-input" placeholder="Video Title Search...">
			</div>
			<div class="col-12 dialog-video-search-output" style="max-height: 50vh; overflow-y: scroll;">

			</div>
		</div>
		`;

		var TemplateVideoSearchResult = `
		<div class="row tight align-items-center rounded mb-4" data-video-title="" data-video-url="" data-video-date="">
			<div class="col-4">
				<div class="ratiobox widescreen wallpapered cover rounded" style="background-image: url(%ImageURL%);">
					<a href="#" class="position-absolutely dialog-video-search-select"></a>
				</div>
			</div>
			<div class="col">
				<div class="fw-bold">
					<a href="#" class="dialog-video-search-select">%Title%</a>
				</div>
			</div>
			<div class="col-auto">
				<a href="%PageURL%" target="_blank" class="btn btn-secondary">
					<i class="mdi mdi-open-in-new mr-0"></i>
				</a>
			</div>
		</div>
		`;


		new DialogUtil.Window({
			show: true,
			title: 'Add Video',
			labelAccept: 'Add',
			fields: [
				new DialogUtil.Field('hidden', 'OtherType', null, eOtherType),
				new DialogUtil.Field('hidden', 'OtherUUID', null, eOtherUUID),
			],
			body: (
				jQuery('<div />')
				.addClass('row tight')
				.append(
					jQuery('<div />')
					.addClass('col mb-4')
					.append(
						jQuery('<button />')
						.addClass('btn btn-block btn-outline-primary')
						.attr('data-video-dialog-select', 'by-url')
						.text('Add By URL')
					)
				)
				.append(
					jQuery('<div />')
					.addClass('col mb-4')
					.append(
						jQuery('<button />')
						.addClass('btn btn-block btn-outline-primary')
						.attr('data-video-dialog-select', 'by-search')
						.text('Search')
					)
				)
				.append(
					jQuery('<div />')
					.addClass('col-12 d-none')
					.attr('data-video-dialog-mode', 'by-url')
					.append(TemplateVideoByURL)
				)
				.append(
					jQuery('<div />')
					.addClass('col-12 d-none')
					.attr('data-video-dialog-mode', 'by-search')
					.append(TemplateVideoBySearch)
				)
			),
			onReady: function(){

				let self = this;

				self.mode = null;
				self.searchTimeout = null;

				self.newURL = this.element.find('.dialog-video-new-url');
				self.newTitle = this.element.find('.dialog-video-new-title');
				self.newDate = this.element.find('.dialog-video-new-date');
				self.newOutput = this.element.find('.dialog-video-new-output');
				self.searchInput = this.element.find('.dialog-video-search-input');
				self.searchOutput = this.element.find('.dialog-video-search-output');

				////////

				// mode selection widgets.

				(self.element.find('[data-video-dialog-select]'))
				.on('click', function(){

					let that = jQuery(this);
					let mode = that.attr('data-video-dialog-select');

					// make the buttons change states.

					(self.element.find('[data-video-dialog-select]'))
					.addClass('btn-outline-primary')
					.removeClass('btn-primary');

					(that)
					.addClass('btn-primary')
					.removeClass('btn-outline-primary');

					// make the view areas change states.

					(self.element.find(`[data-video-dialog-mode]`))
					.addClass('d-none');

					(self.element.find(`[data-video-dialog-mode=${mode}]`))
					.removeClass('d-none');

					self.mode = mode;

					return;
				});

				// url search reaction.

				self.newURL.on('keyup', function(){

					self.newOutput.empty();

					if(self.searchTimeout) {
						clearTimeout(self.searchTimeout);
						self.searchTimeout = null;
					}

					self.searchTimeout = setTimeout(function(){

						let input = jQuery.trim(self.newURL.val());

						if(!input.match(/^https?/))
						return;

						let api = new API.Request('SEARCH', '/api/video/entity', {
							"Q": input,
							"SearchTitle": 0,
							"SearchURL": 1
						});

						(api.send())
						.then(function(result){
							if(result.payload.Total == 0)
							return;

							console.log(result);

							let widget = TemplateVideoDupResult;
							widget = widget.replace(/%Title%/g, result.payload.Results[0].Title);
							widget = widget.replace(/%PageURL%/g, result.payload.Results[0].PageURL);
							widget = widget.replace(/%ImageURL%/g, result.payload.Results[0].ImageURL);

							self.newTitle.val(result.payload.Results[0].Title);
							self.newDate.val(result.payload.Results[0].DatePosted);

							self.newOutput
							.append('<div class="fw-bold mt-4">Video Already Exists</div>')
							.append('<div class="text-muted fs-small mb-2">Click add to continue linking it to this post.</div>')
							.append(widget);

							return;
						})
						.catch(api.catch);

						return;
					}, 300);

					return;
				});

				// title search reaction.

				self.searchInput.on('keyup', function(){

					self.searchOutput.empty();

					if(self.searchTimeout) {
						clearTimeout(self.searchTimeout);
						self.searchTimeout = null;
					}

					self.searchTimeout = setTimeout(function(){

						let input = jQuery.trim(self.searchInput.val());

						let api = new API.Request('SEARCH', '/api/video/entity', {
							"Q": input,
							"SearchTitle": 1,
							"SearchURL": 0
						});

						(api.send())
						.then(function(result){
							if(result.payload.Total == 0)
							return;

							self.searchOutput
							.append(
								`<div class="fw-bold mb-2">${result.payload.Total} Found</div>`
							);

							for(let item of result.payload.Results) {
								let widget = TemplateVideoSearchResult;
								widget = widget.replace(/%Title%/g, item.Title);
								widget = widget.replace(/%PageURL%/g, item.PageURL);
								widget = widget.replace(/%ImageURL%/g, item.ImageURL);

								widget = jQuery(widget);

								widget
								.find('.dialog-video-search-select')
								.on('click', function(){

									let row = jQuery(this).parent().parent().parent();

									row.parent().find('.bg-primary').removeClass('bg-primary');

									row.attr('data-video-url', item.URL);
									row.attr('data-video-title', item.Title);
									row.attr('data-video-date', item.Date);
									row.addClass('bg-primary');

									return false;
								});

								self.searchOutput
								.append(widget);
							}

							return;
						})
						.catch(api.catch);

						return;
					}, 300);

					return;
				});

				////////

				return;
			},
			onAccept: function() {

				let otherType = jQuery.trim(this.element.find('input[name=OtherType]').val());
				let otherUUID = jQuery.trim(this.element.find('input[name=OtherUUID]').val());

				if(this.mode === 'by-url') {
					let url = jQuery.trim(this.newURL.val());
					let title = jQuery.trim(this.newTitle.val());
					let date = jQuery.trim(this.newDate.val());

					let api = new API.Request('POST', '/api/video/entity', {
						"URL": url, "Title": title, "Date": date,
						"OtherType": otherType, "OtherUUID": otherUUID
					});

					(api.send())
					.then(api.reload)
					.catch(api.catch);

					return;
				}

				if(this.mode === 'by-search') {
					let row = this.searchOutput.find('.bg-primary');
					let url = jQuery.trim(row.attr('data-video-url'));
					let title = jQuery.trim(row.attr('data-video-title'));
					let date = jQuery.trim(row.attr('data-video-date'));

					let api = new API.Request('POST', '/api/video/entity', {
						"URL": url, "Title": title, "Date": date,
						"OtherType": otherType, "OtherUUID": otherUUID
					});

					(api.send())
					.then(api.reload)
					.catch(api.catch);
				}

				return;

				let data = this.getFieldData();
				let api = new API.Request('POST', endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		return;
	};

	static WhenOnEdit(eID, eUUID) {

		let vid = new Video(eID, eUUID);
		vid.onEditInfo(null);

		return;
	};

	static WhenOnEditDetails(eID, eUUID) {

		let vid = new Video(eID, eUUID);
		vid.onEditDetails(null);

		return;
	};

	static WhenOnEditTags(eID, eUUID) {

		let vid = new Video(eID, eUUID);
		vid.onEditTags(null);

		return;
	};

	static WhenOnDelete(eID, eUUID, pUUID) {

		let vid = new Video(eID, eUUID);
		vid.onDelete(null, pUUID);

		return;
	};

	static WhenOnEditEnable(eID, eUUID, state) {

		let vid = new Video(eID, eUUID);
		vid.onEditEnable(null, state);

		return;
	};

};

export default Video;
