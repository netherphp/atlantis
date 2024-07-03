import API         from '/share/nui/api/json.js';
import Util        from '/share/nui/util.js';
import DialogUtil  from '/share/nui/util/dialog.js';

import EditorJS    from '/share/atlantis/lib/editorjs/editorjs.js';
import EJSHeader   from '/share/atlantis/lib/editorjs/tools/header.js';
import EJSList     from '/share/atlantis/lib/editorjs/tools/list.js';
import EJSQuote    from '/share/atlantis/lib/editorjs/tools/quote.js';

import AtlBreak    from '/share/atlantis/lib/editorjs/tools/atl-break.js';
import AtlImage    from '/share/atlantis/lib/editorjs/tools/atl-image.js';
import AtlTeletype from '/share/atlantis/lib/editorjs/tools/atl-teletype.js';
import AtlCode     from '/share/atlantis/lib/editorjs/tools/atl-code.js';

class EditorBlock {

	constructor(selector) {

		this.element = jQuery(selector);
		this.mount = this.element.find('.atl-editorjs-mount');
		this.source = this.element.find('.atl-editorjs-source');

		this.eid = null;
		this.ejs = null;

		////////

		this.init();

		return;
	};

	init() {

		let data = JSON.parse(this.source.text() || '{}');

		this.eid = this.mount.attr('id');

		this.ejs = new EditorJS({
			holder: this.eid,
			inlineToolbar: [ 'link', 'bold', 'italic', 'teletype' ],
			tools: {
				header: { class: EJSHeader },
				bulletList: { class: EJSList, inlineToolbar: true },
				quote: { class: EJSQuote },

				breakHr: { class: AtlBreak },
				image: { class: AtlImage },
				teletype: { class: AtlTeletype },
				code: { class: AtlCode }
			},
			data: data
		});

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	async save() {

		let chain = (
			(this.ejs.save())
			.then((result)=> result)
			.catch((error)=> console.log(`EditorJS Error: ${error}`))
		);

		return chain;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static Boot(which) {

		return new this.prototype.constructor(which);
	};

	static Multiboot(mass='.atl-editorjs') {

		let ctype = this;

		jQuery(mass)
		.each(function() {
			ctype.Boot(this);
			return;
		});

		return;
	};

};

export default EditorBlock;


