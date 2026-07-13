import API from '../../nui/api/json.js';
import Dialog from '../../nui/util/dialog.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ProfileUtil {

	static OnOpenEditProfileType(jEv) {

		let that = jQuery(jEv.currentTarget);
		let diag = null;

		let pid = parseInt(that.attr('data-profile-id'));
		let ptype = parseInt(that.attr('data-profile-ptype'));

		////////

		console.log(`[ProfileUtil] PType ${pid} ${ptype}`);

		////////

		diag = new Dialog.Window(new Dialog.WindowConfig({
			show: true,
			title: 'Change Profile Type...',
			body: 'Changing this will change some aspects of how the profile manages itself and what can be edited.',
			fields: [
				Dialog.Field.New({
					type: 'hidden',
					name: 'ID',
					value: pid
				}),
				Dialog.Field.New({
					type: 'select',
					title: 'Profile Type',
					name: 'PType',
					value: ptype,
					list: {
						'Default': 0,
						'Person': 1
					}
				 })
			],
			onAccept: function() {
				let data = diag.getFieldData();
				let api = new API.Request('PATCH', '/api/profile/entity', data);

				(api.send())
				.then(function(r) {
					location.href = r.goto;
					return;
				})
				.catch(api.catch);

				return;
			}
		}));

		return false;
	};

	static OnOpenEditAliasPrefix(jEv) {

		let that = jQuery(jEv.currentTarget);
		let diag = null;

		let pid = parseInt(that.attr('data-profile-id'));
		let prefix = that.attr('data-profile-prefix');

		////////

		console.log(`[ProfileUtil] AliasPrefix ${pid} ${prefix}`);

		////////

		diag = new Dialog.Window(new Dialog.WindowConfig({
			show: true,
			title: 'Change Profile Prefix...',
			body: '<b>System Internal Use Only.</b> Change the prefix that is forced on profiles any time the Alias is updated.',
			fields: [
				Dialog.Field.New({
					type: 'hidden',
					name: 'ID',
					value: pid
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'Alias Prefix',
					name: 'AliasPrefix',
					value: prefix
				})
			],
			onAccept: function() {
				let data = diag.getFieldData();
				let api = new API.Request('PATCH', '/api/profile/entity', data);

				(api.send())
				.then(function(r) {
					location.href = r.goto;
					return;
				})
				.catch(api.catch);

				return;
			}
		}));

		return false;
	};

	static OnOpenNewProfilePerson(jEv) {

		let that = jQuery(jEv.currentTarget);
		let diag = null;

		let ptype = parseInt(that.attr('data-profile-ptype') || '0');
		let prefix = that.attr('data-profile-prefix') || '';

		console.log(`[ProfileUtil] New Person (prefix: ${prefix})`);

		diag = new Dialog.Window(new Dialog.WindowConfig({
			show: true,
			title: 'New Profile [Type: Person]',
			fields: [
				Dialog.Field.New({
					type: 'hidden',
					name: 'PType',
					value: ptype
				}),
				Dialog.Field.New({
					type: 'hidden',
					name: 'AliasPrefix',
					value: prefix
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'Prefix (Mr., Dr., etc...)',
					name: 'ExtraData[NamePrefix]'
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'First Name',
					name: 'ExtraData[NameFirst]'
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'Middle Name',
					name: 'ExtraData[NameMiddle]'
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'Last Name',
					name: 'ExtraData[NameLast]'
				}),
				Dialog.Field.New({
					type: 'text3',
					title: 'Suffix (Sr., Jr., etc)',
					name: 'ExtraData[NameSuffix]'
				}),
			],
			onAccept: function() {

				let api = new API.Request('POST', '/api/profile/entity');
				let data = this.getFieldData();

				console.log(data);

				(api.send(data))
				.then((r)=> location.href = r.goto)
				.catch(api.catch);

				return;
			}
		}));

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {

		console.log('[ProfileUtil] Loaded');

		////////

		jQuery('[data-profile-cmd="ptype"]')
		.on('click', (jEv)=> this.OnOpenEditProfileType(jEv));

		jQuery('[data-profile-cmd="aliasprefix"]')
		.on('click', (jEv)=> this.OnOpenEditAliasPrefix(jEv));

		jQuery('[data-profile-cmd="new-person"]')
		.on('click', (jEv)=> this.OnOpenNewProfilePerson(jEv));

		return;
	};

};

export default ProfileUtil;
