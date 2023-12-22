import API          from '../nui/api/json.js';
import DialogUtil   from '../nui/util/dialog.js';
import TagDialog    from '../atlantis/tag-dialog.js';
import EntDialog    from '../atlantis/eri-dialog.js';
import UploadButton from '../nui/modules/uploader/uploader.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DocReadyFunc {
	constructor(func, data=false) {

		this.func = func;
		this.data = data;

		return;
	};
};

class Profile {

	constructor(id, uuid) {

		this.id = id;
		this.uuid = uuid;

		this.endpoint = '/api/profile/entity';
		this.tagType = 'profile';
		this.entType = 'Profile.Entity';

		console.log(`Profile { ID: ${this.id}, UUID: ${this.uuid} }`);

		return;
	};

	////////////////
	////////////////

	onDelete(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Confirm Delete',
			labelAccept: 'Yes',
			body: (''
				+ '<div class="mb-2">Really delete this profile?</div>'
				+ `<div class="mb-2"><q>${self.id}</q></div>`
				+ '<div class="fw-bold text-danger mb-0">This cannot be undone.</div>'
			),
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id)
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('DELETE', self.endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.fillByRequest(
			'GET', self.endpoint,
			{ ID: self.id },
			true,
			function(d, result) {
				d.body.find('q').text(result.payload.Title);
				return;
			}
		);

		return false;
	};

