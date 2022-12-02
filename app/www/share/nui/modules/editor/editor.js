import '/share/squire/squire-raw.js';

// requires:
// - jQuery
// - Squire
// - bootstrap (btn, modal)

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class Editor {
/*//
@date 2022-11-28
core editor class. this is the main interface used to interact with the editor
from your application.
//*/

	// commented out because safari from 2019 is still out in the wild
	// and it was the last holdout to even start adding field support.

	// selector = null;
	/*//
	@type string
	the query that selected where to construct the editor.
	//*/

	// element = null;
	/*//
	@type jQuery
	reference to the root element the editor is constructed within.
	//*/

	// toolbar = null;
	/*//
	@type Toolbar
	contains and manages toolbar items.
	//*/

	// viewport = null;
	/*//
	@type Viewport
	contains and manages the input portion of the editor.
	//*/

	// debug = null;
	/*//
	@type Viewport
	contains and manages the debugging output portion of the editor.
	//*/

	// api = null;
	/*//
	@type Viewport
	reference to the squire api.
	//*/

	// document = null;
	/*//
	@type Viewport
	reference to the editable document managed by squire.
	//*/

	constructor(selector) {

		this.selector = selector;
		this.element = null;
		this.toolbars = {};
		this.viewport = null;
		this.debug = null;
		this.api = null;
		this.document = null;

		////////

		this.init();

		return;
	};

	init() {

		this.prepareElement();
		this.prepareApi();
		this.onContentChange();

		/*
		(new ModalDialog)
		.setTitle('Test')
		.setBody('This was a test.')
		.addButton('OK');
		*/

		return;
	};

	prepareElement() {

		this.element = jQuery(this.selector);
		this.viewport = new Viewport(this);
		this.debug = new DebugViewport(this);
		this.selected = null;

		this.toolbars.main = new ToolbarMain(this);
		this.toolbars.image = new ToolbarImage(this);
		this.hideExtraToolbars();

		this.element.append(this.toolbars.main.element);
		this.element.append(this.toolbars.image.element);
		this.element.append(this.viewport.element);
		this.element.append(this.debug.element);

		return;
	};

	prepareApi() {

		this.api = new Squire(this.viewport.element.get(0));
		this.document = this.api.getDocument();

		this.api.addEventListener(
			'pathChange',
			this.onPathChange.bind(this)
		);

		this.api.addEventListener(
			'input',
			this.onContentChange.bind(this)
		);

		(this.element)
		.on('click', '.EditorItem', this.onClickEditorItem.bind(this))
		.on('click', '.Viewport', this.onClickNothing.bind(this));

		return;
	};

	onClickNothing(event) {

		if(this.selected)
		this.selected.removeClass('Selected');

		this.element
		.find('.EditorItem.Selected')
		.removeClass('Selected');

		this.selected = null;

		this.onUpdateSelection();

		return false;
	};

	onClickEditorItem(event) {

		this.selected = jQuery(event.originalEvent.target);

		this.element
		.find('.EditorItem.Selected')
		.removeClass('Selected');

		this.selected
		.addClass('Selected');

		this.onUpdateSelection();

		return false;
	};

	onPathChange() {

		this.onUpdate();
		return;
	};

	onContentChange() {

		//this.debug.element.empty();

		this.debug.element.text(
			this.api.getHTML()
		);

		return;
	};

	onUpdate() {

		for(const key of Object.keys(this.toolbars))
		this.toolbars[key].onUpdate();

		return;
	};

	onUpdateSelection() {

		this.hideExtraToolbars();
		this.showExtraToolbars();

		return false;
	};

	hideExtraToolbars() {

		for(const key of Object.keys(this.toolbars)) {
			if(key === 'main')
			this.toolbars[key].show();

			else
			this.toolbars[key].hide();
		}

		return this;
	};

	showExtraToolbars() {

		if(!this.selected)
		return false;

		for(const key of Object.keys(this.toolbars))
		this.toolbars[key].hide();

		if(this.selected.hasClass('EditorItemImage'))
		this.toolbars.image.show();

		return this;
	};

	isAnyTextSelected() {

		let selection = this.api.getSelection();

		if(!selection)
		return false;

		if(selection.collapsed)
		return false;

		return true;
	};

	selectNode(node) {

		let selection = this.api.getSelection();

		if(!selection)
		return false;

		selection.selectNode(node);

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getHTML() {

		return this.api.getHTML();
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class Toolbar {
/*//
@date 2022-11-28
describes and contains the toolbar items.
//*/

	constructor(editor) {

		this.editor = editor;
		this.element = jQuery('<div class="row tight Toolbar" />');
		this.buttons = null;

		this.prepareButtons();
		return;
	};

	onUpdate() {

		for(const btn of this.buttons)
		btn.onUpdate();

		return false;
	};

	hide() {

		this.element.hide();
		return this;
	};

	show() {

		this.element.show();
		return this;
	};

	prepareButtons() {

		return;
	};

};

class Viewport {
/*//
@date 2022-11-28
describes and contains the editable pane.
//*/

	constructor(main) {

		this.main = main;
		this.element = jQuery('<div />').addClass('Viewport');

		return;
	};

};

class DebugViewport {
/*//
@date 2022-11-28
describes and contains the editable pane.
//*/

	constructor(main) {

		this.main = main;
		this.element = jQuery('<code />').addClass('Debug');

		return;
	};

};

class ModalDialog {
/*//
@date 2022-11-29
build and manage a modal popup.
//*/

	constructor(bodyContent=null) {

		this.element = jQuery(TemplateModalDialog);
		this.api = new bootstrap.Modal(this.element.get(0));
		this.title = this.element.find('.modal-header > strong');
		this.body = this.element.find('.modal-body');
		this.footer = this.element.find('.modal-footer');

		(this.element)
		.on(
			'click', '.modal-action-destroy',
			this.onCancel.bind(this)
		)
		.on(
			'click', '.modal-action-cancel',
			this.onCancel.bind(this)
		)
		.on(
			'click', '.modal-action-accept',
			this.onAccept.bind(this)
		);

		if(bodyContent !== null)
		this.setBody(bodyContent);

		this.api.show();

		return;
	};

	destroy() {

		this.api.dispose();

		this.element.remove();
		this.element.empty();
		this.element = null;

		return;
	};

	addButton(name, bclass='btn-primary', action='accept') {

		let button = new jQuery('<button />');

		(button)
		.addClass(`btn ${bclass}`)
		.addClass(`modal-action-${action}`)
		.html(name);

		this.footer.append(button);

		return this;
	};

	setTitle(title) {

		(this.title)
		.empty()
		.html(title);

		return this;
	};

	setBody(body) {

		(this.body)
		.empty()
		.html(body);

		return this;
	};

	onCancel() {

		this.destroy();
		return;
	};

	onAccept() {

		this.destroy();
		return;
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class ToolbarButton {
/*//
@date 2022-11-28
describes and contains a toolbar buttons.
//*/

	constructor(editor, name, icon, tagName=null, tagAttribs=null) {

		this.editor = editor;
		this.element = jQuery('<button />');
		this.tag = null;
		this.parent = null;

		if(tagName !== null)
		this.tag = new SquireTag(tagName, tagAttribs);

		////////

		this
		.setIcon(icon)
		.setName(name);

		(this.element)
		.addClass('ToolbarButton btn btn-dark')
		.on('click', this.onClick.bind(this));

		return;
	}

	setIcon(icon) {

		(this.element)
		.find('i')
		.remove();

		if(icon.match(/^(?:mdi|fa) /))
		this.element.prepend(
			jQuery('<i />')
			.addClass(icon)
		);

		return this;
	};

	setName(name) {

		(this.element)
		.find('span')
		.remove();

		(this.element)
		.attr('title', name)
		.append(
			jQuery('<span />')
			.text(name)
		);

		return this;
	};

	isActive() {

		if(!this.tag)
		return false;

		return this.editor.api.hasFormat(
			this.tag.tag,
			this.tag.attribs
		);
	};

	onUpdate() {

		if(!this.tag)
		return;

		if(this.isActive()) {
			this.element
			.addClass('btn-primary')
			.removeClass('btn-dark');
		}

		else {
			this.element
			.addClass('btn-dark')
			.removeClass('btn-primary');
		}

		return false;
	};

	onClick(ev) {

		return false;
	};

}

class ToolbarDropdown {
/*//
@date 2022-11-28
describes, contains, and maintains a collection of toolbar buttons in as
a dropdown menu.
//*/

	constructor(editor, name, icon, tagName=null, tagAttribs=null) {

		this.editor = editor;
		this.name = name;
		this.element = jQuery('<div class="ToolbarDropdown" />');
		this.button = jQuery('<button />');
		this.menu = jQuery('<div class="dropdown-menu dropdown-menu-end" />');
		this.menubox = jQuery('<div class="row gx-0 flex-nowrap" />')
		this.items = [];
		this.tag = null;

		if(tagName !== null)
		this.tag = new SquireTag(tagName, tagAttribs);

		////////

		this.menu.append(this.menubox);

		(this.button)
		.attr('type', 'button')
		.attr('data-bs-toggle', 'dropdown')
		.attr('data-bs-display', 'static')
		.addClass('dropdown-toggle')
		.addClass('ToolbarButton btn btn-dark');

		this
		.setIcon(icon)
		.setLabel(name);

		(this.element)
		.addClass('dropdown')
		.append(this.button)
		.append(this.menu);

		//(this.element)
		//.on('click', this.onClick.bind(this));

		return;
	};

	addButton(btn) {

		btn.element.addClass('btn-block ToolbarDropdownItem');
		btn.parent = this;

		this.menubox.append(
			jQuery('<div class="col-auto" />')
			.append(btn.element)
		);

		this.items.push(btn);

		return this;
	};

	setIcon(icon) {

		(this.button)
		.find('i')
		.remove();

		if(icon.match(/^(?:mdi|fa) /))
		this.button.prepend(
			jQuery('<i />')
			.addClass(icon)
		);

		return this;
	};

	setLabel(name) {

		(this.button)
		.find('span')
		.remove();

		this.button.append(
			jQuery('<span />')
			.text(name)
		);

		return this;
	};

	setName(name) {

		this.name = name;
		return this;
	};

	onUpdate() {

		let found = null;

		for(const item of this.items) {
			item.onUpdate();

			if(item.element.hasClass('btn-primary')) {
				this.setLabel(item.element.text());
				found = item;
			}
		}

		if(found !== null)
		this.setLabel(found.element.find('span').text());

		else
		this.setLabel(this.name);

		return;
	};

	onClick() {

		return;
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class ToolbarButtonTag
extends ToolbarButton {
/*//
@date 2022-11-28
button for toggling formatting controls.
//*/

	onClick(ev) {

		let add = null;
		let del = null;

		////////

		if(!this.tag)
		return;

		if(this.isActive())
		del = this.tag;

		else
		add = this.tag;

		/////////

		if(this.element.hasClass('ToolbarDropdownItem'))
		if(this.parent)

		bootstrap.Dropdown.getOrCreateInstance(this.parent.menu).hide();

		/////////

		ev.preventDefault();
		this.editor.viewport.element.focus();
		this.editor.api.changeFormat(add, del, null, false);

		return false;
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class ToolbarButtonBold
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Bold', 'mdi mdi-fw mdi-format-bold', 'b');
		return;
	};
};

class ToolbarButtonItalic
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Italic', 'mdi mdi-fw mdi-format-italic', 'i');
		return;
	};
};

class ToolbarButtonUnderline
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Underline', 'mdi mdi-fw mdi-format-underline', 'u');
		return;
	};
};

class ToolbarButtonHeading
extends ToolbarButtonTag {
	constructor(editor, level) {
		super(editor, `Heading ${level}`, `mdi mdi-fw mdi-format-header-${level}`, `h${level}`);
		return;
	};
};

class ToolbarDropdownHeading
extends ToolbarDropdown {
	constructor(editor) {
		super(editor, 'Heading', 'mdi mdi-fw mdi-format-header-pound');

		this
		.addButton(new ToolbarButtonHeading(editor, 1))
		.addButton(new ToolbarButtonHeading(editor, 2))
		.addButton(new ToolbarButtonHeading(editor, 3))
		.addButton(new ToolbarButtonHeading(editor, 4));

		return;
	};
};

class ToolbarButtonImage
extends ToolbarButton {

	constructor(editor) {
		super(editor, 'Image', 'mdi mdi-fw mdi-image-area', 'img');
		return;
	};

	onClick() {

		// this causes squire to clean the html stripping out
		// things i wanted the user to interact with.

		//(this.editor.api)
		//.insertHTML(TemplateEditorImageUploader);

		(this.editor.api)
		.focus();

		let select = window.getSelection();
		let node = select.anchorNode;

		node.before(
			jQuery(TemplateEditorImageUploader)
			.get(0)
		);

		return;
	};

}

class ToolbarButtonClear
extends ToolbarButton {
	constructor(editor, level) {
		super(editor, `Clear Formatting`, `mdi mdi-fw mdi-monitor-shimmer`);
		return;
	};

	onClick() {

		this.editor.api.removeAllFormatting();

		return;
	};
};

class ToolbarButtonSelectionFloatLeft
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Float Left', 'mdi mdi-fw mdi-format-float-left', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('float', 'left')
		.css('margin-right', '0.5rem')
		.css('margin-bottom', '0.5rem');

		return false;
	};
};

class ToolbarButtonSelectionFloatRight
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Float Right', 'mdi mdi-fw mdi-format-float-right', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('float', 'right')
		.css('margin-left', '0.5rem')
		.css('margin-bottom', '0.5rem');

		return false;
	};
};

