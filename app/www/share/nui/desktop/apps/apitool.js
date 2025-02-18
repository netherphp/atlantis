////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../api/json.js';
import NetherOS from '../__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateDataInputTextRow = `
<div class="d-flex gap-2 mb-2" data-key-val-row>
	<div class="flex-fill">
		<input type="text" size="8" class="form-control" placeholder="Key..." data-key />
	</div>
	<div class="flex-fill">
		<input type="text" size="8" class="form-control" placeholder="Value..." data-val data-val-text />
	</div>
</div>
`;

let zTemplateToolWindowHTML = `
<div style="display: grid; grid-template-columns: 1fr 1fr 2fr;">
	<div>
			<div class="mb-2">
				<div class="fw-bold">Verb</div>
				<input type="text" class="form-control" data-win-input="Verb" value="GET" />
			</div>
			<div class="mb-2">
				<div class="fw-bold">URL</div>
				<input type="text" class="form-control" data-win-input="URL" />
			</div>
	</div>
	<div>
		<button class="atl-dtop-btn atl-dtop-btn btn-block" data-win-action="add-key-value">
			<i class="mdi mdi-plus"></i>
			Add Key/Value
		</button>
		<div class="scroll-y" data-win-output="KeyValueInput">
			${TemplateDataInputTextRow}
		</div>
	</div>
	<div></div>
</div>
`;

let TemplateToolWindowHTML = `
<style type="text/css">
.atl-apitool-win-grid {
	grid-template-columns: 1fr 1fr 1fr;
	grid-template-rows: auto fit-content(4lh) 1fr;
	grid-template-areas: 'i i i' 'd d d' 'o o o';
}

.atl-apitool-win-input { grid-area: i; }
.atl-apitool-win-data { grid-area: d; }
.atl-apitool-win-output { grid-area: o; }

@container atl-win-body (min-width: 576px) {
	.atl-apitool-win-grid {
		grid-template-columns: 1fr 1fr 1fr;
		grid-template-rows: auto 1fr 1fr;
		grid-template-areas: 'i o o' 'd o o' 'd o o';
	}
}
</style>

<div class="atl-apitool-win gridset">
	<div class="atl-apitool-win-grid gridbox gap-2 pos-absolutely">
		<div class="atl-apitool-win-input">
			<div class="mb-2">
				<div class="fw-bold">Verb</div>
				<input type="text" class="form-control" data-win-input="Verb" value="GET" />
			</div>
			<div class="mb-2">
				<div class="fw-bold">URL</div>
				<input type="text" class="form-control" data-win-input="URL" />
			</div>
			<div class="mb-2">
				<button class="atl-dtop-btn atl-dtop-btn btn-block" data-win-action="add-key-value">
					<i class="mdi mdi-plus"></i>
					Add Key/Value
				</button>
			</div>
		</div>
		<div class="atl-apitool-win-data scroll-y" data-win-output="KeyValueInput">
			${TemplateDataInputTextRow}
		</div>
		<div class="atl-apitool-win-output">
			<div class="gridbox position-absolutely" style="grid-template-rows: auto 1fr;">
				<div>
					<button class="atl-dtop-btn btn-block mb-2" data-win-action="run">
						Run
					</button>
				</div>
				<div>
					<div class="pos-absolutely scroll-xy" style="min-height:4lh;">
						<pre class="pl-2" data-win-output="Output">{ API Output }</pre>
					</div>
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
		.setListed(true);

		return;
	};

	onLaunchInstance() {

		let w = new ApiToolWindow(this);

		this.registerWindow(w);
		w.setSize(720, 480);
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

		//this.setSize(80, 75, '%');
		this.setBody(TemplateToolWindowHTML);

		this.elBtnAddKeyVal = this.element.find('[data-win-action="add-key-value"]');

		this.setAction('add-key-value', this.onKeyValue);
		this.setAction('run', this.onRun);

		this.hideFooter();

		return;
	};

	readKeyValueFields() {

		let output = new FormData;
		let box = this.getOutputElement('KeyValueInput');
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

			if(k)
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

		let box = this.getOutputElement('KeyValueInput');
		let freshSet = jQuery(TemplateDataInputTextRow);

		box.append(freshSet);

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