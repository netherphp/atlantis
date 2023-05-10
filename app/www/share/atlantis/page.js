import API from '../nui/api/json.js';
import ModalDialog from '../nui/modules/modal/modal.js';
import ConfirmDialog from '../nui/modules/modal/confirm.js';
import Editor from '../nui/modules/editor/editor.js';

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

let TemplatePageNew = `
<div class="row align-items-center">
	<div class="col-12 mb-4">
		<div><strong>Title</strong> (Required):</div>
		<input name="Title" type="text" class="form-control" />
	</div>
	<div class="col-12 mb-4">
		<div><strong>Subtitle:</strong></div>
		<input name="Subtitle" type="text" class="form-control" />
	</div>
	<div class="col-12">
		<div><strong>URL:</strong></div>
		<input name="URL" type="text" class="form-control" data-changed="false" />
	</div>
</div>
`;

let TemplateSectionEditHTML = `
<div class="row align-items-center">
	<div class="col-12 mb-4">
		<div><strong>Title:</strong></div>
		<input name="Title" type="text" class="form-control" />
	</div>
	<div class="col-12 mb-4">
		<div><strong>Subtitle:</strong></div>
		<input name="Subtitle" type="text" class="form-control" />
	</div>
	<div class="col-6 mb-4">
		<div><strong>Background:</strong></div>
		<select name="StyleBG" class="form-select"></select>
	</div>
	<div class="col-6 mb-4">
		<div><strong>Padding:</strong></div>
		<select name="StylePad" class="form-select"></select>
	</div>
	<div class="col-12">
		<div class="Content">
			<div class="Editor"></div>
		</div>
	</div>
</div>
`;

let TemplateSectionNew = `
<div class="row align-items-center">
	<div class="col-12 mb-4">
		<div><strong>Type:</strong></div>
		<select name="Type" class="form-select"></select>
	</div>
	<div class="col-12 mb-4">
		<div><strong>Title:</strong></div>
		<input name="Title" type="text" class="form-control" />
	</div>
	<div class="col-12 mb-4">
		<div><strong>Subtitle:</strong></div>
		<input name="Subtitle" type="text" class="form-control" />
	</div>
	<div class="col-12 mb-4">
		<div><strong>Background:</strong></div>
		<select name="StyleBG" class="form-select"></select>
	</div>
	<div class="col-12">
		<div><strong>Padding:</strong></div>
		<select name="StylePad" class="form-select"></select>
	</div>
</div>
`;

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class DialogPageNew
extends ModalDialog {

	constructor() {
		super();

		this.inputTitle = null;
		this.inputSubtitle = null;
		this.inputURL = null;

		this.build();
		return;
	};

	////////

	build() {

		this.setTitle('New Page');
		this.addButton('Cancel', 'btn-dark', 'cancel');
		this.addButton('Create', 'btn-primary', 'accept');

		this.setBody(
			jQuery('<div />')
			.append(TemplatePageNew)
		);

		////////

		this.inputTitle = this.body.find('[name="Title"]');
		this.inputSubtitle = this.body.find('[name="Subtitle"]');
		this.inputURL = this.body.find('[name="URL"]');

		////////

		(this.inputTitle)
		.bind('input', this.onInputTitle.bind(this));

		(this.inputURL)
		.bind('input', this.onInputURL.bind(this));

		return;
	};

	////////

	getInputData() {

		return {
			Title: jQuery.trim(this.inputTitle.val()),
			Subtitle: jQuery.trim(this.inputSubtitle.val()),
			URL: jQuery.trim(this.inputURL.val())
		};
	};

	////////

	onInputTitle() {

		if(this.inputURL.attr('data-changed') === 'true')
		return;

		////////

		let title = jQuery.trim(this.inputTitle.val());

		title = title.replace(/[^a-z0-9]/gi, '-').toLowerCase();

		this.inputURL.val(`/${title}`);
		return;
	};

	onInputURL() {

		this.inputURL.attr('data-changed', 'true');

		return;
	};

	onAccept() {

		let api = new API.Request('POST', '/api/page/entity');
		let data = this.getInputData();

		if(!data.Title) {
			alert('Title is required.');
			return;
		}

		(api.send(data))
		.then(()=> location.reload())
		.catch(api.catch);

		return;
	};

};