class ToolbarButtonSelectionFloatCenter
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Float Center', 'mdi mdi-fw mdi-format-float-center', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('float', 'none');

		return false;
	};
};

class ToolbarButtonSelectionWidthFull
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Full Width', 'mdi mdi-fw mdi-size-l', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('width', 'auto');

		return false;
	};
};

class ToolbarButtonSelectionWidthMedium
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Full Width', 'mdi mdi-fw mdi-size-m', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('width', '50%');

		return false;
	};
};

class ToolbarButtonSelectionWidthSmall
extends ToolbarButton {
	constructor(editor) {
		super(editor, 'Full Width', 'mdi mdi-fw mdi-size-s', 'img');
		return;
	};

	onClick() {

		if(!this.editor.selected)
		return false;

		this.editor.selected
		.css('width', '25%');

		return false;
	};
};

class EditorHyperlinkDialog
extends ModalDialog {

	constructor(editor) {
		super(TemplateEditorHyperlinkModal);

		this.editor = editor;
		this.inputURL = this.body.find('input[name=URL]');
		this.inputOpenNew = this.body.find('input[name=OpenNew]');

		return;
	};

	onAccept() {

		let url = jQuery.trim(this.inputURL.val());
		let blank = this.inputOpenNew.is(':checked');
		let attr = {};

		if(!url) {
			console.log('Insert Hyperlink: no url.');
			this.destroy();
			return false;
		}

		if(!this.editor.isAnyTextSelected()) {
			let current = this.getCurrentElement();

			if(!current) {
				console.log('Insert Hyperlink: no selection and not within existing hyperlink.');
				this.destroy();
				return false;
			}

			this.editor.selectNode(current);
		}

		////////

		attr.href = url;

		if(blank)
		attr.target = '_blank';

		////////

		// prefer the changeFormat method over the makeLink method
		// provided as this allows to clean up preventing the user
		// from making overlapping links which i've already done
		// myself like 40 times.

		this.editor.api.changeFormat(
			new SquireTag('a', attr),
			new SquireTag('a'),
			null,
			true
		);

		this.destroy();
		return false;
	};

	getCurrentElement() {

		let range = this.editor.api.getSelection();

		let current = Squire.getNearest(
			range.commonAncestorContainer,
			this.editor._root,
			'A'
		);

		return current;
	};

	fillFromCurrent() {

		let current = this.getCurrentElement();

		if(current) {
			let url = current.getAttribute('href');
			let blank = current.target && current.target === '_blank';

			if(url)
			this.inputURL.val(url);

			if(blank)
			this.inputOpenNew.prop('checked', true);
		}

		return this;
	};

};

