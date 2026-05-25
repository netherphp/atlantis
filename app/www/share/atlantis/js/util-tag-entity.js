////////////////////////////////////////////////////////////////////////////////
// TagEnityUtil ////////////////////////////////////////////////////////////////

// Date: 2026-05-25

import API from '/share/nui/api/json.js';
import Dialog from '/share/nui/util/dialog.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class TagEntityUtil {

	static DialogNew(el) {

		alert('todo: new');

		return;
	};

	static DialogDelete(el) {

		let that = jQuery(el);
		let id = that.attr('data-id');

		return this.FetchThen(id, function(r) {
			new Dialog.Window({
				'title': 'Confirm...',
				'body': (`
					<div class="mb-4">Really delete this tag? This cannot be undone.</div>
					<blockquote class="quotron fs-larger fw-bold">${r.payload.Name}</blockquote>
				`),
				'show': true,
				'classAccept': 'btn-red tc-white',
				'onAccept': function() {

					let api = new API.Request('DELETE', '/api/tag/entity');
					let dat = { 'ID': id };

					(api.send(dat))
					.then(api.reload)
					.catch(api.catch);

					return;
				}
			});
		});

		return;
	};

	////////////////////////////////
	////////////////////////////////

	static FetchThen(id, then) {

		let api = new API.Request('GET', '/api/tag/entity');
		let dat = { 'ID': id };

		(api.send(dat))
		.then(then)
		.catch(api.catch);

		return;
	};

	////////////////////////////////
	////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-tag-new]')
		.on('click', (jEv)=> this.DialogNew(jEv.currentTarget));

		jQuery('[data-tag-delete]')
		.on('click', (jEv)=> this.DialogDelete(jEv.currentTarget));

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default TagEntityUtil;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
