import API from '/share/nui/api/json.js';
import Dialog from '/share/nui/util/dialog.js';
import FieldTagSearch from '/share/atlantis/js/field-tag-search.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ProfileUtil {

	static DialogNew(el) {
		return new Dialog.Window({
			'title': 'New Profile...',
			'show': true,
			'fields': [
				new Dialog.Field({
					type: 'text3', title: 'Title', name: 'Title'
				})
			],
			'onAccept': function() {
				let input = this.getFieldData();
				let api = new API.Request('POST', '/api/profile/entity');
				let dat = { 'Title': input['Title'] };

				(api.send(dat))
				.then(function(r){
					location.href = `?uuid=${r.payload.UUID}`;
					return;
				})
				.catch(api.catch);

				return;
			}
		});
	};

	static DialogDelete(el) {

		let that = jQuery(el);
		let id = that.attr('data-id');

		return this.FetchThen(id, function(r) {
			return new Dialog.Window({
				'title': 'Confirm...',
				'body': (`
					<div class="mb-4">Really delete this profile? This cannot be undone.</div>
					<blockquote class="quotron fs-larger fw-bold">${r.payload.Title}</blockquote>
				`),
				'show': true,
				'classAccept': 'btn-red tc-white',
				'onAccept': function() {
					let api = new API.Request('DELETE', '/api/profile/entity');
					let dat = { 'ID': id };

					(api.send(dat))
					.then(api.reload)
					.catch(api.catch);

					return;
				}
			});
		});
	};

	static DialogPublish(el, value) {

		let that = jQuery(el);
		let id = that.attr('data-id');
		let msg = '';

		////////

		if(value === 1)
		msg = 'Publish this profile?';

		if(value === 0)
		msg = 'Set this profile to draft mode?';

		////////

		return this.FetchThen(id, function(r) {
			return new Dialog.Window({
				'title': 'Confirm...',
				'body': (`
					<div class="mb-4">${msg}</div>
					<blockquote class="quotron fs-larger fw-bold">${r.payload.Title}</blockquote>
				`),
				'show': true,
				'classAccept': 'btn-red tc-white',
				'onAccept': function() {
					let api = new API.Request('PATCH', '/api/profile/entity');
					let dat = { 'ID': id, 'Enabled': value };

					(api.send(dat))
					.then(api.reload)
					.catch(api.catch);

					return;
				}
			});
		});
	};

	////////////////////////////////
	////////////////////////////////

	static FetchThen(id, then) {

		let api = new API.Request('GET', '/api/profile/entity');
		let data = { 'ID': id };

		(api.send(data))
		.then(then)
		.catch(api.catch);

		return;
	};

	////////////////////////////////
	////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-profile-new]')
		.on('click', (jEv)=> this.DialogNew(jEv.currentTarget));

		jQuery('[data-profile-delete]')
		.on('click', (jEv)=> this.DialogDelete(jEv.currentTarget));

		jQuery('[data-profile-set-published]')
		.on('click', (jEv)=> this.DialogPublish(jEv.currentTarget, 1));

		jQuery('[data-profile-set-draft]')
		.on('click', (jEv)=> this.DialogPublish(jEv.currentTarget, 0));

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default ProfileUtil;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
