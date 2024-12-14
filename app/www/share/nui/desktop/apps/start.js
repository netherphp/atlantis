////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import OpSys from '../os.js';
import App from '../app.js';
import Win from '../window.js';

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
extends App {

	constructor() {

		super();

		(this)
		.setName('Start')
		.setIdent('net.pegasusgate.atl.start-app')
		.setIcon('mdi mdi-star-face')
		.setListed(false);

		////////

		this.window = null;

		return;
	};

	onInstall(os) {

		super.onInstall(os);
		super.pushToTaskbar();

		////////

		//setTimeout(
		//	(()=> this.onLaunch(os)),
		//	500
		//);

		return;
	};

	onLaunch(os) {

		super.onLaunch(os);

		this.window = new TestWin(this);
		super.bindWindowAnim(this.window);
		//super.pushToDesktop(this.window);

		os.dmgr.current.addWindow(this.window);
		this.window.show();
		this.window.centerInParent();
		this.window.maximise();

		return;
	};

	onWindowAnim(jEv) {

		super.onWindowAnim(jEv);

		////////

		let aName = jEv.originalEvent.animationName;

		if(aName === 'nui-window-show') {
			(this.window.element)
			.find('.form-control:first')
			.focus();
		}

		return false;
	};

};

class TestWin extends Win {

	constructor(app) {
		super();
		this.ident = 'net.pegasusgate.atl.start-app.main';

		this.hideFooter();

		this.setMaxable(true);
		this.setMinable(false);
		this.setMovable(true);
		this.setResizable(true);
		this.setTitle(app.name);
		this.setIcon(app.icon);
		this.setBody(TemplateWindowMainHTML);

		let box = this.getOutputElement('AppList');

		for(const a of app.os.apps) {

			if(!a.isListed())
			continue;

			let row = jQuery(TemplateWindowAppRowHTML);

			if(a.icon.match(/(?:mdi|si) /)) {
				row.find('[data-app-icon]')
				.html(
					jQuery('<div />')
					.addClass('pos-absolute pos-h-center pos-v-center')
					.html(`<i class="${a.icon}"></i>`)
				);
			}

			row.find('[data-app-name-text]').text(a.name);

			box.append(row);
		}

		this.element.css({
			'min-width': '50vw',
			'min-height': '50vh'
		});

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default StartApp ////////////////////////////////////////////////////////
