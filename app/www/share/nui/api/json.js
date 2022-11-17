

class JsonResult {

	constructor( error, message, goto, payload ) {

		this.error = parseInt(error);
		this.message = message ?? '';
		this.goto = goto ?? null;
		this.payload = payload ?? [];

		return;
	};

	static async FromResponse(response) {

		let result = await (
			(response.json())
			.then(function(obj){ return obj; })
		);

		return new JsonResult(
			result.Error,
			result.Message,
			result.Goto,
			result.Payload
		);
	};

};

class JsonRequest {

	constructor(method, url, data) {

		this.method = method;
		this.url = url;
		this.data = data ? data : [];

		return;
	};

	send(data) {

		if(typeof data === 'undefined')
		data = this.data;

		let req = (
			fetch(this.url, { method: this.method, body: data })
			.then(function(resp) { return JsonResult.FromResponse(resp); })
			.then(function(result) {
				if(result.error !== 0)
				return Promise.reject(result);

				return result;
			})
		);

		return req;
	};

	/*************************************************************************
	**** chainable setters***************************************************/

	setMethod(method) {

		if(typeof method === 'string')
		this.method = method;

		return this;
	};

	setUrl(url) {

		if(typeof url === 'string')
		this.url = url;

		return this;
	};

	setData(data) {

		if(data instanceof FormData)
		this.data = data;

		return this;
	};

	/*************************************************************************
	**** "callback "api" ****************************************************/

	// these methods are not designed to be used directly so much as passed
	// to then() and catch()

	catch(error) {

		if(error instanceof JsonResult)
		alert(`JsonRequest Error: ${error.message}`);

		return;
	};

	goto(result) {

		let goto = '/';

		if(result instanceof JsonResult) {
			if(result.goto !== null)
			goto = result.goto;
		}

		location.href = goto;
		return;
	};

};

export { JsonRequest, JsonResult };
