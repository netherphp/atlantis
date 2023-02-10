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

		for(const item of this.element.serializeArray())
		this.data[item.name] = item.value;

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

		for(const key of Object.keys(input))
		output.push(`${key}=${encodeURIComponent(input[key])}`);

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

};

export default FormUtil;
