import ModalDialog from '../modal/modal.js';
import API from '../../api/json.js';

// @todo 2023-10-26 restructure this entire to take better advantage of better
// async management. the single promise that was just bolted on to the queueRun
// method is a bit janky of a way to throttle it to only process one at a time.
// would rather see the queue get populated with something such UploadQueueItem
// instances then run over that and await directly upon the transmission of
// that item. this structure would also make it easier to make it optional to
// wait until the entire queue is done before doing the POSTFINALs or not.

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let UploaderTemplate = `
	<div class="row tight">
		<div class="col atl-uploader-mode-upload">
			<button class="btn btn-primary btn-block font-weight-bold text-uppercase">Click To Choose Files...</button>
			<input type="file" class="d-none" multiple="multiple" />
		</div>
		<div class="col-12 atl-uploader-mode-url d-none">
			<div class="fw-bold">URLs (per line)</div>
			<textarea class="form-control atl-uploader-url-input" style="white-space: pre;" rows="4"></textarea>
		</div>
		<div class="col-12 mt-4 text-center d-none">
			<div class="progress"><div class="progress-bar" style="width: 0%"></div></div>
			<div class="status mt-2 text-uppercase font-size-smaller fw-bold"></div>
		</div>
	</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let UploadDef = {
	uploadUrlVerb: 'POSTURL',
	uploadUrlURL:  '/api/media/entity'
};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UploadChunker {

	constructor(fieldName, file, clen) {

		this.file = file;
		this.chunkSize = clen;
		this.fieldName = fieldName;
		this.count = Math.ceil(this.file.size / this.chunkSize);
		this.uuid = null;

		console.log(`file: ${this.file.name} (${this.file.size})`);
		console.log(`chunks: ${this.count} (${this.chunkSize} ea)`);

		return;
	};

	getChunkRange(num) {
	/*//
	@date 2022-04-14
	first chunk is chunk 0.
	//*/

		let start = (num * this.chunkSize);
		let end = ((num + 1) * this.chunkSize) - 1;

		if(end > this.file.size)
		end = this.file.size;

		return [ start, end ];
	};

	getChunkHeader(num) {
	/*//
	@date 2022-04-14
	first chunk is chunk 0.
	//*/

		let range = this.getChunkRange(num);

		return `bytes ${range[0]}-${range[1]}/${this.file.size}`;
	};

	getChunk(num) {
	/*//
	@date 2022-04-14
	@why https://developer.mozilla.org/en-US/docs/Web/API/Blob/slice
	first chunk is chunk 0.
	//*/

		let range = this.getChunkRange(num);
		let chunk = this.file.slice(range[0], (range[1] + 1));
		let data = new FormData;

		data.append(this.fieldName, chunk, this.file.name);

		return data;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UploadButtonDialog
extends ModalDialog {

	constructor(main) {
		super(UploaderTemplate);

		this.setTitle('Upload Files...');

		this.mode = 'upload';
		this.action = 'default';
		this.queue = new Array;
		this.req = null;

		this.button = this.body.find('.btn:first');
		this.input = this.body.find('input[type=file]:first');
		this.bar = this.body.find('.progress:first');
		this.fill = this.body.find('.progress-bar:first');
		this.status = this.body.find('.status:first');
		this.btnModeUrl = this.body.find('[data-mode=url]');
		this.inputUrls = this.body.find('.atl-uploader-url-input');

		this.runCount = 0;
		this.runTotal = 0;
		this.throttle = null;

		this.url = main.options.url;
		this.method = main.options.method;
		this.dataset = main.options.dataset;
		this.onSuccess = main.options.onSuccess;
		this.chunkSize = main.options.chunkSize;
		this.fieldName = main.options.fieldName;

		this.methodFinal = main.options.methodFinal;
		this.urlFinal = main.options.urlFinal;

		this.button.on('click', this.onSelectFile.bind(this));
		this.input.on('change', this.onSelected.bind(this));
		this.btnModeUrl.on('click', this.onModeUrl.bind(this));

		(this)
		.addButton(
			'By URL...',
			'btn-secondary atl-uploader-mode-upload',
			'mode-url'
		)
		.addButton(
			'By File...',
			'btn-secondary atl-uploader-mode-url d-none',
			'mode-upload'
		)
		.addButton(
			'By Paste...',
			'btn-secondary atl-uploader-mode-paste',
			'mode-paste'
		)
		.addButton(
			'Upload',
			'btn-primary atl-uploader-mode-url d-none',
			'accept'
		);

		(this.element)
		.on(
			'click', '.modal-action-mode-url',
			this.onModeUrl.bind(this)
		)
		.on(
			'click', '.modal-action-mode-upload',
			this.onModeUpload.bind(this)
		)
		.on(
			'click', '.modal-action-mode-paste',
			this.onModePaste.bind(this)
		);

		this.show();
		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onModeUrl() {

		this.mode = 'url';

		(this.element)
		.find('.atl-uploader-mode-upload')
		.addClass('d-none');

		(this.element)
		.find('.atl-uploader-mode-url')
		.removeClass('d-none');

		return;
	};

	onModeUpload() {

		this.mode = 'upload';

		(this.element)
		.find('.atl-uploader-mode-upload')
		.removeClass('d-none');

		(this.element)
		.find('.atl-uploader-mode-url')
		.addClass('d-none');

		return;
	};

	onModePaste() {

		let self = this;

		////////

		const goModePaste = async function() {

			// get paste permissions and the clipboard.

			let cb = await navigator.clipboard.read();

			// for each item on the clipboard determine if it is an image
			// and if it is, add it to the queue.

			for(const item of cb) {
				let types = item.types.filter((t)=> t.match(/^image\//));
				let type = null;
				let blob = null;

				if(types.length === 0)
				continue;

				////////

				type = types[0];

				blob = await item.getType(type);
				blob.name = 'paste.idk';

				// give it a related name.

				if(type === 'image/png')
				blob.name = 'paste.png';

				if(type === 'image/jpeg')
				blob.name = 'paste.jpg';

				if(type === 'image/webp')
				blob.name = 'paste.webp';

				// if it wasn't a supported time bail.

				if(blob.name === 'paste.idk')
				continue;

				self.onModeUpload();
				self.queueAddFile(blob);
			}

			if(self.queue.length > 0)
			self.queueRun();

			return;
		};

		////////

		goModePaste();
		return;
	}

	onSelectFile() {

		this.input.trigger('click');
		return false;
	};

	onSelected() {

		let files = this.input[0].files;

		////////

		if(files.length === 0) {
			console.log('no file selected');
			return;
		}

		////////

		this.queueClear();

		for(const file of files)
		this.queueAddFile(file);

		this.queueRun();

		return;
	};

	onUploadProgress(ev, item, iter) {

		if(ev.lengthComputable)
		this.onUploadProgressReal(ev, item, iter);

		else
		this.onUploadProgressMeh(ev, item, iter);

		return;
	};

	onUploadProgressReal(ev, item, iter) {

		let current = (ev.loaded / ev.total);
		let total = ((iter / item.count) * current);

		this.fill.css({
			'width': `${Math.round(total * 100)}%`
		});

		return;
	};

	onUploadProgressMeh(ev, item, iter) {

		let total = (iter / item.count);

		this.fill.css({
			'width': `${Math.round(total * 100)}%`
		});

		return;
	};

	onUploadChunkDone(ev, item, iter) {

		let range = item.getChunkRange(iter);
		let self = this;

		if(this.req.response.Error !== 0) {
			this.fill
			.addClass('bg-danger')
			.css({ 'width': '100%' });

			alert(this.req.response.Message);
			return;
		}

		// after the first chunk this upload will get a uuid.
		item.uuid = this.req.response.Payload.UUID;

		console.log(`chunk ${iter + 1} of ${item.count} done`);
		console.log(`${range[1]} ${item.file.size}`);

		// i think this is a side effect of that stupid file.slice off by
		// one crap the mdn mentions, such that if i send a file exactly
		// divisible by the chunk that last byte does not get sent. so we
		// actually need to send one more chunk than we thought for that
		// final byte. and to determine that we will just watch the end
		// range and chug until it maths out instead.

		//if(iter < (item.count - 1)) {
		if(range[1] < item.file.size) {
			this.upload(item, (iter + 1));
			return;
		}

		(this.fill)
		.addClass('bg-success progress-bar-striped progress-bar-animated')
		.css({ 'width': '100%' });

		this.status.text(`Processing ${item.file.name} (${this.runCount} of ${this.queue.length})`);

		//console.log(`chunk ${iter + 1} of ${item.count} done`);
		console.log(`file ${item.file.name} done`);

		////////

		let api = new API.Request(this.methodFinal, this.urlFinal);
		let finish = {
			'UUID': this.req.response.Payload.UUID,
			'Path': this.req.response.Payload.Path,
			'Type': this.req.response.Payload.Type,
			'Name': this.req.response.Payload.Name
		};

		for(const dkey in this.dataset)
		finish[dkey] = this.dataset[dkey];

		////////

		(api.send(finish))
		.then(function(result) {
			if(self.runCount < self.queue.length) {
				self.throttle();
				return;
			}

			if(typeof self.onSuccess === 'function') {
				(self.onSuccess)(result, self);
			}

			if(typeof self.onSuccess === 'string') {
				if(self.onSuccess === 'reload' || self.onSuccess === 'refresh')
				location.reload();
				else
				location.href = self.onSuccess;
			}

			return;
		})
		.catch(api.catch);

		return;
	};

	onUploadUrls() {

		this.footer.hide();

		let api = new API.Request(UploadDef.uploadUrlVerb, UploadDef.uploadUrlURL);
		let data = { ...this.dataset };

		data.URL = jQuery.trim(this.inputUrls.val());

		(api.send(data))
		.then(api.reload)
		.catch(api.catch);

		return;
	};

	onAccept() {

		if(this.mode === 'url')
		this.onUploadUrls();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	queueClear() {

		this.queue.length = 0;

		return;
	};

	queueAddFile(file) {

		this.queue.push(new UploadChunker(
			this.fieldName,
			file,
			this.chunkSize
		));

		return;
	};

	async queueRun() {

		let that = this;

		this.runCount = 0;

		this.queue.sort(function(a, b){ return a.file.name > b.file.name; });

		this.fill.removeClass('bg-danger bg-success');
		this.bar.parent().removeClass('d-none');

		for(let item of this.queue) {
			this.runCount += 1;
			this.status.text(`Uploading ${item.file.name} (${this.runCount} of ${this.queue.length})`);

			//this.upload(item);

			await new Promise(function(ok, fail) {
				that.throttle = ok;
				that.upload(item, null);
				return;
			});
		}

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	upload(item, chnum) {

		if(this.queue.length === 0)
		return alert('no file selected');

		if(typeof chnum === 'undefined')
		chnum = 0;

		console.log(`file: ${item.file.name} chunk: ${chnum}`);

		////////

		let chunk = item.getChunk(chnum);
		let chunkHeader = item.getChunkHeader(chnum);

		// add additional form data.

		if(item.uuid)
		chunk.append('UUID', item.uuid);

		for(const datakey in this.dataset)
		chunk.append(datakey, this.dataset[datakey]);

		// send the chunk.

		this.req = new XMLHttpRequest;
		this.req.responseType = 'json';
		this.req.addEventListener('progress', (ev)=> this.onUploadProgress(ev, item, chnum));
		this.req.addEventListener('load', (ev)=> this.onUploadChunkDone(ev, item, chnum));

		this.req.open(this.method, this.url, true);
		this.req.setRequestHeader('content-range', chunkHeader);
		this.req.send(chunk);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UploadButtonOptions {

	constructor(input={}) {

		this.conf = (
			typeof input.conf !== 'undefined'
			? input.conf
			: null
		);

		this.url = (
			typeof input.url !== 'undefined'
			? input.url
			: '/api/file/upload'
		);

		this.method = (
			typeof input.method !== 'undefined'
			? input.method
			: 'UPCHUNK'
		);

		this.urlFinal = (
			typeof input.urlFinal !== 'undefined'
			? input.urlFinal
			: '/api/file/upload'
		);

		this.methodFinal = (
			typeof input.methodFinal !== 'undefined'
			? input.methodFinal
			: 'UPFINAL'
		);

		this.dataset = (
			typeof input.dataset !== 'undefined'
			? input.dataset
			: []
		);

		this.programmed = (
			typeof input.programmed !== 'undefined'
			? input.programmed === 'true'
			: false
		);

		this.onSuccess = (
			typeof input.onSuccess !== 'undefined'
			? input.onSuccess
			: null
		);

		this.chunkSize = (
			typeof input.chunkSize !== 'undefined'
			? input.chunkSize
			: 1024 * 1024
		);

		this.fieldName = (
			typeof input.fieldName !== 'undefined'
			? input.fieldName
			: 'file'
		);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class UploadButton {

	constructor(selector, opt={}) {

		opt = new UploadButtonOptions(opt);

		this.selector = selector;
		this.options = opt;

		this.element = jQuery(selector);
		this.dialog = null;
		this.queue = new Array;
		this.req = null;

		////////

		if(this.element.is('[data-upload-method]'))
		this.options.method = this.element.attr('data-upload-method');

		if(this.element.is('[data-upload-url]'))
		this.options.url = this.element.attr('data-upload-url');

		if(this.element.is('[data-upload-method-final]'))
		this.options.methodFinal = this.element.attr('data-upload-method-final');

		if(this.element.is('[data-upload-url-final]'))
		this.options.urlFinal = this.element.attr('data-upload-url-final');

		if(this.element.is('[data-on-success]'))
		this.options.onSuccess = this.element.attr('data-on-success');

		if(this.element.is('[data-dataset]'))
		this.options.dataset = JSON.parse(
			this.element.attr('data-dataset')
		);

		if(this.element.is('[data-programmed]'))
		this.options.programmed = (
			this.element.attr('data-programmed') === 'true'
			? true : false
		);

		////////

		this.element.on(
			'click',
			this.onButtonClick.bind(this)
		);

		return;
	};

	async onButtonClick() {
	/*//
	@date 2022-04-14
	//*/

		let conf = this.options.conf || this.element.attr('data-conf');

		////////

		if(conf)
		await this.getRemoteConfig(conf);

		this.dialog = new UploadButtonDialog(this);


		return false;
	};

	destroyDialog() {

		if(this.dialog)
		this.dialog.destroy();

		this.dialog = null;
		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getRemoteConfig(url) {

		let api = new API.Request('UPCONFIG', url);
		let self = this;

		let chain = (
			(api.send())
			.then(function(result) {
				self.options.method = result.payload.UploadMethod;
				self.options.url = result.payload.UploadURL;
				self.options.chunkSize = result.payload.UploadChunk;
				self.options.fieldName = result.payload.UploadName;

				self.options.methodFinal = result.payload.FinalMethod;
				self.options.urlFinal = result.payload.FinalURL;

				console.log(self.options);
				return;
			})
			.catch(api.catch)
		);

		return chain;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default UploadButton;
export { UploadButton, UploadButtonDialog, UploadChunker };
