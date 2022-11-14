
class JsonResult {

	constructor( error, message, location, payload ) {

		this.error = parseInt(error);
		this.message = message;
		this.location = location;
		this.payload = payload;

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
			result.Location,
			result.Payload
		);
	};

};

export default JsonResult;
