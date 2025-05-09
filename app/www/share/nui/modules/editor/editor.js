import '../../../atlantis/lib/squire/squire-raw.js';
import ModalDialog from '../modal/modal.js';
import UploadButton from '../uploader/uploader.js';
import * as Toolbarr from './toolbar.js';

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

	// type = null;
	/*//
	@type string
	defines the type of editor this is.
	//*/

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

		this.type = 'html';
		this.selector = selector;
		this.element = null;
		this.toolbars = {};
		this.viewport = null;
		this.debug = null;
		this.api = null;
		this.document = null;
		this.content = null;

		////////

		this.init();

		return;
	};

	init() {

		this.prepareElement();
		this.prepareApi();
		this.onContentChange();

		this.element.removeClass('d-none');

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

		this.content = this.element.html();
		this.element.empty();

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
		this.setContent(this.content);

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

	setContent(input) {

		return this.api.setHTML(input);
	};

	getContent() {

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
		this.element = jQuery('<div />').addClass('Viewport EditorContent form-control');

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

		this.onReady();
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

	onReady() {

		return;
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

class ToolbarButtonBulletList
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Bullet List', 'mdi mdi-fw mdi-format-list-bulleted', 'ul');
		return;
	};

	onClick(ev) {

		ev.preventDefault();
		this.editor.viewport.element.focus();
		this.editor.api.makeUnorderedList();

		return;
	};
};

class ToolbarButtonNumberedList
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Numbered List', 'mdi mdi-fw mdi-format-list-numbered', 'ul');
		return;
	};

	onClick(ev) {

		ev.preventDefault();
		this.editor.viewport.element.focus();
		this.editor.api.makeOrderedList();

		return;
	};
};

class ToolbarButtonIndent
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Increase Indent', 'mdi mdi-fw mdi-format-indent-increase', 'blockquote');
		return;
	};

	onClick(ev) {

		ev.preventDefault();
		this.editor.viewport.element.focus();
		this.editor.api.increaseQuoteLevel();

		return;
	};
};

class ToolbarButtonDedent
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Decrease Indent', 'mdi mdi-fw mdi-format-indent-decrease', 'blockquote');
		return;
	};

	onClick(ev) {

		ev.preventDefault();
		this.editor.viewport.element.focus();
		this.editor.api.decreaseQuoteLevel();

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

class ToolbarButtonHR
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'HR Break', 'mdi mdi-fw mdi-line-scan', 'hr', { 'class': 'break break-hr' });
		return;
	};
};

class ToolbarButtonBreak
extends ToolbarButtonTag {
	constructor(editor) {
		super(editor, 'Clear Break', 'mdi mdi-fw mdi-scan-helper', 'hr', { 'class': 'break break-clear' });
		return;
	};
};

class ToolbarButtonImage
extends ToolbarButton {

	constructor(editor) {
		super(editor, 'Image', 'mdi mdi-fw mdi-image-area', 'img');
		return;
	};

	onReady() {

		this.uploader = new UploadButton(this.element, {
			url: '/api/media/entity',
			dataset: { type: 'default' },
			onSuccess: this.onSuccess.bind(this)
		});

		return;
	};

	onSuccess(result) {

		(this.editor.api)
		.focus();

		let select = window.getSelection();
		let node = select.anchorNode;
		let img = jQuery(TemplateEditorImagePlaceholder);

		img.attr('src', result.payload.URL);
		node.before(img.get(0));

		this.

		return;
	};

	onClick() {

		// this causes squire to clean the html stripping out
		// things i wanted the user to interact with.

		//(this.editor.api)
		//.insertHTML(TemplateEditorImagePlaceholder);

		console.log('click');

		return false;
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

		return false;
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

		(this.editor.selected)
		.addClass('float-start')
		.removeClass('float-end');

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

		(this.editor.selected)
		.addClass('float-end')
		.removeClass('float-start');

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

		(this.editor.selected)
		.removeClass('float-right float-left');

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

		let diag = (new EditorHyperlinkDialog(this.editor))
		.setTitle('Insert Hyperlink...')
		.addButton('Cancel', 'btn-dark', 'cancel')
		.addButton('Save', 'btn-primary', 'accept')
		.fillFromCurrent();

		diag.show();

		return false;
	};

};

