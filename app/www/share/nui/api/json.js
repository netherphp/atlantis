import FormUtil from '/share/nui/util/form.js';

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

		// if no specific data was supplied to this method then pull the
		// dataset already associated with this instance.

		if(typeof data === 'undefined')
		data = this.data;

		////////

		let headers = {
			'content-type': (
				'application/x-www-form-urlencoded;'
				+ 'charset=UTF-8'
			)
		};

		let body = null;
		let req = null;

		////////

		// if the supplied data was a js form data object it seems like
		// that just only really works with multipart when used as the
		// form body.

		if(data instanceof FormData) {
			headers['content-type'] = 'multipart/form-data';
			body = data;
		}

		// if the supplied data is our form util class then we can modify
		// the request that needs to be sent based on what it wants.

		else if(data instanceof FormUtil) {
			headers['content-type'] = data.getContentType();
			body = data.getData();
		}

		else if(typeof data === 'object') {
			body = FormUtil.ObjectArrayToDataString(data);
		}

		// else... yolo. hope you knew what you were doing.

		else {
			body = data;
		}

		////////

		// if it was a multipart form then we actually need to unset that
		// header because the browser will need to refill it with the
		// random boundary values it invents.

		if(headers['content-type'] === 'multipart/form-data')
		delete headers['content-type'];

		////////

		let fetchset = {
			method: this.method,
			headers: headers
		};

		////////

		if(this.method === 'GET' || this.method === 'HEAD') {
			if(typeof body === 'string') {
				if(this.url.indexOf('?') === -1)
				this.url += `?${body}`;
				else
				this.url += `&${body}`;
			}
		}

		else {
			fetchset.body = body;
		}

		////////

		req = (
			fetch(this.url, fetchset)
			.then(function(resp) {
				return JsonResult.FromResponse(resp);
			})
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
		alert(`Error: ${error.message}`);

		console.log('api error:');
		console.log(error);

		return;
	};

	goto(result) {

		let goto = null;

		if(result instanceof JsonResult) {
			if(result.goto !== null)
			goto = result.goto;

			if(goto === 'reload')
			goto = location.href;
		}

		if(goto !== null)
		setTimeout((()=> location.href = goto), 100);

		return false;
	};

	reload(result) {

		location.reload();
		return;
	};

};

export { JsonRequest, JsonResult };
export default { Request: JsonRequest, Result: JsonResult };
