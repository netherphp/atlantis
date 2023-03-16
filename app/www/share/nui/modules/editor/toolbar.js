
class Button {
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

class Dropdown {
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

class TagButton
extends Button {
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

// simple formats.

class ButtonBold
extends TagButton {
	constructor(editor) {
		super(editor, 'Bold', 'mdi mdi-fw mdi-format-bold', 'b');
		return;
	};
};

class ButtonItalic
extends TagButton {
	constructor(editor) {
		super(editor, 'Italic', 'mdi mdi-fw mdi-format-italic', 'i');
		return;
	};
};

class ButtonUnderline
extends TagButton {
	constructor(editor) {
		super(editor, 'Underline', 'mdi mdi-fw mdi-format-underline', 'u');
		return;
	};
};

class ButtonClearFormatting
extends Button {
	constructor(editor, level) {
		super(editor, `Clear Formatting`, `mdi mdi-fw mdi-monitor-shimmer`);
		return;
	};

	onClick() {

		this.editor.api.removeAllFormatting();

		return;
	};
};

// heading choices.

class ButtonHeading
extends TagButton {
	constructor(editor, level) {
		super(editor, `Heading ${level}`, `mdi mdi-fw mdi-format-header-${level}`, `h${level}`);
		return;
	};
};

class DropdownHeading
extends Dropdown {
	constructor(editor) {
		super(editor, 'Heading', 'mdi mdi-fw mdi-format-header-pound');

		this
		.addButton(new ButtonHeading(editor, 1))
		.addButton(new ButtonHeading(editor, 2))
		.addButton(new ButtonHeading(editor, 3))
		.addButton(new ButtonHeading(editor, 4));

		return;
	};
};

// image handling.

class ButtonImage
extends Button {

	constructor(editor) {
		super(editor, 'Image', 'mdi mdi-fw mdi-image-area', 'img');
		return;
	};

	onClick() {

		// this causes squire to clean the html stripping out
		// things i wanted the user to interact with.

		//(this.editor.api)
		//.insertHTML(TemplateEditorImagePlaceholder);

		(this.editor.api)
		.focus();

		let select = window.getSelection();
		let node = select.anchorNode;

		node.before(
			jQuery(TemplateEditorImagePlaceholder)
			.get(0)
		);

		return;
	};

}

class ButtonSelectionFloatLeft
extends Button {
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

class ButtonSelectionFloatRight
extends Button {
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

class ButtonSelectionFloatCenter
extends Button {
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

class ButtonSelectionWidthFull
extends Button {
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

class ButtonSelectionWidthMedium
extends Button {
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

class ButtonSelectionWidthSmall
extends Button {
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

export {
	Button, TagButton,
	Dropdown,
	ButtonBold, ButtonItalic, ButtonUnderline, ButtonClearFormatting,
	ButtonHeading, DropdownHeading,
	ButtonImage,
	ButtonSelectionFloatLeft, ButtonSelectionFloatCenter, ButtonSelectionFloatRight,
	ButtonSelectionWidthFull, ButtonSelectionWidthMedium, ButtonSelectionWidthSmall
};
