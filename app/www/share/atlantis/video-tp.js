import API        from '../nui/api/json.js';
import DialogUtil from '../nui/util/dialog.js';
import TagDialog  from '../atlantis/tag-dialog.js';

class Video {

	constructor(id, uuid) {

		this.id = id;
		this.uuid = uuid;
		this.endpoint = '/api/media/video-tp';

		console.log(`Video { ID: ${this.id}, UUID: ${this.uuid} }`);

		return;
	};

	////////////////
	////////////////

	bindify() {

		jQuery('[data-video-cmd=info]')
		.on('click', this.onInfo.bind(this));

		return;
	};

	////////////////
	////////////////

	onInfo(ev) {

		let diag = new DialogUtil.Window({
			title: 'Edit Video Info',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, this.id),
				new DialogUtil.Field('text', 'URL', 'URL'),
				new DialogUtil.Field('text', 'Title', 'Title'),
				new DialogUtil.Field('date', 'Date', 'Date Posted')
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('PATCH', this.endpoint, data);

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

