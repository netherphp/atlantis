import API          from '../nui/api/json.js';
import DialogUtil   from '../nui/util/dialog.js';
import TagDialog    from '../atlantis/tag-dialog.js';
import UploadButton from '../nui/modules/uploader/uploader.js';
import SimpleLightbox from '/themes/default/lib/js/simplelightbox.js';

class DocReadyFunc {
	constructor(func, data=false) {

		this.func = func;
		this.data = data;

		return;
	};
};

class Photo {

	constructor(id, uuid) {

		this.id = id;
		this.uuid = uuid;

		this.endpoint = '/api/media/entity';
		this.entType = 'Media.Image';

		console.log(`Photo { ID: ${this.id}, UUID: ${this.uuid} }`);

		return;
	};

	onDelete() {

		let self = this;

		let diag = new DialogUtil.Window({
			title: 'Confirm Photo Delete',
			labelAccept: 'Yes',
			body: (''
				+ '<div class="mb-0">Really delete this Photo?</div>'
				+ '<div class="fw-bold text-danger mb-2">This cannot be undone.</div>'
				+ `<div class="mb-2"><q></q></div>`
			),
			fields: [
				new DialogUtil.Field('hidden', 'ID', null, self.id)
			],
			onAccept: function() {

				let data = this.getFieldData();
				let api = new API.Request('DELETE', self.endpoint, data);

				(api.send())
				.then(api.reload)
				.catch(api.catch);

				return;
			}
		});

		diag.fillByRequest(
			'GET', self.endpoint,
			{ ID: self.id },
			true,
			function(d, result) {

				let dock = d.body.find('q').parent();

				d.body.find('q')
				.remove();

				dock.append(
					jQuery('<div />')
					.addClass('ratiobox widescreen contained wallpapered rounded')
					.css('background-image', `url(${result.payload.URL})`)
				);

				return;
			}
		);

		return false;
	}

	////////////////
	////////////////

	static MountTempButton() {

		let lol = document.createElement('button');

		lol.classList.add('d-none');
		document.body.append(lol);

		return lol;
	};

	////////////////
	////////////////

	static WhenDocumentReady() {

		let map = {
			upload: new DocReadyFunc((btn)=> Photo.WhenUploadTo(btn), false),
			delete: new DocReadyFunc((id, uuid, btn)=> Photo.WhenDelete(id, uuid, btn), true)
		};

		for(let key in map) {
			let item = map[key];

			jQuery(`[data-photolib-cmd=${key}]`)
			.on('click', function(ev) {

				let that = jQuery(this);

				if(item.data === false) {
					item.func.call(null, that);
					return false;
				}

				let eID = that.attr('data-id') ?? null;
				let eUUID = that.attr('data-uuid') ?? null;

				if(eID)
				item.func.call(null, eID, eUUID, that);

				return false;
			});
		}

		jQuery(function(){

			new SimpleLightbox('.PhotoGalleryItem', {
				captions: true,
				captionSelector: 'self',
				scrollZoom: false,
				fileExt: false,
				sourceAttr: 'data-url-lg'
			});

			return;
		});

		return;
	};

	static WhenDelete(eID, eUUID, btn) {

		let pho = new Photo(eID, eUUID);
		pho.onDelete();

		return false;
	};

	static WhenUploadTo(btn) {

		// the upload button does its job good and i should keep it, but
		// it also needs to be refactored a bit to be more programmable.
		// this is stupid but it will be ok for now.

		let lol = Photo.MountTempButton();
		let parentUUID = btn.attr('data-parent-uuid');
		let parentType = btn.attr('data-parent-type');

		console.log(`[Photo.WhenUploadTo] ${parentType} ${parentUUID}`);

		let upl = new UploadButton(lol, {
			'title': 'Upload Photos...',
			'dataset': { 'ParentUUID': parentUUID, 'ParentType': parentType },
			'onSuccess': ()=> location.reload()
		});

		upl.onButtonClick();
		lol.remove();

		return false;
	};

};

export default Photo;
