////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../../../nui/api/json.js';
import NetherOS from '../../../../nui/desktop/main.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateUserLogoutWindow = `
<div class="container-fluid g-0">
	<div class="row align-items-center g-2">
		<div class="col-auto">
			<i class="mdi mdi-bomb fs-mostest-large"></i>
		</div>
		<div class="col">
			Are you sure you want to log out?
		</div>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class LogOutApp
extends NetherOS.App {

	onConstruct() {

		this.setName('Log Out');
		this.setIdent('net.pegasusgate.atl.logoutapp');
		this.setIcon('mdi mdi-logout');
		this.pushToTaskbar(true);

		this.win = null;

		return;
	};

	onLaunch(os) {

		super.onLaunch(os);

		this.win = new UserLogoutWindow(this);

		return;
	};

};

class UserLogoutWindow
extends NetherOS.Window {

	constructor(app) {

		super();

		this.setup(app);
		this.show();
		this.centerInParent();

		return;
	};

	setup(app) {

		this.setOS(app.os);
		this.setTitle(app.name);
		this.setIcon(app.icon);
		this.setMaxable(false);
		this.setMinable(false);
		this.setResizable(false);
		this.setMovable(false);
		this.setBody(TemplateUserLogoutWindow);

		this.addButton('OK Bye', 'logout');
		this.addButton('Cancel', 'cancel', true);
		this.setAction('logout', this.onLogout);

		this.pushToDesktop();

		return;
	};

	onLogout(jEv) {

		let api = new API.Request('LOGOUT', '/api/user/session');

		////////

		(api.send())
		.then(this.onLogoutResult.bind(this))
		.catch(api.catch);

		return false;
	};

	onLogoutResult(result) {

		location.reload();

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default LogOutApp; ///////////////////////////////////////////////////////
