import API      from '/share/nui/api/json.js';
import FormUtil from '/share/nui/util/form.js';
import Editor   from '/share/nui/modules/editor/editor.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Timeline {

	constructor(id=null) {

		this.id = null;

		console.log(`new timeline ${this.id}`);

		return;
	};

	importPayload(payload) {

		this.id = payload.ID;
		this.uuid = payload.UUID;
		this.title = payload.Title;
		this.date = payload.Date;
		this.details = payload.Details;

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static BindDocument() {

		// rig the Toggle Timeine New button.

		jQuery('[data-atl-timeline-cmd="new"')
		.each(function() {

			let that = jQuery(this);
			let selector = that.attr('data-atl-timeline-target');
			let el = jQuery(selector);

			that.on('click', function() {
				Timeline.WhenClickNew(el);
				return false;
			})

			return;
		});

		// rig the Toggle Item New button.

		jQuery('[data-atl-timeline-cmd="item-new"')
		.each(function() {

			let that = jQuery(this);
			let selector = that.attr('data-atl-timeline-target');
			let el = jQuery(selector);

			that.on('click', function() {
				Timeline.WhenClickItemNew(el);
				return false;
			})

			return;
		});

		////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////

		// rig Submit Timeline New

		jQuery('[data-atl-timeline-form="new"]')
		.each(function() {

			let that = jQuery(this);

			that.on('submit', function() {
				Timeline.WhenSubmitTimelineNew(that);
				return false;
			});

			return;
		});

		// rig Submit Item New

		jQuery('[data-atl-timeline-form="item-new"]')
		.each(function() {

			let that = jQuery(this);

			let editel = that.find('.Editor');
			let editor = new Editor(editel);

			(that)
			.data('editor', editor)
			.on('submit', function() {
				Timeline.WhenSubmitItemNew(that);
				return false;
			});

			return;
		});

		// rig Submit Item Edit

		jQuery('[data-atl-timeline-form="item-edit"]')
		.each(function() {

			let that = jQuery(this);

			let editel = that.find('.Editor');
			let editor = new Editor(editel);

			(that)
			.data('editor', editor)
			.on('submit', function() {
				Timeline.WhenSubmitItemEdit(that);
				return false;
			});

			return;
		});

		// rig Submit Item Delete

		jQuery('[data-atl-timeline-form="item-delete"]')
		.each(function() {

			let that = jQuery(this);

			(that)
			.on('submit', function() {
				Timeline.WhenSubmitItemDelete(that);
				return false;
			});

			return;
		});

		return;
	};

	static WhenClickNew(el) {

		el.toggleClass('d-none');

		return;
	};

	static WhenClickItemNew(el) {

		el.toggleClass('d-none');

		el[0].scrollIntoView();

		return;
	};

	static WhenSubmitTimelineNew(el) {

		let api = new API.Request('POST', '/api/media/timeline');
		let title = el.find('[name=Title]').val();

		let data = {
			Title: title
		};

		(api.send(data))
		.then(api.goto)
		.catch(api.catch);

		return;
	};

	static WhenSubmitItemNew(el) {

		let api = new API.Request('POST', '/api/media/timeline/item');
		let editor = el.data('editor');

		let id = el.attr('data-atl-timeline-id');
		let title = el.find('[name="Title"]').val();
		let date = el.find('[name="Date"]').val();
		let url = el.find('[name="URL"]').val();
		let details = editor.getHTML();

		let data = {
			"TimelineID": id,
			"Title": title,
			"Date": date,
			"URL": url,
			"Details": details
		};

		(api.send(data))
		.then(()=> location.reload())
		.catch(api.catch);

		return;
	};

	static WhenSubmitItemEdit(el) {

		let api = new API.Request('PATCH', '/api/media/timeline/item');
		let editor = el.data('editor');

		let id = el.attr('data-atl-timeline-item-id');
		let title = el.find('[name="Title"]').val();
		let date = el.find('[name="Date"]').val();
		let url = el.find('[name="URL"]').val();
		let details = editor.getHTML();

		let data = {
			"ID": id,
			"Title": title,
			"Date": date,
			"URL": url,
			"Details": details
		};

		(api.send(data))
		.then(api.goto)
		.catch(api.catch);

		return;
	};

	static WhenSubmitItemDelete(el) {

		let api = new API.Request('DELETE', '/api/media/timeline/item');
		let editor = el.data('editor');

		let id = el.attr('data-atl-timeline-item-id');

		let data = {
			"ID": id,
		};

		(api.send(data))
		.then(api.goto)
		.catch(api.catch);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default Timeline;
