import API from '/share/nui/api/json.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class BlogPost {

	constructor() {

		this.id = null;
		this.blogID = null;
		this.uuid = null;
		this.enabled = null;
		this.title = null;
		this.alias = null;
		this.content = null;
		this.extraData = {};

		return;
	};

	importPayload(payload) {

		this.id = payload.ID;
		this.blogID = payload.BlogID;
		this.uuid = payload.UUID;
		this.enabled = payload.Enabled;
		this.title = payload.Title;
		this.alias = payload.Alias;
		this.content = payload.Content;

		if(this.payload.ExtraData)
		this.extraData = payload.ExtraData;

		return;
	};

	toFormData() {

		let data = new FormData;

		data.append('ID', this.id);
		data.append('BlogID', this.blogID);
		data.append('Enabled', this.enabled);
		data.append('Title', this.title);
		data.append('Content', this.content);

		if(typeof this.extraData === 'object') {
			for(let k of Object.keys(this.extraData))
			data.append(`ExtraData[${k}]`, this.extraData[k]);
		}

		return data;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	async update(payload) {

		let api = new API.Request('PATCH', '/api/blog/post');

		payload.ID = this.id;

		let prom = (
			(api.send(payload))
			.catch(api.catch)
		);

		return prom;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static async FromAPI(id) {

		let api = new API.Request('GET', '/api/blog/post');

		let prom = (
			(api.send({ ID: id }))
			.then((result)=> this.FromPayload(result.payload))
			.catch(api.catch)
		);

		return prom;
	};

	static FromPayload(payload) {

		let output = new this.prototype.constructor;
		output.importPayload(payload);

		return output;
	};

};

export default BlogPost;
