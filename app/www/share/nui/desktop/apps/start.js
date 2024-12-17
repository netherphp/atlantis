////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import API from '../../api/json.js';
import NetherOS from '../main.js';

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


		this.setName('Start');
		this.setIdent('net.pegasusgate.atl.start-app');
		this.setIcon('mdi mdi-star-face');
		this.pushToTaskbar(true);
		this.setListed(false);

		////////

		this.window = null;

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
		//this.window.maximise();

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

class TestWin
extends NetherOS.Window {

	constructor(app) {
		super();
		this.ident = 'net.pegasusgate.atl.start-app.main';

		this.hideFooter();

		this.setMaxable(true);
		this.setMinable(false);
		this.setMovable(true);
		this.setResizable(true);
		this.setTitle(`${app.os.name} ${app.os.version}`);
		this.setIcon(app.icon);
		this.setBody(TemplateWindowMainHTML);
		this.setSize(75, 75, '%');
		//this.hideHeader();
		this.hideFooter();


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