class DialogEditSectionHTML
extends ModalDialog {

	constructor(section) {
		super();

		let self = this;

		this.types = null;
		this.section = section;
		this.source = null;

		this.inputTitle = null;
		this.inputSubtitle = null;
		this.inputStyleBG = null;
		this.inputStylePad = null;
		this.editor = null;

		(new Promise(function(pNext, pFail) {

			let api = new API.Request('TYPES', '/api/page/section');

			(api.send())
			.then(pNext)
			.catch(api.catch);

			return;
		}))
		.then(function(result) {
			self.types = result.payload;
			return;
		})
		.then(function() {
			self.build();
			return;
		});

		return;
	};

	////////

	build() {

		this.setTitle('Edit Page Section');
		this.setWidth('95vw');

		this.setBody(
			jQuery('<div />')
			.append(TemplateSectionEditHTML)
		);

		this.addButton('Cancel', 'btn-secondary', 'cancel');
		this.addButton('Save', 'btn-primary');

		this.inputTitle = this.element.find('[name=Title]');
		this.inputSubtitle = this.element.find('[name=Subtitle]');
		this.inputStyleBG = this.element.find('[name=StyleBG]');
		this.inputStylePad = this.element.find('[name=StylePad]');
		this.source = this.element.find('.Content .Editor');
		this.editor = new Editor(this.source);

		this.inputTitle.val(this.section.Title);
		this.inputSubtitle.val(this.section.Subtitle);
		this.editor.setContent(this.section.Content);

		for(const key in this.types.StyleBG)
		this.inputStyleBG.append(
			jQuery('<option />')
			.attr('value', this.types.StyleBG[key])
			.text(key)
		);

		for(const key in this.types.StylePad)
		this.inputStylePad.append(
			jQuery('<option />')
			.attr('value', this.types.StylePad[key])
			.text(key)
		);

		this.inputStyleBG.val(this.section.StyleBG);
		this.inputStylePad.val(this.section.StylePad);

		return;
	};

	////////

	getInputData() {

		return {
			ID: this.section.ID,
			Title: jQuery.trim(this.inputTitle.val()),
			Subtitle: jQuery.trim(this.inputSubtitle.val()),
			StyleBG: jQuery.trim(this.inputStyleBG.val()),
			StylePad: jQuery.trim(this.inputStylePad.val()),
			Content: jQuery.trim(this.editor.getContent())
		};
	};

	////////

	onAccept() {

		let api = new API.Request('PATCH', '/api/page/section');
		let data = this.getInputData();

		(api.send(data))
		.then(api.reload)
		.catch(api.catch);

		return;
	};

};

class DialogPageSectionNew
extends ModalDialog {

	constructor(pageID, afterID, types) {
		super();

		this.types = types;

		this.page = pageID;
		this.after = afterID;

		this.inputTitle = null;
		this.inputSubtitle = null;
		this.inputType = null;
		this.inputStyleBG = null;
		this.inputStylePad = null;

		this.build();

		return;
	};

	////////

	build() {

		this.setTitle('New Page Section...');

		this.setBody(
			jQuery('<div />')
			.append(TemplateSectionNew)
		);

		this.addButton('Cancel', 'btn-secondary', 'cancel');
		this.addButton('Add', 'btn-primary', 'accept');

		this.inputTitle = this.element.find('[name=Title]');
		this.inputSubtitle = this.element.find('[name=Subtitle]');
		this.inputType = this.element.find('[name=Type]');
		this.inputStyleBG = this.element.find('[name=StyleBG]');
		this.inputStylePad = this.element.find('[name=StylePad]');

		////////

		for(const key in this.types.Type)
		this.inputType.append(
			jQuery('<option />')
			.attr('value', this.types.Type[key])
			.text(key)
		);

		for(const key in this.types.StyleBG)
		this.inputStyleBG.append(
			jQuery('<option />')
			.attr('value', this.types.StyleBG[key])
			.text(key)
		);

		for(const key in this.types.StylePad)
		this.inputStylePad.append(
			jQuery('<option />')
			.attr('value', this.types.StylePad[key])
			.text(key)
		);

		////////

		this.inputType.val(
			(this.inputType)
			.find(`option:contains('${this.types.DefaultType}')`)
			.val()
		);

		this.inputStyleBG.val(
			(this.inputStyleBG)
			.find(`option:contains('${this.types.DefaultBG}')`)
			.val()
		);

		this.inputStylePad.val(
			(this.inputStylePad)
			.find(`option:contains('${this.types.DefaultPad}')`)
			.val()
		);

		return;
	};

	////////

	getInputData() {

		return {
			PageID: this.page,
			AfterID: this.after,
			Type: jQuery.trim(this.inputType.val()),
			Title: jQuery.trim(this.inputTitle.val()),
			Subtitle: jQuery.trim(this.inputSubtitle.val()),
			StyleBG: jQuery.trim(this.inputStyleBG.val()),
			StylePad: jQuery.trim(this.inputStylePad.val())
		};
	};

	////////

	onAccept() {

		let api = new API.Request('POST', '/api/page/section');
		let data = this.getInputData();

		(api.send(data))
		.then(api.reload)
		.catch(api.catch);

		return;
	};

};

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

