import ModalDialog from '../modules/modal/modal.js';
import Litepicker from '../../atlantis/lib/date/litepicker.js';
import Editor from '../modules/editor/editor.js';
import JsonAPI from '../api/json.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DialogWindowConfig {
/*//
this class defines the arguments the dialog window is able to take upon its
constructor dumped in object form.
//*/

	constructor({
		title, fields=[], body=null,
		labelAccept='OK', labelCancel='Cancel',
		onAccept=null,
		show=false, maximise=false
	}) {

		this.title = title;
		this.fields = Array.isArray(fields) ? fields : [];
		this.body = body;
		this.labelAccept = labelAccept;
		this.labelCancel = labelCancel;
		this.onAccept = onAccept;
		this.show = show;
		this.maximise = maximise;

		return;
	};

};

class DialogFieldConfig {
/*//
this class defines the arguments the dialog fields are able to take upon their
constructors in object form.
//*/

	constructor({ type, name, title, value, list }) {

		this.type = type;
		this.name = name;
		this.title = title ? title : name;
		this.value = value ? value : null;
		this.list  = list ? list : [];

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DialogWindow
extends ModalDialog {
/*//
this is the primary class that will be interacted with usually to spawn new
in-page dialog windows.
//*/

	constructor(config) {

		super();
		this.config = new DialogWindowConfig(config);

		////////

		this.setTitle(this.config.title);
		this.buildFields(this.config.fields);
		this.addButton(this.config.labelCancel, 'btn-dark', 'cancel');
		this.addButton(this.config.labelAccept, 'btn-primary', 'accept');

		this.onAcceptFunc = this.config.onAccept;

		if(this.config.maximise === true)
		this.element.find('.modal-dialog').css({
			'min-width': '95vw',
			'max-width': '95vw',
			'width': '95vw'
		});

		if(this.config.show === true)
		this.show();

		return;
	};

	////////////////
	////////////////

	buildFields(fields) {

		let output = jQuery('<div />');
		output.addClass('hr-hide-last');

		if(this.config.body)
		output.append(
			jQuery('<div />')
			.addClass('mb-3')
			.append(this.config.body)
		);

		for(const item of this.config.fields) {
			output.append(item.build());
			output.append('<hr class="border-0 mt-0 mb-2" />');
		}

		this.setBody(output);

		return;
	};

	getFields() {

		let output = [];

		(this.body)
		.find('[data-fieldtype]')
		.each(function(){

			let that = jQuery(this);

			if(that.attr('data-fieldtype') === 'editor-html')
			output.push(new DialogField(
				that.attr('data-fieldtype'),
				that.attr('name'),
				that.attr('title'),
				that.data('Editor').getHTML()
			));

			else
			output.push(new DialogField(
				that.attr('data-fieldtype'),
				that.attr('name'),
				that.attr('title'),
				that.val()
			));

			return;
		});

		return output;
	};

	getFieldData() {

		let output = {};
		let input = this.getFields();

		for(const item of input)
		output[item.name] = item.value ? item.value : '';

		console.log(output);

		return output;
	};

	////////////////
	////////////////

	onAccept() {

		if(typeof this.onAcceptFunc === 'function')
		return this.onAcceptFunc.call(this);

		return super.onAccept();
	};

	////////////////
	////////////////

	fillFromObject(input) {

		for(const key in input) {
			let field = this.body.find(`[name=${key}]`);

			if(!field.length)
			continue;

			// field type check to fill editors later...

			field.val(input[key]);
		}


		return;
	};

	fillFromResult(result) {

		return this.fillFromObject(result.payload);
	};

	fillByRequest(method, url, data, show=false, customFillFunc=null) {

		let self = this;
		let api = new JsonAPI.Request(method, url, data);

		(api.send())
		.then(function(result) {
			self.fillFromResult(result);

			if(typeof customFillFunc === 'function')
			customFillFunc(self, result);

			if(show)
			self.show();

			return;
		})
		.catch(api.catch);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class DialogField {
/*//
a dialog window can be given an array of these to automatically build a form
inside the dialog window.
//*/

	constructor(type, name, title, value, list) {

		if(typeof type === 'object') {
			name = type.name;
			title = type.title;
			value = type.value;
			type = type.type;
			list = type.list;
		}

		this.type = type;
		this.name = name;
		this.title = title ? title : name;
		this.value = value ? value : null;
		this.list = list ? list : null;

		return;
	};

	////////////////
	////////////////

	build() {

		if(this.type === 'text')
		return this.buildTextField();

		if(this.type === 'date')
		return this.buildDateField();

		if(this.type === 'hidden')
		return this.buildHiddenField();

		if(this.type === 'editor-html')
		return this.buildEditorHTML();

		if(this.type === 'select')
		return this.buildSelectField();

		return;
	};

	buildSelectField() {

		let output = jQuery('<div />');
		let field = null;

		output.append(
			jQuery('<div />')
			.addClass('fw-bold')
			.text(this.title)
		);

		output.append(
			field = jQuery('<select />')
			.addClass('form-select')
			.attr('name', this.name)
			.attr('title', this.title)
			.attr('data-fieldtype', this.type)
		);

		if(typeof this.list === 'object')
		for(const item in this.list)
		field.append(
			jQuery('<option />')
			.attr('value', this.list[item])
			.text(item)
		);

		if(this.value)
		field.val(this.value);

		return output;
	};

	buildTextField() {

		let output = jQuery('<div />');
		let field = null;

		output.append(
			jQuery('<div />')
			.addClass('fw-bold')
			.text(this.title)
		);

		output.append(
			field = jQuery('<input />')
			.addClass('form-control')
			.attr('type', 'text')
			.attr('name', this.name)
			.attr('title', this.title)
			.attr('data-fieldtype', this.type)
		);

		if(this.value)
		field.val(this.value);

		return output;
	};

	buildDateField() {

		let output = this.buildTextField();

		let picker = new Litepicker({
			element: output.find('input[type=text]')[0],
			showOnClick: true,
			position: 'bottom left',
			lang: 'en-US',
			firstDay: 0
		});

		output.data('Litepicker', picker);

		return output;
	};

	buildHiddenField() {

		let output = jQuery('<div />');
		let field = null;

		output
		.addClass('d-none')
		.append(
			field = jQuery('<input />')
			.addClass('form-control')
			.attr('type', 'hidden')
			.attr('name', this.name)
			.attr('data-fieldtype', this.type)
		);

		if(this.value)
		field.val(this.value);

		return output;
	};

	buildEditorHTML() {

		let output = jQuery('<div />');
		let field = null;
		let editor = null;

		output.append(
			jQuery('<div />')
			.addClass('fw-bold')
			.text(this.title)
		);

		output.append(
			field = jQuery('<div />')
			.addClass('Editor')
			.attr('name', this.name)
			.attr('title', this.title)
			.attr('data-fieldtype', this.type)
		);

		editor = new Editor(field);
		field.data('Editor', editor);
		console.log(editor);

		if(this.value)
		editor.setContent(this.value);

		return output;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let DialogUtil = {
	Window: DialogWindow,
	WindowConfig: DialogWindowConfig,
	Field: DialogField,
	FieldConfig: DialogFieldConfig
};

export default DialogUtil;