class EditorColourDialog
extends ModalDialog {

	constructor(editor) {
		super(TemplateEditorColourModal);

		this.editor = editor;
		this.inputColour = this.body.find('input[name=Colour]');

		return;
	};

	onAccept() {

		let c = jQuery.trim(this.inputColour.val());
		let attr = {};

		console.log(`[EditorColourDialog.onAccept]: picked ${c}`);

		if(!c) {
			console.log('Insert Colour: no colour');
			this.destroy();
			return false;
		}

		if(!this.editor.isAnyTextSelected()) {
			let current = this.getCurrentElement();

			if(!current) {
				console.log('Insert Colour: no selection and not within existing colour.');
				this.destroy();
				return false;
			}

			this.editor.selectNode(current);
		}

		////////

		attr.style = `color:${c}`;
		console.log(attr);

		////////

		// prefer the changeFormat method over the makeLink method
		// provided as this allows to clean up preventing the user
		// from making overlapping links which i've already done
		// myself like 40 times.

		this.editor.api.setTextColour(c);

		/*
		this.editor.api.changeFormat(
			new SquireTag('span', attr),
			new SquireTag('span'),
			null,
			true
		);
		*/

		this.destroy();
		return false;
	};

	getCurrentElement() {

		let range = this.editor.api.getSelection();

		let current = Squire.getNearest(
			range.commonAncestorContainer,
			this.editor._root,
			'SPAN'
		);

		return current;
	};

	fillFromCurrent() {

		let current = this.getCurrentElement();

		if(current) {
			let c = current.style.color;

			if(c.match(/^rgb\(/))
			c = this.rgbStringToHexString(c);

			this.inputColour.val(c);
		}

		return this;
	};

	rgbStringToHexString(rgb) {
		// Choose correct separator
		let sep = rgb.indexOf(",") > -1 ? "," : " ";

		// Turn "rgb(r,g,b)" into [r,g,b]
		rgb = rgb.substr(4).split(")")[0].split(sep);

		let r = (+rgb[0]).toString(16),
			g = (+rgb[1]).toString(16),
			b = (+rgb[2]).toString(16);

		if (r.length == 1)
		r = "0" + r;
		if (g.length == 1)
		g = "0" + g;
		if (b.length == 1)
		b = "0" + b;

		return "#" + r + g + b;
	};

};

class ToolbarButtonColour
extends ToolbarButton {

	constructor(editor) {
		super(editor, 'Font Color', 'mdi mdi-fw mdi-palette');
		return;
	};

	onClick() {

		let diag = (new EditorColourDialog(this.editor))
		.setTitle('Font Color...')
		.addButton('Cancel', 'btn-dark', 'cancel')
		.addButton('Save', 'btn-primary', 'accept')
		.fillFromCurrent();

		diag.show();

		return false;
	};

};

class ToolbarButtonFontSize
extends ToolbarButton {

	constructor(editor, level, icon) {

		super(editor, `Font Size: ${level}`, icon);
		this.size = level;

		return;
	};

	onClick() {

		this.editor.api.setFontSize(this.size);

		return;
	};

};

class ToolbarDropdownFontSize
extends ToolbarDropdown {
	constructor(editor) {
		super(editor, 'Font Size', 'mdi mdi-fw mdi-format-size');

		this
		.addButton(new ToolbarButtonFontSize(editor, 12, 'mdi mdi-size-xs'))
		.addButton(new ToolbarButtonFontSize(editor, 14, 'mdi mdi-size-s'))
		.addButton(new ToolbarButtonFontSize(editor, 16, 'mdi mdi-size-m'))
		.addButton(new ToolbarButtonFontSize(editor, 22, 'mdi mdi-size-l'))
		.addButton(new ToolbarButtonFontSize(editor, 32, 'mdi mdi-size-xl'));

		return;
	};
};

class ToolbarButtonDeselectDone
extends ToolbarButton {

	constructor(editor, title='Done') {
		super(editor, title, 'mdi mdi-fw mdi-check');
		return;
	};

	onClick() {

		this.editor.onClickNothing();
		return false;
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
			new ToolbarDropdownFontSize(this.editor),
			new ToolbarButtonColour(this.editor),
			new ToolbarButtonBulletList(this.editor),
			new ToolbarButtonNumberedList(this.editor),
			new ToolbarButtonIndent(this.editor),
			new ToolbarButtonDedent(this.editor),
			new ToolbarButtonImage(this.editor),
			new ToolbarButtonHyperlink(this.editor),
			new ToolbarButtonHR(this.editor),
			new ToolbarButtonBreak(this.editor),
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
			new ToolbarButtonDeselectDone(this.editor),
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

let TemplateEditorImagePlaceholder = `
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

let TemplateEditorColourModal = `
<div class="row">
	<div class="col-12 mb-4">
		<div><strong>Color:</strong></div>
		<input type="color" name="Colour" class="form-control" />
	</div>
	<div class="col-12 mb-4">
		[prefab pallete]
	</div>
</div>
`;

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////

export default Editor;