class Page {

	static DialogPageNew() {

		let diag = new DialogPageNew();

		diag.show();

		return;
	};

	static DialogPageDelete(pageID) {

		let diag = new ConfirmDialog({
			title: 'Delete Page?',
			message: 'Are you sure you want to delete this page?',
			onAccept: function() {

				let api = new API.Request('DELETE', '/api/page/entity');
				let data = { ID: pageID };

				(api.send(data))
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.show();

		return;
	};

	static DialogPageSectionEditHTML(section) {

		let diag = new DialogEditSectionHTML(section);
		diag.show();

		return;
	};

	static DialogPageSectionNew(pageID, afterID) {

		let api = new API.Request('TYPES', '/api/page/section');
		let data = { PageID: pageID, AfterID: afterID, Type: 'html' };

		(api.send(data))
		.then(function(result) {

			let diag = new DialogPageSectionNew(
				pageID, afterID,
				result.payload
			);

			diag.show();

			return;
		})
		.catch(api.catch);

		return;
	};

	static DialogPageSectionDelete(sectID) {

		let diag = new ConfirmDialog({
			title: 'Delete Section?',
			message: 'Are you sure you want to delete this page section?',
			onAccept: function() {

				let api = new API.Request('DELETE', '/api/page/section');
				let data = { ID: sectID };

				(api.send(data))
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.show();

		return;
	};

	////////

	static BindEditCommands() {

		jQuery('.CmdPageEdit')
		.on('click', function() {

			let that = jQuery(this);
			let id = that.attr('data-section-id');
			let api = new API.Request('GET', '/api/page/section');
			let data = { 'ID': id };

			(api.send(data))
			.then(function(result) {

				if(result.payload.Type === 'html')
				Page.DialogPageSectionEditHTML(result.payload);

				return;
			})
			.catch(api.catch);

			return;
		});

		jQuery('.CmdPageDelete')
		.on('click', function() {

			let that = jQuery(this);
			let pageID = that.attr('data-page-id');

			Page.DialogPageSectionDelete(pageID);
			return;
		});

		jQuery('.CmdPageSectionNew')
		.on('click', function() {

			let that = jQuery(this);
			let pageID = that.attr('data-page-id');
			let afterID = that.attr('data-section-id');

			Page.DialogPageSectionNew(pageID, afterID);
			return;
		});

		jQuery('.CmdPageSectionDelete')
		.on('click', function() {

			let that = jQuery(this);
			let sectID = that.attr('data-section-id');

			Page.DialogPageSectionDelete(sectID);
			return;
		});

		jQuery('.CmdPageSectionMoveUp, .CmdPageSectionMoveDown')
		.on('click', function() {

			let that = jQuery(this);
			let sectID = that.attr('data-section-id');
			let move = that.attr('data-move');

			let api = new API.Request('MOVE', '/api/page/section');
			let data = { ID: sectID, Move: move };

			(api.send(data))
			.then(api.reload)
			.then(api.catch);

			return;
		});

		return;
	};

};

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

export default Page;
