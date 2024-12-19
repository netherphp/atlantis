////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateUserLoginWindow = `
<div class="g-2">
	<div class="mb-2">
		<input type="text" class="form-control" placeholder="Email..." data-win-input="Account" />
	</div>
	<div class="mb-0">
		<input type="password" class="form-control" placeholder="Password..." data-win-input="Password" />
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class LoginApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Log In')
		.setIdent('net.pegasusgate.atl.login')
		.setIcon('mdi mdi-login')
		.setListed(false)
		.setSingleInstance(true)
		.setTaskbarItem(true);

		return;
	};

	onInstalled() {

		setTimeout(this.onLaunchSingle.bind(this), 250);

		return;
	};

	onLaunchSingle() {

		let w = new UserLoginWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UserLoginWindow
extends NetherOS.Window {

	onConstruct() {

		this.setMaxable(false);
		this.setMinable(false);
		this.setResizable(false);
		this.setMovable(false);
		this.setBody(TemplateUserLoginWindow);

		this.addButton('Submit', 'login');
		this.setAction('login', this.onLogin);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onLogin() {

		let api = new NetherOS.API.Request('LOGIN', '/api/user/session');

		let data = {
			'Username': this.getInputValue('Account'),
			'Password': this.getInputValue('Password')
		};

		////////

		(api.send(data))
		.then(this.onLoginResult.bind(this))
		.catch(api.catch);

		return false;
	};

	onLoginResult(result) {

		location.reload();

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default LoginApp; ///////////////////////////////////////////////////////
