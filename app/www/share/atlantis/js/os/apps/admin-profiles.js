////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateProfileSearchWindow = `
<div class="d-flex flex-column h-100">

	<div class="flex-shrink-1 flex-grow-0 mb-2">
		<div class="input-group">
			<input type="text" class="form-control w-100" placeholder="Search..." data-win-input="Query" />
		</div>
	</div>

	<div class="flex-shrink-1 flex-grow-0 mb-2">
		<div class="row justify-content-end align-items-center g-2">
			<div class="col-auto">
				<button class="atl-dtop-btn atl-dtop-btn-alt" data-win-action="new">
					<i class="mdi mdi-plus"></i>
					New
				</button>
			</div>
			<div class="col-auto">|</div>
			<div class="col-auto">
				<button class="atl-dtop-btn" data-win-action="search">
					<i class="mdi mdi-magnify"></i>
					Search
				</button>
			</div>
		</div>
	</div>

	<div class="flex-shrink-0 flex-grow-1 pos-relative">
		<div class="pos-absolutely" style="overflow-y: scroll;">
			<div class="row" data-win-output="SearchResults">
			</div>
		</div>
	</div>

</div>
`;

let TemplateProfileSearchRow = `
<div class="col-12" data-profile-id="0">
	<div class="row align-items-center g-2">
		<div class="col">
			<div>
				<a href="" target="_blank" data-profile-title-text data-profile-href></a>
			</div>
			<div>
 				<span class="o-50 fs-smallerer fst-italic" data-profile-alias-text></span>
			</div>
		</div>
		<div class="col-auto">
			<a href="" target="_blank" class="btn atl-dtop-btn" data-profile-href>
				Goto
			</a>
		</div>
		<div class="col-auto">
			<button class="btn atl-dtop-btn" data-win-action="edit">
				<i class="mdi mdi-cog"></i>
			</button>
		</div>
	</div>
	<div class="col-12">
		<hr />
	</div>
</tr>
`;

let TemplateUserEditWindow = `
<div class="d-flex gap-4 h-100">
	<div class="flex-fill h-100 w-50">

		<table class="table">
			<thead>
				<tr>
					<th class="th-shrink text-nowrap">User Info</th>
					<th class="th-grow text-nowrap"></th>
					<th class="th-shrink text-nowrap"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-nowrap">Email</td>
					<td class="text-nowrap"><input type="text" class="form-control" data-win-input="Email" /></td>
					<td class="text-nowrap">
						<button class="atl-dtop-btn">
							Save
						</button>
					</td>
				</tr>
				<tr>
					<td class="text-nowrap">Alias</td>
					<td class="text-nowrap"><input type="text" class="form-control" data-win-input="Alias" /></td>
					<td class="text-nowrap">
						<button class="atl-dtop-btn">
							Save
						</button>
					</td>
				</tr>
				<tr>
					<td class="text-nowrap">Admin</td>
					<td class="text-nowrap">
						<select class="form-select" data-win-input="Admin">
							<option value="0">No</option>
							<option value="1">Normal Admin</option>
							<option value="65535">Ultra Admin</option>
						</select>
					</td>
					<td class="text-nowrap">
						<button class="atl-dtop-btn">
							Save
						</button>
					</td>
				</tr>
			</tbody>
		</table>

	</div>
	<div class="flex-fill h-100 w-50">

		<div class="d-flex flex-column h-100">
			<div class="flex-grow-0">

				<div class="d-flex">
					<div><input class="form-control" placeholder="Key..." data-win-input="NewPrivKey" /></div>
					<div><input class="form-control" placeholder="Value..." data-win-input="NewPrivVal" /></div>
					<div>
						<button class="atl-dtop-btn" data-win-action="priv-add">
							<i class="mdi mdi-plus"></i>
						</button>
					</div>
				</div>

			</div>
			<div class="flex-grow-1 pos-relative h-100">

				<div class="pos-absolutely h-100" style="overflow:scroll;">
					<table class="table h-100 w-100 m-0 g-0">
						<thead>
							<tr>
								<th class="th-grow">Key</th>
								<th class="th-throw">Value</th>
								<th class="th-shrink"></th>
							</tr>
						</thead>
						<tbody data-win-output="AccessPrivs"></tbody>
					</table>
				</div>

			</div>
		</div>

	</div>
</div>
`;

let zTemplateUserEditWindow = `
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
		.setName('Profiles')
		.setIdent('net.pegasusgate.atl.admin.profiles')
		.setIcon('mdi mdi-note-multiple');

		return;
	};

	onLaunchInstance() {

		let w = new ProfileSearchWindow(this);

		this.registerWindow(w);

		w.setSize(75, 75, '%');
		w.show();
		w.centerInParent();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ProfileSearchWindow
extends NetherOS.Window {

	onConstruct() {

		this.setBody(TemplateProfileSearchWindow);
		this.setAction('search', this.onSearch);
		this.setAction('new', this.onNew)

		return;
	};

	onShown() {

		//this.onSearchByRecent();

		return;
	};

	onSearchByRecent(jEv) {

		let api = new NetherOS.API.Request('SEARCH', '/api/user/entity');
		let data = { 'Sort': 'newest' };

		////////

		this.onSearchSend(api, data);

		return false;
	};

	////////////////////////////////
	////////////////////////////////

	onSearch(jEv) {

		let api = new NetherOS.API.Request('SEARCH', '/api/profile/entity.v2');
		let data = { 'Query': this.getInputValue('Query') };

		////////

		this.onSearchSend(api, data);

		return false;
	};

	onSearchSend(api, data) {

		let self = this;
		let output = this.getOutputElement('SearchResults');

		////////

		// please wait instead of empty

		output.empty();

		////////

		(api.send(data))
		.then(function(results) {
			self.onSearchResults(results, output);
			return;
		})
		.catch(api.catch);

		////////

		return false;
	};

	onSearchResults(result, output) {

		output.empty();

		output.append(`
			<div class="col-12 ta-center py-2 fw-bold ff-mono tt-upper">
				Profiles Found: ${result.payload.length}
			</div>
			<div class="col-12">
				<hr />
			</div>
		`);

		for(const p of result.payload) {
			console.log(p);

			let row = jQuery(TemplateProfileSearchRow);
			row.find('[data-profile-id]').attr('data-profile-id', p.ID);
			row.find('[data-profile-href]').attr('href', p.PageURL);

			row.find('[data-profile-title-text]').text(p.Title || '-- No Title --');
			row.find('[data-profile-alias-text]').text(p.Alias || '-- No Alias --');

			output.append(row);
		}

		////////

		return false;
	};

	////////////////////////////////
	////////////////////////////////

	onNew(jEv) {

		let api = new NetherOS.API.Request('POST', '/api/profile/entity.v2');
		let data = { 'Title': 'New Profile' };

		(api.send(data))
		.then(function(r){
			console.log(r);
			return;
		})
		.catch(api.catch);

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserEditWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setSize(80, 80, '%')
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
