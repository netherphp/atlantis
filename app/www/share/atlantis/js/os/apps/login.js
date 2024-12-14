////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import OpSys from '../../../../nui/desktop/os.js';
import App from '../../../../nui/desktop/app.js';
import Win from '../../../../nui/desktop/window.js';
import API from '../../../../nui/api/json.js';

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
extends App {

	constructor() {

		super();
		this.setName('Log In');
		this.setIdent('net.pegasusgate.atl.login');
		this.setIcon('mdi mdi-login');

		this.loginWindow = null;

		return;
	};

	onInstall(os) {

		super.onInstall(os);
		super.pushToTaskbar();

		setTimeout(
			(()=> this.onLaunch(os)),
			250
		);

		return;
	};

	onLaunch(os) {

		super.onLaunch(os);

		this.loginWindow = new UserLoginWindow(this);

		return;
	};

};

class UserLoginWindow
extends Win {

	constructor(app) {

		super();

		this.setup(app);
		this.show();
		this.resizeToFit();
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
		this.setBody(TemplateUserLoginWindow);

		this.addButton('Submit', 'login');
		this.setAction('login', this.onLogin);

		this.pushToDesktop();

		return;
	};

	onLogin(jEv) {

		let api = new API.Request('LOGIN', '/api/user/session');

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
