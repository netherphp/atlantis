import JsonResult from '/share/atlantis/api/json-result.js';

class JsonRequest {

	constructor(method, url, data) {

		this.method = method;
		this.url = url;
		this.data = data ? data : new FormData;

		return;
	};

	send() {

		let req = (
			fetch(this.url, { method: this.method, body: this.data })
			.then(function(res) { return JsonResult.FromResponse(res); })
		);

		return req;
	};

};

export default JsonRequest;
