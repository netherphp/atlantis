import API         from '../../../nui/api/json.js';
import ModalWindow from '../../../nui/modules/modal/modal.js';
import EditorHTML  from '../../../nui/modules/editor/editor.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let GroupNewTemplate = `
<div>
	<div class="mb-4">
		<div class="fw-bold">Title</div>
		<input type="text" name="Title" class="form-control" />
	</div>
</div>
`;

let GroupEditTemplate = `
<div>
	<input type="hidden" name="UUID" value="" />
	<div class="mb-4">
		<div class="fw-bold">Title</div>
		<input type="text" name="Title" class="form-control" />
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class GroupEditEntityWindow
extends ModalWindow {

	constructor() {
		super(GroupEditTemplate);

		this.elUUID = this.element.find('input[name="UUID"]');
		this.elTitle = this.element.find('input[name="Title"]');

		this.setTitle('Edit Group');
		this.addButton('Save', 'btn-primary', 'accept');
		this.addButton('Cancel', 'btn-secondary', 'cancel');

		this.show();
		return;
	};

	setUUID(uuid) {

		this.elUUID.val(uuid);

		return this;
	};

	onAccept() {

		let uuid = this.elUUID.val();
		let title = this.elTitle.val();

		let api = new API.Request('PATCH', '/api/atl/blob/group', {
			'UUID':     uuid,
			'Title':    title
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

		let api = new API.Request('GET', '/api/atl/blob/group', {
			'UUID': uuid
		});

		(api.send())
		.then(function(r){
			self.elTitle.val(r.payload.Title);
			return;
		});

		return;
	};

	static ForUUID(uuid) {

		let w = new GroupEditEntityWindow;

		w.setUUID(uuid);

		w.fetchThenShow();

		return w;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class GroupEntity {

	constructor() {

		this.id = null;
		this.uuid = null;
		this.title = null;

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static ToRequestPayload(ent) {

		return {
			'ID':      ent.id,
			'UUID':    ent.uuid,
			'Title':   ent.title
		};
	};

	static FromRequestPayload(payload) {

		let output = new GroupEntity;

		output.id = payload.ID;
		output.uuid = payload.UUID;
		output.title = payload.Title;

		return output;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-atl-bgrp-cmd="new"]')
		.on('click', function(jEv){ GroupEntity.WhenCommandNew(jEv, jQuery(this)); });

		jQuery('[data-atl-bgrp-cmd="edit"]')
		.on('click', function(jEv){ GroupEntity.WhenCommandEdit(jEv, jQuery(this)); });

		return;
	};

	static WhenCommandNew(jEv, btn) {

		let uuid = btn.attr('data-atl-bgrp-uuid');

		let win = GroupEditEntityWindow.ForUUID(uuid);

		return;
	};

	static WhenCommandEdit(jEv, btn) {

		let uuid = btn.attr('data-atl-bgrp-uuid');

		let win = GroupEditEntityWindow.ForUUID(uuid);

		return;
	};

};

export default GroupEntity;
