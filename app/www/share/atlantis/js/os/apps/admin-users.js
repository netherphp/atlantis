////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateUserSearchWindow = `
<div class="d-flex flex-column h-100">
	<div class="flex-shrink-1 flex-grow-0 mb-2">
		<input type="text" class="form-control w-100" placeholder="Search..." data-win-input="Query" />
	</div>
	<div class="flex-shrink-1 flex-grow-0 mb-2">
		<div class="row justify-content-end align-items-center g-2">
			<div class="col-auto">
				<span class="fw-bold">Search By:</span>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn" data-win-action="search-alias">Alias</button>
			</div>
			<div class="col-auto">
				<button class="atl-dtop-btn" data-win-action="search-email">Email</button>
			</div>
		</div>
	</div>
	<div class="flex-shrink-0 flex-grow-1 pos-relative">
		<div class="pos-absolutely" style="overflow:scroll;">
			<table class="table w-100 m-0 g-2">
				<thead>
					<tr>
						<th class="th-shrink">Alias</th>
						<th class="th-shrink">Email</th>
						<th class="th-grow"></th>
						<th class="th-shrink" style=""></th>
					</tr>
				</thead>
				<tbody data-win-output="SearchResults"></tbody>
			</table>
		</div>
	</div>
</div>
`;

let TemplateUserSearchRow = `
<tr>
	<td class="text-nowrap" data-user-alias-text></td>
	<td class="text-nowrap" data-user-email-text></td>
	<td class=""></td>
	<td class="text-nowrap">
		<button class="atl-dtop-btn" data-win-action="edit-user" data-user-id="">
			<i class="mdi mdi-pencil"></i>
			Edit
		</button>
	</td>
</tr>
`;

let TemplateUserEditWindow = `
<div class="d-flex flex-column h-100">
	<div class="flex-shrink-1 flex-grow-0">
		<div class="mb-2">
			<div class="fw-bold">Email</div>
			<input class="form-control" data-win-input="Email" />
		</div>
		<div class="mb-2">
			<div class="fw-bold">Alias</div>
			<input class="form-control" data-win-input="Alias" />
		</div>
		<div class="mb-2">
			<div class="fw-bold">Admin</div>
			<select class="form-select" data-win-input="Admin">
				<option value="0">No</option>
				<option value="1">Normal Admin</option>
				<option value="65535">Ultra Admin</option>
			</select>
		</div>
	</div>
	<div class="flex-shrink-1">
		<div class="fw-bold">Access Privs</div>
		<div class="mb-0">
			<div class="d-flex gap-2">
				<div class="flex-grow-1">
					<input class="form-control" placeholder="Key..." data-win-input="NewPrivKey" />
				</div>
				<div class="flex-grow-1">
					<input class="form-control" placeholder="Value..." data-win-input="NewPrivVal" />
				</div>
				<div class="flex-grow-0">
					<button class="atl-dtop-btn" data-win-action="priv-add">
						<i class="mdi mdi-plus"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="flex-shrink-0 flex-grow-1 pos-relative">
		<div class="pos-absolutely" style="overflow:scroll;">
			<table class="table w-100 m-0 g-2">
				<thead>
					<tr>
						<th class="th-grow">Key</th>
						<th class="th-shrink">Value</th>
						<th class="th-shrink"></th>
					</tr>
				</thead>
				<tbody data-win-output="AccessPrivs"></tbody>
			</table>
		</div>
	</div>
</div>
`;

let TemplateUserEditPrivRow = `
<tr>
	<td data-key class="ff-mono "></td>
	<td data-val class="ff-mono ta-right"></td>
	<td><button class="atl-dtop-btn" data-win-action="priv-del" data-access-id><i class="mdi mdi-close"></i></button></td>
