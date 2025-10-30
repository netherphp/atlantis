import API        from '../nui/api/json.js';
import DialogUtil from '../nui/util/dialog.js';
import TagDialog  from '../atlantis/tag-dialog.js';

let Messages = {
	PostDeleteTitle: 'Delete Post?',
	PostDeleteButton: 'Delete',
	PostDeleteConfirm: (
		'<div class="fw-bold mb-2">Really delete post #%ID%?</div>' +
		'<div class="mb-2"><q>%Title%</q></div>' +
		'<div class="fw-bold text-danger mb-0">This cannot be undone.</div>'
	),

	PostDraftTitle: 'Redraft Post?',
	PostDraftButton: 'Draft',
	PostDraftConfirm: (
		'<div class="fw-bold mb-2">Send post #%ID% back to drafts?</div>' +
		'<div class="mb-0"><q>%Title%</q></div>'
	),

	PostPublishTitle: 'Publish Post?',
	PostPublishButton: 'Publish',
	PostPublishConfirm: (
		'<div class="fw-bold mb-2">Publish post #%ID%?</div>' +
		'<div class="mb-0"><q>%Title%</q></div>'
	)
};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Blog {

	constructor( id, uuid ) {

		this.id = parseInt(id);
		this.uuid = uuid;

		return;
	};

	bindify() {

		return;
	};

	static FromElement({ el='#BlogInfo', bindify=false } = {}) {

		let that = jQuery(el);

		let blog = new Blog(
			that.attr('data-blog-id'),
			that.attr('data-blog-uuid')
		);

		if(bindify)
		blog.bindify();

		return blog;
	};

};

class Post {

	constructor( id, uuid, blog=null ) {

		this.id = parseInt(id);
		this.uuid = uuid;
		this.blog = blog;

		return;
	};

	bindify() {

		jQuery('[data-post-cmd=tags]')
		.on('click', this.onEditTags.bind(this));

		jQuery('[data-post-cmd=delete]')
		.on('click', this.onDelete.bind(this));

		jQuery('[data-post-cmd=publish]')
		.on('click', this.onPublish.bind(this));

		jQuery('[data-post-cmd=draft]')
		.on('click', this.onDraft.bind(this));

		jQuery('[data-post-cmd=photoset]')
		.on('click', this.onPhotoSet.bind(this));

		jQuery('[data-post-cmd=adminnotes]')
		.on('click', this.onEditTags.bind(this));

		//jQuery('[data-post-cmd=erlink')
		//.on('click', this.onEditRels.bind(this));

		return this;
	};

	format(fmt, input) {

		let tokens = { '%ID%': input.ID, '%Title%': input.Title };
		let output = fmt;

		for(const tok in tokens)
		output = output.replace(tok, tokens[tok]);

		return output;
	};

	////////////////
	////////////////

	onPhotoSet() {

		alert('TODO');

		return false;
	};

	onEditTags(ev) {

		let diag = new TagDialog(this.uuid, 'Blog.Post');
		diag.show();

		return false;
	};

	onDelete(ev) {

		let api = new API.Request('GET', '/api/blog/post', { ID: this.id });

		(api.send())
		.then((result)=> new DialogUtil.Window({
			show: true,
			title: Messages.PostDeleteTitle,
			body: this.format(Messages.PostDeleteConfirm, result.payload),
			labelAccept: Messages.PostDeleteButton,
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, result.payload.ID)
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('DELETE', '/api/blog/post', data);

				(api.send())
				.then(api.goto)
				.catch(api.catch);

				return;
			}
		}))
		.catch(api.catch);

		return false;
	};

	onPublish(ev, state=1) {

		let api = new API.Request('GET', '/api/blog/post', { ID: this.id });
		let title = state ? Messages.PostPublishTitle : Messages.PostDraftTitle;
		let button = state ? Messages.PostPublishButton : Messages.PostDraftButton;
		let confirm = state ? Messages.PostPublishConfirm : Messages.PostDraftConfirm;

		(api.send())
		.then((result)=> new DialogUtil.Window({
			show: true,
			title: title,
			body: this.format(confirm, result.payload),
			labelAccept: button,
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, result.payload.ID),
				new DialogUtil.Field('hidden', 'Enabled', null, state)
			],
			onAccept: function() {

				let api = new API.Request('PATCH', '/api/blog/post');

				(api.send(this.getFieldData()))
				.then(api.goto)
				.catch(api.catch);

				return;
			}
		}))
		.catch(api.catch);

		return false;
	};

	onDraft(ev) {

		this.onPublish(null, 0);

		return false;
	};

	////////////////
	////////////////

	static FromElement({ el='#PostInfo', bindify=false } = {}) {

		let that = jQuery(el);
		let blog = Blog.FromElement({ el: el, bindify: bindify });

		let post = new Post(
			that.attr('data-post-id'),
			that.attr('data-post-uuid'),
			blog
		);

		if(bindify)
		post.bindify();

		return post;
	};

	static FromElementUnique({ el='#PostInfo', bindify=false } = {}) {

		let that = jQuery(el);
		let blog = Blog.FromElement({ el: el, bindify: bindify });

		let post = new Post(
			that.attr('data-id'),
			that.attr('data-uuid'),
			blog
		);

		if(bindify)
		post.bindify();

		return post;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let Namespace = {
	Blog: Blog,
	Post: Post
};

export default Namespace;
