////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

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
		this.setSingleInstance(true);
		this.setTaskbarItem(true);

		return;
	};

	onLaunchSingle() {

		let w = new UserLogoutWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserLogoutWindow
extends NetherOS.Window {

	onConstruct() {

		this.setMaxable(false);
		this.setMinable(false);
		this.setResizable(false);
		this.setMovable(false);
		this.setBody(TemplateUserLogoutWindow);

		this.addButton('OK Bye', 'logout');
		this.addButton('Cancel', 'cancel', true);
		this.setAction('logout', this.onLogout);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onLogout(jEv) {

		let api = new NetherOS.API.Request('LOGOUT', '/api/user/session');

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