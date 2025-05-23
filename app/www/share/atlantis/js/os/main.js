////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../../nui/api/json.js';
import NetherOS from '../../../nui/desktop/__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AtlantOS
extends NetherOS.System {

	constructor(selector='body') {

		super(selector);

		this.setName('AtlantOS');
		this.setVersion('Mk1');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onBoot(result) {

		let loaders = [];

		////////

		for(const url of result.payload.Apps)
		loaders.push(this.appInstallFromModule(url));

		(Promise.allSettled(loaders))
		.then(this.onReady.bind(this));

		////////

		return;
	};

	onReady(loaders) {

		// resort the taskbar to match the order they were shipped by
		// the api. they will have been installed in their order of
		// opportunity.

		for(const item of loaders) {
			if(!item || !item.value)
			continue;

			let tbi = item.value.taskbarItem;

			if(!tbi)
			continue;

			(tbi.element.parent())
			.append(tbi.element);
		}

		// then expose the desktop for use.

		(this.element)
		.removeClass('o-0');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	boot() {

		let api = new API.Request('GET', '/api/atlantos/v1/boot');

		/////////

		(api.send())
		.then(this.onBoot.bind(this))
		.catch(api.catch);

		return;
	};

};

export default AtlantOS;
