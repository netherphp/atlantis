////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../api/json.js';
import NetherOS from '../__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateWindowAppRowHTML = `
<div class="col-6 col-sm-4 col-md-3 col-lg-2">
	<div class="row align-items-center">
		<div class="col-12"><div class="ratiobox square bg-dtop-primary tc-dtop-nullary fs-most-large" data-app-icon></div></div>
		<div class="col-12" data-app-name-text></div>
	</div>
</div>
`;

let TemplateWindowMainHTML = `
<div class="container-fluid h-100">
<div class="row flex-column g-0 h-100">
	<div class="col-auto mb-4">
		<input type="text" class="form-control m-0" placeholder="Search Apps..." />
	</div>
	<div class="col pos-relative">
		<div class="pos-absolutely" style="overflow:hidden;overflow-y:scroll;">

			<div class="row align-items-center flex-row pos-absolutely g-2" data-win-output="AppList">

			</div>

		</div>
	</div>
</div>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class StartApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Start')
		.setIdent('net.pegasusgate.atl.start-app')
		.setIcon('mdi mdi-star-face')
		.setListed(false)
		.setSingleInstance(true)
		.setPinToTaskbarStart();

		return;
	};

	onLaunchSingle() {

		let win = new StartAppWindow(this);

		super.registerWindow(win);
		win.show();
		win.centerInParent();

		return;
	};

	onWindowShown(jEv, win) {

		(win.element)
		.find('.form-control:first')
		.focus();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class StartAppWindow
extends NetherOS.Window {

	onConstruct() {

		(this)
		.setBody(TemplateWindowMainHTML)
		.setSize(75, 75, '%')
		.setShowOnTaskbar(false)
		.setMaxable(true)
		.setMinable(false)
		.setMovable(true)
		.setResizable(true)
		.hideFooter();

		this.maximise();
		this.fillAppList();

		return;
	};

	fillAppList() {

		let box = this.getOutputElement('AppList');
		let tpl = jQuery(TemplateWindowAppRowHTML);
		let pile = [];

		////////

		for(const a of this.os.apps) {

			if(!a.isListed())
			continue;

			let row = tpl.clone();
			row.attr('data-app-ident', a.ident);

			if(a.icon.match(/(?:mdi|si) /)) {
				row.find('[data-app-icon]').html(
					jQuery('<div />')
					.addClass('pos-absolute pos-h-center pos-v-center')
					.html(`<i class="${a.icon}"></i>`)
				);
			}

			row.attr('data-app-name', a.name);
			row.find('[data-app-name-text]').text(a.name);

			row.on('click', this.onClickItem.bind(this));

			pile.push(row);
			continue;
		}

		pile.sort(function(a, b) {
			if(a.attr('data-app-name') < b.attr('data-app-name'))
			return -1;

			if(a.attr('data-app-name') > b.attr('data-app-name'))
			return 1;

			return 0;
		});

		for(const a of pile)
		box.append(a);

		////////

		return;
	}

	onClickItem(jEv) {

		let that = jQuery(jEv.delegateTarget);
		let ident = that.attr('data-app-ident');

		this.quit();

		this.os.appLaunchByIdent(ident);

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default StartApp ////////////////////////////////////////////////////////