class ToolbarButtonHyperlink
extends ToolbarButton {

	constructor(editor) {
		super(editor, 'Hyperlink', 'mdi mdi-fw mdi-link-variant', 'a')
		return;
	};

	onClick() {

		(new EditorHyperlinkDialog(this.editor))
		.setTitle('Insert Hyperlink...')
		.addButton('Cancel', 'btn-dark', 'cancel')
		.addButton('Save', 'btn-primary', 'accept')
		.fillFromCurrent();

		return;
	};

};

class ToolbarMain
extends Toolbar {
/*//
@date 2022-12-01
//*/

	prepareButtons() {

		this.buttons = [
			new ToolbarButtonBold(this.editor),
			new ToolbarButtonItalic(this.editor),
			new ToolbarButtonUnderline(this.editor),
			new ToolbarDropdownHeading(this.editor),
			new ToolbarButtonImage(this.editor),
			new ToolbarButtonHyperlink(this.editor),
			new ToolbarButtonClear(this.editor)
		];

		for(const btn of this.buttons) {
			this.element.append(
				jQuery('<div class="col-auto">')
				.append(btn.element)
			);
		}

		return;
	};

};

class ToolbarImage
extends Toolbar {
/*//
@date 2022-12-01
//*/

	prepareButtons() {

		this.buttons = [
			new ToolbarButtonSelectionFloatLeft(this.editor),
			new ToolbarButtonSelectionFloatCenter(this.editor),
			new ToolbarButtonSelectionFloatRight(this.editor),
			new ToolbarButtonSelectionWidthFull(this.editor),
			new ToolbarButtonSelectionWidthMedium(this.editor),
			new ToolbarButtonSelectionWidthSmall(this.editor)
		];

		for(const btn of this.buttons) {
			this.element.append(
				jQuery('<div class="col-auto">')
				.append(btn.element)
			);
		}

		return;
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

class SquireTag {
/*//
@date 2022-11-28
one of the things squire expects is that you pass around tag definitions as
a simple object with two properties, and it does not seem to provide a class
for it even though that would make it a lot easier to use.
//*/

	constructor(tag, attribs=null) {

		this.tag = tag;

		if(attribs != null)
		this.attributes = attribs;

		return;
	};

};

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

let TemplateModalDialog = `
<div class="modal">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<strong class="modal-title"></strong>
				<button type="button" class="btn btn-dark modal-action-destroy"><i class="mdi mdi-fw mdi-close"></i></button>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
`;

/*
let TemplateEditorImageUploader = `
<div class="EditorItemImage position-relative" style="user-select:none;" contenteditable="false" disabled>
	<div class="position-absolutely" style="user-select:none;">
		<button class="btn btn-dark" style="user-select:none;">Upload</button>
		<button class="btn btn-dark" style="user-select:none;">Del</button>
	</div>
	<img src="/share/nui/modules/editor/image-placeholder.jpg" style="user-select:none;" />
</div>
`;
*/

let TemplateEditorImageUploader = `
<img src="/share/nui/modules/editor/image-placeholder.jpg" class="EditorItem EditorItemImage" draggable="false" />
`;

let TemplateEditorHyperlinkModal = `
<div class="row">
	<div class="col-12 mb-4">
		<div><strong>URL:</strong></div>
		<input type="text" name="URL" class="form-control" />
	</div>
	<div class="col-12 mb-0">
		<label>
			<input type="checkbox" name="OpenNew" value="1" />
			Open in new Tab/Window?
		</label>
	</div>
</div>
`;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

export default Editor;
