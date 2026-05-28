import API from '/share/nui/api/json.js';
import Dialog from '/share/nui/util/dialog.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserEntityUtil {

	static FormPrivGrant(el) {

		let that = jQuery(el);
		let id = that.attr('data-id');
		let key = that.find('input[name="PrivKey"]').val();
		let val = that.find('input[Name="PrivVal"]').val();

		let api = new API.Request('GRANT', '/api/user/entity');
		let data = { 'ID': id, 'Key': key, 'Value': val };

		(api.send(data))
		.then(function(r) {
			location.reload();
			return;
		})
		.catch(api.catch);

		return false;
	};

	////////////////////////////////
	////////////////////////////////

	static DialogDelete(el) {

		let that = jQuery(el);
		let id = that.attr('data-id');

		return this.FetchThen(id, function(r) {
			return new Dialog.Window({
				'title': 'Confirm...',
				'body': (`
					<div class="mb-4">Really delete this user account? This cannot be undone.</div>
					<blockquote class="quotron fs-larger fw-bold">${r.payload.User.Email}</blockquote>
				`),
				'show': true,
				'classAccept': 'btn-red tc-white',
				'onAccept': function() {
					let api = new API.Request('DELETE', '/api/user/entity');
					let dat = { 'ID': id };

					(api.send(dat))
					.then(api.reload)
					.catch(api.catch);

					return;
				}
			});
		});
	};

	static DialogPrivDelete(el) {

		let that = jQuery(el);
		let id = that.attr('data-access-id');

		return new Dialog.Window({
			'title': 'Revoke Privileges...',
			'show': true,
			'onAccept': function() {
				let api = new API.Request('REVOKE', '/api/user/entity');
				let dat = { 'AccessID': id };

				(api.send(dat))
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

	};

	////////////////////////////////
	////////////////////////////////

	static FetchThen(id, then) {

		let api = new API.Request('GET', '/api/user/entity');
		let data = { 'ID': id };

		(api.send(data))
		.then(then)
		.catch(api.catch);

		return;
	};

	////////////////////////////////
	////////////////////////////////

	static WhenDocumentReady() {

		jQuery('[data-user-priv-grant-form]')
		.on('submit', (jEv)=> this.FormPrivGrant(jEv.currentTarget));

		jQuery('[data-user-delete]')
		.on('click', (jEv)=> this.DialogDelete(jEv.currentTarget));

		jQuery('[data-user-priv-delete]')
		.on('click', (jEv)=> this.DialogPrivDelete(jEv.currentTarget));

		return;
	};

};

export default UserEntityUtil;
