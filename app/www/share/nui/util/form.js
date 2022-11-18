class FormUtil {

	constructor(input) {

		this.element = jQuery(input);
		this.output = 'string';

		return;
	};

	getData() {

		if(this.output === 'formdata')
		return this.getDataFormData();

		if(this.output === 'array')
		return this.getDataArray();

		return this.getDataString();
	};

	getDataFormData() {

		let input = this.element.serializeArray();
		let data = new FormData;

		for(const item of input)
		data.set(item.name, item.value);

		return data;
	};

	getDataArray() {

		let input = this.element.serializeArray();
		let data = {};

		for(const item of input)
		data[item.name] = item.value;

		return data;
	};

	getDataString() {

		let input = this.element.serializeArray();
		return FormUtil.ObjectArrayToDataString(input);
	};

	static ObjectArrayToDataString(input) {

		let output = [];

		for(const item of input)
		output.push(`${item.name}=${encodeURIComponent(item.value)}`);

		return output.join('&');

	};

};

export default FormUtil;
