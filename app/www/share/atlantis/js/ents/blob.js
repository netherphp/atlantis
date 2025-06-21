import API         from '../../../nui/api/json.js';
import ModalWindow from '../../../nui/modules/modal/modal.js';
import EditorHTML  from '../../../nui/modules/editor/editor.js';
import UploadBtn   from '../../../nui/modules/uploader/uploader.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let BlobNewTemplate = `
<div>
	<input type="hidden" name="GroupID" value="" />
	<input type="hidden" name="Type" value="html" />
	<div class="mb-4">
		<div class="fw-bold">Title</div>
		<input type="text" name="Title" class="form-control" />
	</div>
	<div class="mb-4">
		<div class="fw-bold">Content</div>
		<div class="Editor AtlBlobEditor"></div>
	</div>
</div>
`;

let BlobEditTemplate = `
<div>
	<input type="hidden" name="UUID" value="" />
	<input type="hidden" name="Type" value="" />
	<div class="mb-4">
		<div class="fw-bold">Title</div>
		<input type="text" name="Title" class="form-control" />
	</div>
	<div class="mb-4">
		<div class="fw-bold">Content</div>
		<div class="Editor AtlBlobEditor"></div>
	</div>
	<div class="mb-0">
		<div class="row">
			<div class="col">
				<div class="fw-bold mb-2">Image</div>
				<div class="row g-2">
					<div class="col-auto">
						<button name="ImageUploadBtn" class="btn btn-primary">
							<i class="mdi mdi-upload"></i>
							Upload Image
						</button>
					</div>
					<div class="col"><input type="text" name="ImageURL" class="form-control" /></div>
				</div>

			</div>
			<div class="col-2">
				<div name="ImagePreview" class="ratiobox square wallpapered contained bg-transparent border rounded"></div>
			</div>
		</div>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class BlobNewEntityWindow
extends ModalWindow {

	constructor(type, group=null) {
		super(BlobNewTemplate);

		this.elGroup = this.element.find('input[name="GroupID"]');
		this.elType = this.element.find('input[name="Type"]');
		this.elTitle = this.element.find('input[name="Title"]');
		this.editor = null;

		this.setWidth('95vw');
		this.setTitle('New Item');
		this.addButton('Save', 'btn-primary', 'accept');
		this.addButton('Cancel', 'btn-secondary', 'cancel');

		this.setType(type);
		this.setGroup(group);

		this.show();
		return;
	};

	setType(type) {

		this.elType.val(type);

		if(type === 'html') {
			this.editor = new EditorHTML(this.element.find('.AtlBlobEditor').get(0));
		}

		return this;
	};

	setGroup(group) {

		this.elGroup.val(group);

		return this;
	};

	onAccept() {

		let group = this.elGroup.val() || null;
		let type = this.elType.val() || 'html';
		let title = this.elTitle.val();
		let text = this.editor.getHTML();

		let api = new API.Request('POST', '/api/atl/blob/entity', {
			'GroupID':  group,
			'Type':     type,
			'Title':    title,
			'Content':  text
		});

		(api.send())
		.then(function(r){
			location.reload();
			return;
		})
		.catch(api.catch);

		return;
	};

};

