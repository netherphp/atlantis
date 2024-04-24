import API from '/share/nui/api/json.js';

class Video {

	constructor({
		ID=null, UUID=null,
		Title=null, URL=null,
		AdminNotes=null, PageURL=null, ImageURL=null,

		Tags=null
	}) {

		this.insertVerb = 'POST';
		this.insertURL = '/api/video/entity';

		this.taggingType = 'videotp';
		this.taggingVerb = 'TAGSPATCH';
		this.taggingURL = '/api/media/entity';

		////////

		this.id = ID;
		this.uuid = UUID;
		this.title = Title;
		this.url = URL;
		this.adminNotes = AdminNotes;

		this.tags = Tags;

		return;
	};

	toDataset() {

		let fdat = new FormData;

		fdat.append('ID', this.id);
		fdat.append('UUID', this.uuid);
		fdat.append('Title', this.title);
		fdat.append('URL', this.url);
		fdat.append('AdminNotes', this.adminNotes);

		return fdat;
	};

	insert(tids=null, tnames=null, img=null) {
	/*//
	@arg array tids - list of tags to attch after inserting.
	@arg array tnames - list of tags that dont exist to create and attach.
	@arg file img - image to use as the profile picture.
	//*/

		let self = this;
		let api = new API.Request(this.insertVerb, this.insertURL);
		let data = this.toDataset();
		let resultEnt = null;
		let resultTag = null;

		////////

		if(!data.has('URL') || data.get('URL') === 'null' || !data.get('URL'))
		throw 'MissingURL';

		if(!data.has('Title') || data.get('Title') === 'null' || !data.get('Title'))
		throw 'MissingTitle';

		data.delete('ID');
		data.delete('UUID');

		////////

		(api.send(data))
		.then(function(result) {
			resultEnt = result;

			if((!tids || !tids.length) && (!tnames || !tnames.length))
			return;

			let tapi = new API.Request(self.taggingVerb, self.taggingURL);
			let tdat = {
				'EntityType': self.taggingType,
				'EntityUUID': result.payload.UUID,
				'TagID': tids,
				'TagName': tnames,
				'OnlyAdd': 1
			};

			return tapi.send(tdat);
		})
		.then(function(result) {
			resultTag = result;

			location.href = `?added=${resultEnt.payload.ID}`;
			return;
		})
		.catch(function(result) {
			console.log(result);

			if(result.error !== 0)
			alert(result.message);

			return;
		});

		return;
	};


};

export default Video;
