import ModalDialog from '/share/nui/modules/modal/modal.js';

class ConfirmDialogOptions {

	constructor(input={
		title, message,
		onAccept, onCancel,
		labelAccept, btnAccept,
		labelCancel, btnCancel
	}) {

		this.title = (
			typeof input.title !== 'undefined'
			? input.title
			: 'Confirm'
		);

		this.message = (
			typeof input.message !== 'undefined'
			? input.message
			: 'For Real?'
		);

		this.onAccept = (
			typeof input.onAccept !== 'undefined'
			? input.onAccept
			: null
		);

		this.onCancel = (
			typeof input.onCancel !== 'undefined'
			? input.onCancel
			: null
		);

		this.labelAccept = (
			typeof input.labelAccept !== 'undefined'
			? input.labelAccept
			: 'OK'
		);

		this.labelCancel = (
			typeof input.labelCancel !== 'undefined'
			? input.labelCancel
			: 'Cancel'
		);

		this.btnAccept = (
			typeof input.btnAccept !== 'undefined'
			? input.btnAccept
			: 'btn-primary'
		);

		this.btnCancel = (
			typeof input.btnCancel !== 'undefined'
			? input.btnCancel
			: 'btn-dark'
		);

		return;
	};

};

class ConfirmDialog
extends ModalDialog {

	constructor(opt={ }) {
		super();

		this.options = new ConfirmDialogOptions(opt);

		this.setTitle(this.options.title);
		this.setBody(this.options.message);
		this.addButton(this.options.labelCancel, this.options.btnCancel, 'cancel');
		this.addButton(this.options.labelAccept, this.options.btnAccept, 'accept');

		this.confirmAccept = this.options.onAccept;
		this.confirmReject = this.options.onCancel;

		return;
	};

	onAccept() {

		if(typeof this.confirmAccept === 'function')
		return this.confirmAccept.call(this);

		super.onAccept();
		return;
	};

	onCancel() {
		super.onCancel();
		return;
	};

};

export default ConfirmDialog;
export { ConfirmDialog, ConfirmDialogOptions };
