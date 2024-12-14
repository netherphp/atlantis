////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../../nui/api/json.js';
import OS from '../../../nui/desktop/os.js';

import LoginApp from './apps/login.js';
import AdminUserApp from './apps/admin-users.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class AtlantOS
extends OS {

	constructor(selector='body') {

		super(selector);

		this.name = 'AtlantOS';
		this.version = 'Mk1';

		////////

		let api = new API.Request(
			'GET', '/api/atlantos/v1/boot'
		);

		/////////

		(api.send())
		.then(this.onBoot.bind(this))
		.catch(api.catch);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onBoot(result) {

		let self = this;
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
			let tbi = item.value.taskbarItem;

			(tbi.element.parent())
			.append(tbi.element);
		}


		this.element.removeClass('o-0');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

};

export default AtlantOS;