	onEditDetails(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			maximise: true,
			title: 'Edit Profile Description',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('editor-html', 'Details', 'Description', jQuery('#EditorContent').html())
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

	onEditEnable(ev, state) {

		let self = this;
		let title = 'Edit Profile Status';
		let msg = 'Enable this Profile?'

		if(state === 0) {
			msg = 'Disable this Profile?';
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

	onEditTags(ev) {

		let diag = new TagDialog(this.uuid, this.tagType);
		diag.show();

		return false;
	};

	onEditRels(ev, tags, opts) {

		let diag = new EntDialog(this.uuid, tags, opts);
		diag.show();

		return false;
	};

	onEditTitle(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Edit Profile Title',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('text', 'Title', 'Title'),
				new DialogUtil.Field('text', 'Alias', 'Alias'),
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('PATCH', self.endpoint, data);

				(api.send())
				.then((result)=> location.href = result.payload.PageURL)
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

	onEditAlias(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Edit Profile Alias',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('text', 'Alias', 'URL Alias')
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('PATCH', self.endpoint, data);

				(api.send())
				.then((result)=> location.href = result.payload.PageURL)
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

	onEditAddress(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Edit Profile Address',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('text', 'AddressStreet1', 'Street Address 1'),
				new DialogUtil.Field('text', 'AddressStreet2', 'Street Address 2'),
				new DialogUtil.Field('text', 'AddressCity', 'City'),
				new DialogUtil.Field('text', 'AddressState', 'State'),
				new DialogUtil.Field('text', 'AddressPostalCode', 'Zip')
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

	onEditLinks(ev) {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Edit Profile Social Links',
			labelAccept: 'Save',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('text', 'SocialDataWebsite', 'Website'),
				new DialogUtil.Field('text', 'SocialDataFacebook', 'Facebook'),
				new DialogUtil.Field('text', 'SocialDataInstagram', 'Instagram'),
				new DialogUtil.Field('text', 'SocialDataTikTok', 'TikTok'),
				new DialogUtil.Field('text', 'SocialDataThreads', 'Threads'),
				new DialogUtil.Field('text', 'SocialDataTwitter', 'Twitter/X'),
				new DialogUtil.Field('text', 'SocialDataYouTube', 'YouTube')
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
			true,
			function(d, result) {
				d.fillFromObject(result.payload.SocialData, 'SocialData');
				return;
			}
		);

		return false;
	};

	////////////////
	////////////////

	onAddVideo(ev) {

		let self = this;

		new DialogUtil.Window({
			show: true,
			title: 'Add Video By URL',
			labelAccept: 'Add',
			fields: [
				new DialogUtil.Field('hidden', 'ParentUUID', null, self.uuid),
				new DialogUtil.Field('hidden', 'ParentType', null, self.entType),
				new DialogUtil.Field('text', 'URL'),
				new DialogUtil.Field('text', 'Title'),
				new DialogUtil.Field('date', 'DatePosted', 'Date')
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('POST', '/api/media/video-tp', data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		return false;
	};

	onSetPhoto(ev, pho) {

		let self = this;

		new DialogUtil.Window({
			show: true,
			title: 'Set As Main Photo',
			body: 'Use this as the main photo for this profile?',
			labelAccept: 'Yes',
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id),
				new DialogUtil.Field('hidden', 'CoverImageID', null, pho)
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

		return false;
	};

	////////////////
	////////////////

	static MountTempButton() {

		let lol = document.createElement('button');

		lol.classList.add('d-none');
		document.body.append(lol);

		return lol;
	};

	static WhenDocumentReady() {

		let map = {
			new:      new DocReadyFunc((i, u, b)=> Profile.WhenNew(i, u, b), false),
			title:    new DocReadyFunc((i, u)=> Profile.WhenEditTitle(i, u), true),
			alias:    new DocReadyFunc((i, u)=> Profile.WhenEditAlias(i, u), true),
			links:    new DocReadyFunc((i, u)=> Profile.WhenEditLinks(i, u), true),
			details:  new DocReadyFunc((i, u)=> Profile.WhenEditDetails(i, u), true),
			tags:     new DocReadyFunc((i, u)=> Profile.WhenEditTags(i, u), true),
			erlink:   new DocReadyFunc((i, u, b)=> Profile.WhenEditRels(i, u, b), true),
			photo:    new DocReadyFunc((i, u)=> Profile.WhenUploadPhoto(i, u), true),
			videotp:  new DocReadyFunc((i, u)=> Profile.WhenAddVideo(i, u), true),
			photoset: new DocReadyFunc((i, u, e)=> Profile.WhenSetPhoto(i, u, e), true),
			disable:  new DocReadyFunc((i, u)=> Profile.WhenEditEnable(i, u, 0), true),
			enable:   new DocReadyFunc((i, u)=> Profile.WhenEditEnable(i, u, 1), true),
			delete:   new DocReadyFunc((i, u)=> Profile.WhenDelete(i, u), true),
			address:  new DocReadyFunc((i, u)=> Profile.WhenEditAddress(i, u), true)
		};

		for(let key in map) {
			let item = map[key];

			jQuery(`[data-profile-cmd=${key}]`)
			.on('click', function(ev) {

				let that = jQuery(this);

				if(item.data === false) {
					item.func.call(null, that);
					return false;
				}

				let eID = that.attr('data-id') ?? null;
				let eUUID = that.attr('data-uuid') ?? null;

				if(eID)
				item.func.call(null, eID, eUUID, that);

				return false;
			});
		}

		return;
	};

	static WhenDelete(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onDelete(null);

		return;
	};

	static WhenEditDetails(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditDetails(null);

		return;
	};

	static WhenEditEnable(id, uuid, state) {

		let pro = new Profile(id, uuid);
		pro.onEditEnable(null, state);

		return;
	};

	static WhenEditTitle(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditTitle(null);

		return;
	};

	static WhenEditAlias(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditAlias(null);

		return;
	};

	static WhenEditAddress(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditAddress(null);

		return;
	};

	static WhenEditLinks(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditLinks(null);

		return;
	};

	static WhenEditTags(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onEditTags(null);

		return;
	};

	static WhenEditRels(id, uuid, btn) {

		let pro = new Profile(id, uuid);

		let opt = {
			title: null,

			parentType: null,
			childType: null,

			searchVerb: null,
			searchURL: null,

			listVerb: null,
			listURL: null,

			saveVerb: null,
			saveURL: null
		};

		let tags = btn.attr('data-profile-tags');

		////////

		if(btn.has('[data-parent-type]'))
		opt.parentType = btn.attr('data-parent-type');

		if(btn.has('[data-child-type]'))
		opt.childType = btn.attr('data-child-type');

		if(btn.has('[data-er-title]'))
		opt.title = btn.attr('data-er-title');

		if(btn.has('[data-er-type]'))
		opt.childType = btn.attr('data-er-type');

		if(btn.has('[data-er-search-verb]'))
		opt.searchVerb = btn.attr('data-er-search-verb');

		if(btn.has('[data-er-search-url]'))
		opt.searchURL = btn.attr('data-er-search-url');

		console.log(opt);

		////////

		pro.onEditRels(null, tags, opt);

		return;
	};

	static WhenNew(btn) {

		let pro = new Profile(null, null);
		let tags = btn.attr('data-profile-tags');

		////////

		new DialogUtil.Window({
			show: true,
			title: 'New Profile',
			labelAccept: 'Create',
			fields: [
				new DialogUtil.Field('text', 'Title'),
				new DialogUtil.Field('hidden', 'Tags', null, tags)
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('POST', pro.endpoint, data);

				(api.send())
				.then(api.goto)
				.catch(api.catch);

				return;
			}
		});

		return;
	};

	static WhenUploadPhoto(id, uuid) {

		// the upload button does its job good and i should keep it, but
		// it also needs to be refactored a bit to be more programmable.
		// this is stupid but it will be ok for now.

		let lol = Profile.MountTempButton();
		let btn = new UploadButton(lol, {
			'title': 'Upload Profile Photos...',
			'dataset': { 'ID': id, 'ParentUUID': uuid, 'ParentType': 'Profile.Entity' },
			'onSuccess': ()=> location.reload()
		});

		btn.onButtonClick();
		lol.remove();

		return;
	};

	static WhenAddVideo(id, uuid) {

		let pro = new Profile(id, uuid);
		pro.onAddVideo(null);

		return;
	};

	static WhenSetPhoto(id, uuid, element) {

		let pro = new Profile(id, uuid);
		let pho = element.attr('data-photo-id');

		pro.onSetPhoto(null, pho);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default Profile;
