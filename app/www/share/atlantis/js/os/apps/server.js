////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let ServerAppWindowTemplate = `
<div class="pos-absolutely p-2" style="display:grid;">
	<div class="fw-bold mb-2">$_SERVER from PHP</div>
	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Name</th>
					<th>Value</th>
				</tr>
			</thead>
			<tbody data-win-output="ServerVar">

			</tbody>
		</table>
	</div>
</div>
`;

let ServerAppServerVarTemplate = `
<tr>
	<td class="fs-smaller fw-bold" data-var-key-text></td>
	<td class="ff-mono tw-harder" data-var-val-text></td>
</tr>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ServerApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Server')
		.setIdent('net.pegasusgate.atl.server')
		.setIcon('mdi mdi-web-box')
		.setListed(true)
		.setSingleInstance(true);

		return;
	};

	onLaunchSingle() {

		let w = new ServerAppWindow(this);

		this.registerWindow(w);

		w.show();
		w.centerInParent();
		w.maximise();

		return;
	};

};

class ServerAppWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setTitle('Server Info')
		.setBody(ServerAppWindowTemplate);

		return;
	};

	onReady() {

		let api = new NetherOS.API.Request('GET', '/api/atlantos/v1/server');

		(api.send())
		.then(this.onFetchServerInfo.bind(this))
		.catch(api.catch);

		return;
	};

	onFetchServerInfo(result) {

		let output = this.getOutputElement('ServerVar');
		let buffer = [];

		for(const key in result.payload.ServerVar) {
			let row = jQuery(ServerAppServerVarTemplate);

			row.find('[data-var-key-text]').text(key);
			row.find('[data-var-val-text]').text(result.payload.ServerVar[key]);

			buffer.push(row);
		}

		output.empty();
		output.append(buffer);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default ServerApp; //////////////////////////////////////////////////////
