import API from '../../nui/api/json.js';
import Form from '../../nui/util/form.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateApiToolDataItem = `
<div class="row tight align-items-center mb-2">
	<div class="col"><input type="text" name="DataKey" class="form-control" placeholder="Key..." /></div>
	<div class="col"><input type="text" name="DataValue" class="form-control" placeholder="Value..." /></div>
	<div class="col-auto">
		<button type="button" class="btn btn-link btn-sm link-danger font-size-large p-0 BtnDataDelete">
			<i class="mdi mdi-close-circle mr-0"></i>
		</button>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class ApiTool {

	constructor(selector='#ToolSend') {

		let self = this;

		////////

		this.element = jQuery(selector);
		this.datalist = this.element.find('#DatasetList');
		this.output = this.element.find('#ToolSendOutput');
		this.inputVerb = this.element.find('[name=Verb]');
		this.inputEndpoint = this.element.find('[name=Endpoint]');
		this.inputData = this.element.find('[name=Dataset]');

		////////

		(this.element)
		.on('click', '.BtnDataAppend', function() { return self.onDatasetAppend(); })
		.on('click', '.BtnDataDelete', function() { return self.onDatasetDelete(this); })
		.on('submit', this.onSendSubmit.bind(this));

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getDatasetData() {

		let output = {};

		(this.datalist.find('.row'))
		.each(function() {

			let that = jQuery(this);
			let dkey = that.find('[name=DataKey]').val();
			let dval = that.find('[name=DataValue]').val();

			output[dkey] = dval;

			return;
		});

		return output;
	};

	getSendData() {

		return {
			verb: this.inputVerb.val(),
			endpoint: this.inputEndpoint.val(),
			dataset: this.getDatasetData()
		};
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onDatasetAppend() {

		if(this.datalist.find('.jumbotron').length > 0)
		this.datalist.empty();

		////////

		this.datalist.append(
			jQuery(TemplateApiToolDataItem)
		);

		return;
	};

	onDatasetDelete(item) {

		jQuery(item)
		.parent()
		.parent()
		.remove();

		return;
	};

	onSendSubmit() {

		let data = this.getSendData();
		let api = new API.Request(data.verb, data.endpoint, data.dataset);

		(api.send())
		.then(this.onSendResult.bind(this))
		.catch(api.catch);

		return false;
	};

	onSendResult(result) {

		this.output.empty();

		this.output.text(JSON.stringify(result.payload.User, null, '\t'));

		return false;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default ApiTool;