class BlobEditEntityWindow
extends ModalWindow {

	constructor() {
		super(BlobEditTemplate);

		this.elUUID = this.element.find('input[name="UUID"]');
		this.elType = this.element.find('input[name="Type"]');
		this.elTitle = this.element.find('input[name="Title"]');
		this.elImage = this.element.find('input[name="ImageURL"]');
		this.elUpload = this.element.find('button[name="ImageUploadBtn"]');
		this.elPreview = this.element.find('div[name="ImagePreview"]');

		this.elEdit = this.element.find('textarea[name="Editor"]');
		this.editor = null;

		new UploadBtn(this.elUpload, {
			onSuccess: this.onUploaded.bind(this)
		});

		this.setWidth('95vw');
		this.setTitle('Edit Content');
		this.addButton('Save', 'btn-primary', 'accept');
		this.addButton('Cancel', 'btn-secondary', 'cancel');

		this.show();
		return;
	};

	setUUID(uuid) {

		this.elUUID.val(uuid);

		return this;
	};

	setType(type) {

		this.elType.val(type);

		if(type === 'html') {
			this.editor = new EditorHTML(this.element.find('.AtlBlobEditor').get(0));
		}

		return this;
	};

	getContent() {

		if(this.elType.val() === 'html') {
			return this.editor.getHTML();
		}

		return;
	};

	onUploaded(r, dialog) {
		this.elImage.val(r.payload.URL);
		this.elPreview.css({ 'background-image': `url(${r.payload.URL})` });
		dialog.destroy();
		return;
	};

	onAccept() {

		let uuid = this.elUUID.val();
		let type = this.elType.val();
		let title = this.elTitle.val();
		let img = this.elImage.val();
		let text = this.editor.getHTML();

		let api = new API.Request('PATCH', '/api/atl/blob/entity', {
			'UUID':     uuid,
			'Type':     type,
			'Title':    title,
			'ImageURL': img,
			'Content':  text
		});

		(api.send())
		.then(function(r){
			location.reload();
			return;
		})
		.catch(api.catch);

		return;
	};

	fetchThenShow() {

		let self = this;
		let uuid = this.elUUID.val();

		let api = new API.Request('GET', '/api/atl/blob/entity', {
			'UUID': uuid
		});

		(api.send())
		.then(function(r){
			self.elTitle.val(r.payload.Title);
			self.elImage.val(r.payload.ImageURL);
			self.elPreview.css({ 'background-image': `url(${r.payload.ImageURL})` });
			self.editor.setContent(r.payload.Content);
			return;
		});

		return;
	};

	static ForUUID(uuid, type) {

		let w = new BlobEditEntityWindow;

		w.setUUID(uuid);
		w.setType(type);

		w.fetchThenShow();

		return w;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class BlobEntity {

	constructor() {

		this.id = null;
		this.uuid = null;
		this.groupID = null;
		this.type = null;
		this.title = null;
		this.content = null;

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static ToRequestPayload(ent) {

		return {
			'ID':      ent.id,
			'UUID':    ent.uuid,
			'GroupID': ent.groupID,
			'Type':    ent.type,
			'Title':   ent.title,
			'Content': ent.content
		};
	};

	static FromRequestPayload(payload) {

		let output = new BlobEntity;

		output.id = payload.ID;
		output.uuid = payload.UUID;
		output.groupID = payload.GroupID;
		output.type = payload.Type;
		output.title = payload.Title;
		output.content = payload.Content;

		return output;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-atl-blob-cmd="new"]')
		.on('click', function(jEv){ BlobEntity.WhenCommandNew(jEv, jQuery(this)); });

		jQuery('[data-atl-blob-cmd="edit"]')
		.on('click', function(jEv){ BlobEntity.WhenCommandEdit(jEv, jQuery(this)); });

		jQuery('[data-atl-blob-cmd="delete"]')
		.on('click', function(jEv){ BlobEntity.WhenCommandDelete(jEv, jQuery(this)); });

		return;
	};

	static WhenCommandNew(jEv, btn) {

		let group = btn.attr('data-atl-blob-group') || null;
		let type = btn.attr('data-atl-blob-type') || 'html';

		let win = new BlobNewEntityWindow(type, group);

		win.show();

		return;
	};

	static WhenCommandEdit(jEv, btn) {

		let uuid = btn.attr('data-atl-blob-uuid');
		let type = btn.attr('data-atl-blob-type') || 'html';

		let win = BlobEditEntityWindow.ForUUID(uuid, type);

		win.show();

		return;
	};

	static WhenCommandDelete(jEv, btn) {

		let uuid = btn.attr('data-atl-blob-uuid');
		let type = btn.attr('data-atl-blob-type') || 'html';

		if(confirm('Really delete this?')) {
			let api = new API.Request('DELETE', '/api/atl/blob/entity', {
				'UUID':  uuid
			});

			(api.send())
			.then(function(r){
				location.reload();
				return;
			})
			.catch(api.catch);
		}

		return;
	};

};

export default BlobEntity;
