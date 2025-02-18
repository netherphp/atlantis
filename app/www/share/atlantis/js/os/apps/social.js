////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let Config = {
	'OverviewVerb': 'GET',
	'OverviewURL': '/api/social/overview'
};

let ThirdParty = {
	'Scripts': [
		'https://cdn.jsdelivr.net/npm/chart.js@4.4',
		'https://cdn.jsdelivr.net/npm/moment@2.30',
		'https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0'
	]
};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateSocialOverviewHTML = `
<div style="max-height: 40svh; overflow-y: scroll;">
	<table class="table w-100">
		<thead>
			<tr>
				<th class="th-shrink ta-center"></th>
				<th class="th-grow ta-left">Account</th>
				<th class="th-shrink ta-center">Followers</th>
				<th class="th-shrink ta-center"></th>
			</tr>
		</thead>
		<tbody data-win-output="Accounts">
		</tbody>
	</table>
</div>
`;

let TemplateSocialOverviewRowHTML = `
<tr>
	<td class="ta-center" data-account-service-icon></td>
	<td class="ta-left" data-account-handle-text></td>
	<td class="ta-center" data-account-followers-text></td>
	<td class="ta-center"><a class="btn atl-dtop-btn" href="" target="_blank" title="Open in New Tab" data-account-url-href><i class="mdi mdi-open-in-new mr-0"></i></a></td>
</tr>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SocialApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Social Data')
		.setIdent('net.pegasusgate.atl.admin.social')
		.setIcon('mdi mdi-account-details');

		this.hackInTheDeps();

		return;
	};

	onLaunchInstance() {

		let w = new SocialAppWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	async hackInTheDeps() {

		// chart.js and its deps depend on being loaded in sequental order
		// so here is a little promise irrigation.

		let scripts = ThirdParty.Scripts.map(
			this.prepareScriptElement
		);

		for(const script of scripts)
		await this.loadScriptPromised(script);

		return;
	};

	prepareScriptElement(url) {

		// make an html script element out of a url.

		let script = jQuery('<script />');

		script.attr('type', 'text/javascript');
		script.attr('src', url);

		return script;
	};

	loadScriptPromised(script) {

		// trigger the loading and return a promise.

		return new Promise(function(yah, nah) {

			script.on('load', yah);
			document.body.appendChild(script.get(0));

			return;
		});
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SocialAppWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setBody(TemplateSocialOverviewHTML)
		.setSize(50, 50, '%');

		this.elAccounts = this.getOutputElement('Accounts');

		this.addButton('OK', NetherOS.Window.ActionAccept);
		this.showOverlay();


		return;
	};

	onShown() {

		this.fetchOverview();

		return;
	};

	fetchOverview() {

		let api = new NetherOS.API.Request(
			Config.OverviewVerb,
			Config.OverviewURL
		);

		(api.send())
		.then(this.onOverviewPayload.bind(this))
		.catch(api.catch);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onOverviewPayload(result) {

		this.elAccounts.empty();

		for(const a of result.payload) {
			let row = jQuery(TemplateSocialOverviewRowHTML);
			let icon = jQuery('<i />').addClass(a.Icon);

			row.find('[data-account-handle-text]').text(a.Handle);
			row.find('[data-account-followers-text]').text(a.Followers);
			row.find('[data-account-service-icon]').html(icon);
			row.find('[data-account-url-href]').attr('href', a.URL);

			this.elAccounts.append(row);
		}

		this.hideOverlay();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default SocialApp; //////////////////////////////////////////////////////


