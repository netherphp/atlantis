import API from '../../nui/api/json.js';
import Form from '../../nui/util/form.js';

import NUIUtil from '../../nui/util.js';
import DialogUtil from '../../nui/util/dialog.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DeckManager {

	static WhenDocumentReady() {

		jQuery('[data-deck-copy-alias]')
		.on('click', DeckManager.OnClickCopyAlias);

		jQuery('[data-deck-new]')
		.on('click', DeckManager.OnClickNew);

		jQuery('[data-deck-delete]')
		.on('click', DeckManager.OnClickDelete);

		////////

		jQuery('[data-deck-editor-save]')
		.on('click', ((jEv)=> this.OnClickEditorSave(jEv)));

		jQuery('[data-deck-editor-row-add]')
		.on('click', ((jEv)=> this.OnClickEditorRowAdd(jEv)));

		jQuery('[data-deck-editor]')
		.on(
			'click', '[data-deck-editor-row-move-up]',
			((jEv)=> this.OnClickEditorRowMoveUp(jEv))
		)
		.on(
			'click', '[data-deck-editor-row-move-down]',
			((jEv)=> this.OnClickEditorRowMoveDown(jEv))
		)
		.on(
			'click', '[data-deck-editor-row-delete]',
			((jEv)=> this.OnClickEditorRowDelete(jEv))
		);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static OnClickCopyAlias(jEv) {

		let that = jQuery(this);
		let alias = that.attr('data-deck-copy-alias');

		NUIUtil.copyValueToClipboard(alias, this);

		return false;
	};

	static OnClickNew(jEv) {

		DialogUtil.Window.New({
			title: 'New Slide Deck',
			show: true,
			fields: [
				DialogUtil.Field.New({
					type: 'text3',
					name: 'Name',
					title: 'Deck Name',
					info: 'Just a descriptive name.'
				}),
				DialogUtil.Field.New({
					type: 'text3',
					name: 'Alias',
					title: 'Alias / Filename',
					info: 'The alias used when calling for this deck from code.'
				})
			],
			onAccept: function() {

				let name = this.element.find('[name="Name"]').val();
				let alias = this.element.find('[name="Alias"]').val();
				let api = new API.Request('POST', '/ops/deckmgr/api');
				let data = { "Name": name, "Alias": alias };

				(api.send(data))
				.then(api.goto)
				.catch(api.catch);

				return;
			}
		});

		return false;
	};

	static OnClickDelete(jEv) {

		let that = jQuery(this);
		let alias = that.attr('data-deck-delete');

		DialogUtil.Window.New({
			title: 'Delete Slide Deck',
			show: true,
			body: 'Really delete? This cannot be undone.',
			onAccept: function() {

				let api = new API.Request('DELETE', '/ops/deckmgr/api');
				let data = { "Alias": alias };

				(api.send(data))
				.then(function(r){
					location.reload();
					return;
				})
				.catch(api.catch);

				return;
			}
		});

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static OnClickEditorRowAdd(jEv) {

		let box = this.FetchDeckEditor();
		let tpl = this.FetchDeckEditorTemplate();
		let num = this.NewRowIterator();

		////////

		(tpl.find('div'))
		.attr('id', `row-new-${num}`);

		(tpl.find('hr'))
		.attr('id', `hr-new-${num}`);

		(tpl.find('[data-deck-editor-row-delete]'))
		.attr('data-deck-editor-row-delete', `new-${num}`);

		////////

		box.prepend(tpl.html());

		return false;
	};

	static OnClickEditorRowDelete(jEv) {

		let that = jQuery(jEv.currentTarget);
		let rid = that.attr('data-deck-editor-row-delete');

		////////

		if(confirm('Delete Row?'))
		jQuery(`#row-${rid}, #hr-${rid}`).remove();

		return false;
	};

	static OnClickEditorRowMoveUp(jEv) {

		let that = jQuery(jEv.currentTarget);
		let rid = that.attr('data-deck-editor-row-move-up');

		let box = this.FetchDeckEditor();
		let row = box.find(`#row-${rid}`);
		let pin = row.prev();

		if(pin)
		pin.before(row);

		return false;
	};


	static OnClickEditorRowMoveDown(jEv) {

		let that = jQuery(jEv.currentTarget);
		let rid = that.attr('data-deck-editor-row-move-down');

		let box = this.FetchDeckEditor();
		let row = box.find(`#row-${rid}`);
		let pin = row.next();

		if(pin)
		pin.after(row);

		return false;
	};

	static OnClickEditorSave(jEv) {

		let api = new API.Request('PATCH', '/ops/deckmgr/api');
		let name = jQuery('[data-deck-name]').val();
		let alias = jQuery('[data-deck-alias]').val();

		let box = this.FetchDeckEditor();
		let rows = box.find('[data-deck-editor-row]');

		let data = {
			"Name": name,
			"Alias": alias,
			"Items": []
		};

		////////

		rows.each(function(idx, r) {

			let that = jQuery(r);
			let uuid = that.attr('data-deck-editor-row');
			let img = that.find('[name="ImageURL"]').val();
			let link = that.find('[name="LinkURL"]').val();

			////////

			if(uuid === 'template')
			uuid = '';

			(data.Items)
			.push({ "UUID": uuid, "ImageURL": img, "LinkURL": link });

			return;
		});

		////////

		data.Items = JSON.stringify(data.Items);

		(api.send(data))
		.then(function(r){
			console.log(r);
			return;
		});

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static #NewRowIterator = 0;
	static NewRowIterator() { return (++DeckManager.#NewRowIterator); };

	static FetchDeckEditor() { return jQuery('[data-deck-editor]'); };
	static FetchDeckEditorTemplate() { return jQuery('[data-deck-editor-template]').clone(); };

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default DeckManager;
