import API         from '../../../nui/api/json.js';
import ModalWindow from '../../../nui/modules/modal/modal.js';
import EditorHTML  from '../../../nui/modules/editor/editor.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let BlobEditTemplate = `
<div>
	<input type="hidden" name="UUID" value="" />
	<input type="hidden" name="Type" value="" />
	<div>
		<div id="AtlBlobEditor" class="Editor"></div>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class BlobEditEntityWindow
extends ModalWindow {

	constructor() {
		super(BlobEditTemplate);

		this.elUUID = this.element.find('input[name="UUID"]');
		this.elType = this.element.find('input[name="Type"]');

		this.elEdit = this.element.find('textarea[name="Editor"]');
		this.editor = null;

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
			this.editor = new EditorHTML('#AtlBlobEditor');
		}

		return this;
	};

	getContent() {

		if(this.elType.val() === 'html') {
			return this.editor.getHTML();
		}

		return;
	};

	onAccept() {

		let uuid = this.elUUID.val();
		let type = this.elType.val();
		let text = this.editor.getHTML();

		let api = new API.Request('PATCH', '/api/atl/blob/entity', {
			'UUID': uuid,
			'Type': type,
			'Content': text
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
			'Type':    ent.type,
			'Title':   ent.title,
			'Content': ent.content
		};
	};

	static FromRequestPayload(payload) {

		let output = new BlobEntity;

		output.id = payload.ID;
		output.uuid = payload.UUID;
		output.type = payload.Type;
		output.title = payload.Title;
		output.content = payload.Content;

		return output;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-atl-blob-cmd="edit"]')
		.on('click', function(jEv){ BlobEntity.WhenCommandEdit(jEv, jQuery(this)); });

		return;
	};

	static WhenCommandEdit(jEv, btn) {

		let uuid = btn.attr('data-atl-blob-uuid');
		let type = btn.attr('data-atl-blob-type') || 'text';

		let win = BlobEditEntityWindow.ForUUID(uuid, type);

		return;
	};

};

export default BlobEntity;
