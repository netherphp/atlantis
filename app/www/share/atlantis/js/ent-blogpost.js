import API from '/share/nui/api/json.js';

class BlogPost {

	constructor({
		ID=null, UUID=null,
		Editor=null, Alias=null, Title=null, Content=null,
		Enabled=null, AdminNotes=null,
		PageURL=null, CoverImageURL=null,
		Blog=null, Tags=null,

		SourceURL=null, SourceDate=null, SourceDateSort=null, SourceExcerpt=null
	}) {

		this.insertVerb = 'POST';
		this.insertURL = '/api/blog/post';

		this.taggingType = 'blogpost';
		this.taggingVerb = 'TAGSPATCH';
		this.taggingURL = '/api/media/entity';

		////////

		this.id = ID;
		this.uuid = UUID;
		this.editor = Editor;
		this.alias = Alias;
		this.title = Title;
		this.pageURL = PageURL;
		this.coverImageURL = CoverImageURL;
		this.adminNotes = AdminNotes;

		this.sourceURL = SourceURL;
		this.sourceDate = SourceDate;
		this.sourceDateSort = SourceDateSort;
		this.sourceExcerpt = SourceExcerpt;

		this.tags = Tags;
		this.blog = Blog;

		return;
	};

	toDataset() {

		let fdat = new FormData;

		fdat.append('ID', this.id);
		fdat.append('UUID', this.uuid);
		fdat.append('Editor', this.editor);
		fdat.append('Alias', this.alias);
		fdat.append('Title', this.title);
		fdat.append('AdminNotes', this.adminNotes);

		if(this.editor === 'link') {
			fdat.append('URL', this.sourceURL);
			fdat.append('Excerpt', this.sourceExcerpt);
			fdat.append('Date', this.sourceDate);
			fdat.append('OptUseLinkDate', this.sourceDateSort);
		}

		return fdat;
	};

	insert(bid=null, tids=null, tnames=null, img=null) {
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

		if(!data.has('Title') || data.get('Title') === 'null' || !data.get('Title'))
		throw 'MissingTitle';

		data.delete('ID');
		data.delete('UUID');
		data.set('BlogID', bid);

		if(img !== null)
		data.set('PostPhoto', img);

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
			throw 'what';
		});

		return;
	};


};

export default BlogPost;
