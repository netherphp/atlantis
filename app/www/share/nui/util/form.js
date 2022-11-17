
class FormUtil {

	constructor(input) {

		this.element = jQuery(input);
		return;
	};

	getFormData() {

		let input = this.element.serializeArray();
		let data = new FormData;

		for(const item of input)
		data.set(item.name, item.value);

		return data;
	};

	getFormArray() {

		let input = this.element.serializeArray();
		let data = {};

		for(const item of input)
		data[item.name] = item.value;

		console.log(data);

		return data;
	};

	getDataString() {

		let input = this.element.serializeArray();
		let output = [];

		for(const item of input)
		output.push(`${item.name}=${encodeURIComponent(item.value)}`);

		return output.join('&');
	};

};

export default FormUtil;
