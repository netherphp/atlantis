////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../api/json.js';
import NetherOS from '../__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateDataInputTextRow = `
<div class="d-flex gap-2 mb-3" data-key-val-row>
	<div class="flex-fill">
		<input type="text" class="form-control" placeholder="Key..." data-key />
	</div>
	<div class="flex-fill">
		<input type="text" class="form-control" placeholder="Value..." data-val data-val-text />
	</div>
</div>
`;

let TemplateToolWindowHTML = `
<div class="d-flex h-100 gap-4">
	<div class="flex-grow-0">
		<div class="d-flex flex-column h-100">
			<div class="flex-grow-0 mb-2 fw-bold">Verb</div>
			<div class="flex-grow-0 mb-2">
				<input type="text" class="form-control" data-win-input="Verb" value="GET" />
			</div>
			<div class="flex-grow-0 mb-2 fw-bold">URL</div>
			<div class="flex-grow-0 mb-2">
				<input type="text" class="form-control" data-win-input="URL" />
			</div>
			<div class="flex-grow-0 mb-2 fw-bold">Dataset</div>
			<div class="flex-grow-1 pos-relative">
				<div class="pos-absolutely" style="overflow:scroll;">
					${TemplateDataInputTextRow}

					<button class="atl-dtop-btn atl-dtop-btn-alt btn-block" data-win-action="add-key-value">
						<i class="mdi mdi-plus"></i>
						Add Key/Value
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="flex-grow-1 pos-relative">
		<div class="d-flex flex-column gap-4 h-100">
			<div class="flex-grow-0 mb-2">
				<button class="atl-dtop-btn btn-block" data-win-action="run">
					Run
				</button>
			</div>
			<div class="flex-grow-1 pos-relative">
				<div class="pos-absolutely" style="overflow:scroll;border-left:3px solid var(--atl-dtop-cfg-colour-primary);">
					<pre class="pl-2" data-win-output="Output">{ API Output }</pre>
				</div>
			</div>
		</div>

	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ApiTool
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('API Tool')
		.setIdent('net.pegasusgate.atl.apitool')
		.setIcon('mdi mdi-api')
		.setListed(true)
		.setTaskbarItem(true);

		return;
	};

	onLaunchInstance() {

		let w = new ApiToolWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return this;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ApiToolWindow
extends NetherOS.Window {

	onConstruct() {

		this.setSize(80, 75, '%');
		this.setBody(TemplateToolWindowHTML);

		this.elBtnAddKeyVal = this.element.find('[data-win-action="add-key-value"]');

		this.setAction('add-key-value', this.onKeyValue);
		this.setAction('run', this.onRun);

		this.hideFooter();

		return;
	};

	readKeyValueFields() {

		let output = new FormData;
		let box = this.elBtnAddKeyVal.parent();
		let rows = box.find('[data-key-val-row]');

		rows.each(function() {
			let t = jQuery(this);
			let k = null;
			let v = null;
			let inKey = t.find('[data-key]');
			let inVal = t.find('[data-val]');

			////////

			k = inKey.val();

			if(inVal.is('[data-val-text]'))
			v = inVal.val();

			////////

			output.append(k, v);

			return;
		});

		return output;
	};

	onRun() {

		let verb = this.getInputValue('Verb');
		let url = this.getInputValue('URL');
		let data = this.readKeyValueFields();
		let api = new API.Request(verb, url);

		if(verb === 'GET')
		data = Object.fromEntries(data.entries());

		(api.send(data))
		.then(this.onResponse.bind(this))
		.catch(this.onResponse.bind(this));

		return;
	};

	onKeyValue() {

		let freshSet = jQuery(TemplateDataInputTextRow);

		this.elBtnAddKeyVal.before(freshSet);

		return;
	};

	onResponse(r) {

		let el = this.getOutputElement('Output');

		let json = JSON.stringify(r, null, 4);

		el.text(json);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default ApiTool /////////////////////////////////////////////////////////