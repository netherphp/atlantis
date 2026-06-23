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

		jQuery('[data-deck-editor-save]')
		.on('click', DeckManager.OnClickEditorSave);

		jQuery('[data-deck-editor-row-add]')
		.on('click', DeckManager.OnClickEditorRowAdd);

		jQuery('[data-deck-editor-row-delete]')
		.on('click', DeckManager.OnClickEditorRowDelete);

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

		alert('todo: add row');

		return false;
	};

	static OnClickEditorRowDelete(jEv) {

		let that = jQuery(this);
		let uuid = that.attr('data-deck-editor-row-delete');

		if(confirm('Delete Row?'))
		jQuery(`#row-${uuid}, #hr-${uuid}`).remove();

		return false;
	};

	static OnClickEditorSave(jEv) {

		alert('todo: save');

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default DeckManager;
