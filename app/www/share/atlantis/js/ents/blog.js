import API from '/share/nui/api/json.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Blog {

	constructor() {

		this.id = null;
		this.uuid = null;
		this.title = null;
		this.tagline = null;
		this.details = null;

		return;
	};

	importPayload(payload) {

		this.id = payload.ID;
		this.uuid = payload.UUID;
		this.title = payload.Title;
		this.tagline = payload.Tagline;
		this.details = payload.Details;

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	async update(payload) {

		let api = new API.Request('PATCH', '/api/blog/entity');

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

		let api = new API.Request('GET', '/api/blog/entity');

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

export default Blog;
