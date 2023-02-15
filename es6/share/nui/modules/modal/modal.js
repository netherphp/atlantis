
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

		return;
	};

	destroy() {

		this.api.dispose();

		this.element.remove();
		this.element.empty();
		this.element = null;

		return;
	};

	show() {

		this.api.show();

		return this;
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

export default ModalDialog;