</tr>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AdminUserApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('User Admin')
		.setIdent('net.pegasusgate.atl.admin.users')
		.setIcon('mdi mdi-account-multiple')
		.setTaskbarItem(true);

		return;
	};

	onLaunchInstance() {

		let w = new UserSearchWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserSearchWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setSize(30, 75, '%')
		.setBody(TemplateUserSearchWindow);

		this.setAction('search-alias', this.onSearchByAlias);
		this.setAction('search-email', this.onSearchByEmail);
		this.setAction('edit-user', this.onEditUser);

		this.onSearchByRecent();

		return;
	};

	onSearchByRecent(jEv) {

		let api = new NetherOS.API.Request('SEARCH', '/api/user/entity');
		let data = { 'Sort': 'newest' };

		////////

		this.onSearchSend(api, data);

		return false;
	};

	onSearchByAlias(jEv) {

		let api = new NetherOS.API.Request('SEARCH', '/api/user/entity');
		let data = { 'Alias': this.getInputValue('Query') };

		////////

		this.onSearchSend(api, data);

		return false;
	};

	onSearchByEmail(jEv) {

		let api = new NetherOS.API.Request('SEARCH', '/api/user/entity');
		let data = { 'Email': this.getInputValue('Query') };

		////////

		this.onSearchSend(api, data);

		return false;
	};

	onSearchSend(api, data) {

		let self = this;
		let output = this.getOutputElement('SearchResults');

		////////

		output.empty();

		////////

		(api.send(data))
		.then(function(results) {
			self.onSearchResults(results);
			return;
		})
		.catch(api.catch);

		////////

		return false;
	};

	onSearchResults(result) {

		let output = this.getOutputElement('SearchResults');

		////////

		output.empty();

		for(const u of result.payload.Results) {
			let row = jQuery(TemplateUserSearchRow);

			row.find('[data-user-alias-text]').text(u.Alias || '-- No Alias Set --');
			row.find('[data-user-email-text]').text(u.Email);
			row.find('[data-user-id]').attr('data-user-id', u.ID);

			output.append(row);
		}

		//this.resizeToFit();

		////////

		return false;
	};

	onEditUser(jEv) {

		let that = jQuery(jEv.target);
		let uid = that.attr('data-user-id');

		let win = new UserEditWindow(this.app);

		this.app.registerWindow(win);
		win.fetchUserInfo(uid);
		win.show();
		win.setPositionBasedOn(this);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserEditWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setSize(40, 80, '%')
		.setBody(TemplateUserEditWindow);

		this.uid = null;

		this.addButton('Save', 'save-user');
		this.setAction('save-user', this.onSaveUser);

		this.addButton('Cancel', 'cancel', true);
		this.setAction('priv-add', this.onPrivAdd);
		this.setAction('priv-del', this.onPrivDel);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	setUserID(uid) {

		this.uid = uid;

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	fetchUserInfo(uid) {

		let api = new NetherOS.API.Request('GET', '/api/user/entity');
		let data = { ID: uid };

		this.setUserID(uid);
		this.showOverlay();

		(api.send(data))
		.then(this.onFetchUser.bind(this))
		.catch(api.catch);

		return this;
	};

	onFetchUser(result) {

		this.getInputElement('Alias').val(result.payload.User.Alias);
		this.getInputElement('Email').val(result.payload.User.Email);
		this.getInputElement('Admin').val(result.payload.User.Admin);

		this.setTitle(`User Edit: #${result.payload.User.ID}`);

		let el = this.getOutputElement('AccessPrivs');
		el.empty();

		for(const p in result.payload.Access) {
			let pr = jQuery(TemplateUserEditPrivRow);
			pr.find('[data-key]').text(result.payload.Access[p].Key);
			pr.find('[data-val]').text(result.payload.Access[p].Value);
			pr.find('[data-access-id]').attr('data-access-id', result.payload.Access[p].ID);
			el.append(pr);
		}

		this.hideOverlay();

		return false;
	};

	onSaveUser(jEv) {

		let api = new NetherOS.API.Request('PATCH', '/api/user/entity');
		let data = {
			'ID': this.uid,
			'Alias': this.getInputValue('Alias'),
			'Email': this.getInputValue('Email'),
			'Admin': this.getInputValue('Admin')
		};

		(api.send(data))
		.then(this.onSaveDone.bind(this))
		.catch(api.catch);

		return;
	};

	onSaveDone() {

		this.quit();

		return;
	};

	onPrivAdd(jEv) {

		let api = new NetherOS.API.Request('SETACCESS', '/api/user/entity');
		let k = this.getInputValue('NewPrivKey');
		let v = this.getInputValue('NewPrivVal');

		let data = {
			"ID": this.uid,
			"Key": k,
			"Value": v
		};

		(api.send(data))
		.then(()=> this.fetchUserInfo(this.uid))
		.catch(api.catch);

		this.getInputElement('NewPrivKey').val('');
		this.getInputElement('NewPrivVal').val('');

		return this;
	};

	onPrivDel(jEv) {

		let that = jQuery(jEv.target);
		let aid = that.attr('data-access-id');
		let api = new NetherOS.API.Request('DELACCESS', '/api/user/entity');
		let data = { 'ID': this.uid,  'AccessID': aid };

		(api.send(data))
		.then(()=> this.fetchUserInfo(this.uid))
		.catch(api.catch);

		return this;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default AdminUserApp; ///////////////////////////////////////////////////
