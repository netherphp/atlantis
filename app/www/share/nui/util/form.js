////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class FormUtil {

	constructor(input) {

		this.element = jQuery(input);
		this.contentType = null;
		this.dataType = null;
		this.data = [];

		this.setContentType('encoded');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	read() {
	/*//
	@date 2023-02-06
	sucks the data currently in the form into oneself.
	//*/

		this.data = [];

		for(const item of this.element.serializeArray()) {
			if(item.name.match(/\[\]$/)) {
				let kname = item.name.replace(/\[\]$/,'');

				if(!Array.isArray(this.data[kname]))
				this.data[kname] = [];

				this.data[kname].push(item.value);
			}

			else {
				this.data[item.name] = item.value;
			}
		}

		return this;
	};

	set(key, val) {
	/*//
	@date 2023-02-06
	push specific data into the dataset.
	//*/

		this.data[key] = val;

		return this;
	};

	trim(args) {

		// given a list of keys trim each of them.

		if(Array.isArray(args)) {
			for(const item of args) {
				if(typeof this.data[item] === undefined)
				continue;

				console.log(`[FormUtil:trim] trimming ${item}`);
				this.data[item] = jQuery.trim(this.data[item]);
			}
		}

		else if(typeof args === 'string') {
			if(typeof this.data[args] !== undefined) {
				console.log(`[FormUtil:trim] trimming ${args}`);
				this.data[args] = jQuery.trim(this.data[args]);
			}
		}

		return this;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	getContentType() {
	/*//
	@date 2023-02-06
	//*/

		return this.contentType;
	};

	setContentType(ctype) {

		switch(ctype) {
			case 'normal':
			case 'string':
			case 'encoded':
				this.dataType = 'encoded';
				this.contentType = 'application/x-www-form-urlencoded;charset=UTF-8';
			break;

			case 'multipart':
			case 'data':
			case 'upload':
				this.dataType = 'formdata';
				this.contentType = 'multipart/form-data';
			break;

			default:
				this.dataType = 'encoded';
				this.contentType = ctype;
			break;
		}

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	updateUrlObject(url, fields) {

		// given a url object go through this form and set data from it
		// as get variables. if a form value is set to its default then
		// completely remove that variable.

		// this provides a way for forms which are stupid with scripting
		// to manipulate a url with its field data to always produce the
		// cleanest possible url that form could have produced.

		let data = this.getDataObject();

		for(const field of fields) {
			if(typeof data[field.name] === undefined)
			continue;

			console.log(`[FormUtil::updateUrlObject] updating ${field.name}`);

			if(data[field.name] !== field.default)
			url.searchParams.set(field.name, data[field.name]);
			else
			url.searchParams.delete(field.name);
		}

		return this;
	};

	updateCurrentUrl(fields) {

		let url = new URL(location.href);

		this.updateUrlObject(url, fields);

		return url;
	};

	getDataFormData() {

		let data = new FormData;

		for(const key in this.data)
		data.set(key, this.data[key]);

		return data;
	};

	getDataArray() {
		// misnaming was mearly a setback.
		return this.getDataObject();
	};

	getDataObject() {

		let data = {};

		for(const key in this.data)
		data[key] = this.data[key];

		return data;
	};

	getDataString() {

		return FormUtil.ObjectArrayToDataString(this.data);
	};

	getData() {

		if(this.dataType === 'formdata')
		return this.getDataFormData();

		if(this.dataType === 'object' || this.dataType === 'array')
		return this.getDataObject();

		return this.getDataString();
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static ObjectArrayToDataString(input) {

		let output = [];

		for(const key of Object.keys(input)) {
			if(input[key] === null)
			output.push(`${key}=`);
			else
			output.push(`${key}=${encodeURIComponent(input[key])}`);
		}


		return output.join('&');

	};

	static WhenSubmitDoCleanURL() {

		let form = new FormUtil(this);
		let url = new URL(location.href);
		let data = form.getDataObject();

		for(const key of Object.keys(data)) {
			if(data[key] === '')
			url.searchParams.delete(key);
			else
			url.searchParams.set(key, data[key]);
		}

		location.href = url.toString();
		return false;
	};

	static ArrayToFormData(input) {

		let output = new FormData;

		for(const k in input) {
			output.append(k, input[k]);
		}

		return output;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default FormUtil; ///////////////////////////////////////////////////////
