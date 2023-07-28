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
				new DialogUtil.Field('date', 'DatePosted', 'Date Posted')
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

	onDelete(ev) {

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
			let eChildType = that.attr('data-child-type') ?? null;
			let eChildUUID = that.attr('data-child-uuid') ?? null;

			Video.WhenOnNew(eID, eUUID, eTagID, eChildType, eChildUUID);

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

			if(eID)
			Video.WhenOnDelete(eID, eUUID);

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
				new DialogUtil.Field('hidden', 'ChildType', null, eChildType),
				new DialogUtil.Field('hidden', 'ChildUUID', null, eChildUUID),
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

	static WhenOnDelete(eID, eUUID) {

		let vid = new Video(eID, eUUID);
		vid.onDelete(null);

		return;
	};

	static WhenOnEditEnable(eID, eUUID, state) {

		let vid = new Video(eID, eUUID);
		vid.onEditEnable(null, state);

		return;
	};

};

export default Video;
