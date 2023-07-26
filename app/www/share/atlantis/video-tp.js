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

	bindify() {

		let self = this;

		////////

		jQuery('[data-video-cmd=info]')
		.on('click', this.onEditInfo.bind(this));

		jQuery('[data-video-cmd=details]')
		.on('click', this.onEditDetails.bind(this));

		jQuery('[data-video-cmd=tags]')
		.on('click', this.onEditTags.bind(this));

		jQuery('[data-video-cmd=enable]')
		.on('click', function(ev) { return self.onEditEnable(ev, 1); });

		jQuery('[data-video-cmd=disable]')
		.on('click', function(ev) { return self.onEditEnable(ev, 0); });

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

	////////////////
	////////////////

	static WhenDocumentReady() {

		jQuery('[data-videotp-cmd=new]')
		.on('click', function(ev) {

			let that = jQuery(this);
			let eID = that.attr('data-id') ?? null;
			let eUUID = that.attr('data-uuid') ?? null;
			let eTagID = that.attr('data-tag-id') ?? null;

			Video.WhenOnNew(eID, eUUID, eTagID);

			return;
		});

		return;
	};

	static WhenOnNew(eID, eUUID, eTagID) {

		let v = new Video(eID, eUUID);
		let endpoint = v.endpoint;

		////////

		new DialogUtil.Window({
			show: true,
			title: 'Add Video By URL',
			labelAccept: 'Add',
			fields: [
				new DialogUtil.Field('hidden', 'TagID', null, eTagID),
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

	////////////////
	////////////////

	static FromElement({ el='#VideoEntityInfo', bindify=false } = {}) {

		let that = jQuery(el);

		let output = new Video(
			that.attr('data-id'),
			that.attr('data-uuid')
		);

		if(bindify)
		output.bindify();

		return output;
	};

};

export default Video;

