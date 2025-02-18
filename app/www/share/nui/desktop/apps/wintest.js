////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateWindow1 = `
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc convallis urna lacus, et rhoncus dolor pretium quis. Cras maximus varius leo, sed imperdiet velit pulvinar gravida. Aliquam luctus, magna in consequat aliquet, magna diam condimentum lorem, quis ullamcorper magna ligula et orci. Mauris sed urna ac purus faucibus pharetra eget et nulla. Sed volutpat suscipit risus. Morbi at bibendum sapien. Proin nec volutpat velit. Phasellus ullamcorper purus at est lobortis venenatis.</p>
<div class="row justify-content-center mb-3">
	<div class="col-auto">
		<button class="atl-dtop-btn" data-win-input="ClickToCenter">Click To Center Again</button>
	</div>
	<div class="col-auto">
		<button class="atl-dtop-btn" data-win-input="ClickToAutoSize">Click To Auto Size Again</button>
	</div>
</div>
<p>Mauris consequat nibh sit amet finibus tristique. Suspendisse in velit et mauris commodo lobortis. Duis vel ultricies nibh, eu mollis libero. Cras ac egestas odio. Quisque dapibus elementum blandit. Quisque id iaculis quam, ut sollicitudin metus. Cras congue urna et tellus tristique sodales. Vestibulum consectetur ex et neque auctor, in facilisis arcu pretium. Suspendisse feugiat sodales libero ac aliquet. Nullam lobortis quam eu dolor feugiat, eget lacinia diam egestas. Curabitur est magna, aliquam et eros ut, finibus lacinia est. Fusce eu feugiat odio, et tincidunt lacus. Nullam porttitor, mauris vitae convallis consectetur, turpis mi ultrices lectus, in fringilla dolor lorem nec tortor. Phasellus non iaculis enim. Interdum et malesuada fames ac ante ipsum primis in faucibus.</p>
<p>In a bibendum risus. Duis mattis, turpis posuere imperdiet euismod, tellus leo sodales sapien, vitae varius nibh ipsum orci. Vivamus dignissim risus sit amet suscipit venenatis. Maecenas semper varius dolor, et convallis neque pretium eget. In ullamcorper vulputate magna, in efficitur felis vestibulum tincidunt. Nam eu tellus magna. In porttitor nunc id mauris luctus lacinia. Nam feugiat, quam vitae vestibulum faucibus, odio tellus venenatis ex, et porta ipsum neque ac libero. Curabitur varius lectus nec metus lacinia malesuada. Cras aliquam, ligula ut sollicitudin pretium, turpis justo hendrerit augue, vel egestas felis neque vel arcu. Cras ut elit tristique, pellentesque lorem et, auctor elit. Fusce non malesuada sapien. Vestibulum eu urna vel magna consequat varius. Sed interdum enim vitae turpis fermentum, sed dignissim urna feugiat. Pellentesque finibus lacinia purus maximus tempus.</p>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class WindowTestApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Window Test')
		.setIdent('net.pegasusgate.atl.wintest')
		.setIcon('mdi mdi-window-restore')
		.setListed(true)
		.setSingleInstance(false);

		return;
	};

	onLaunch() {

		let w = new WindowTestWindow1;
		this.registerWindow(w);

		w.show();
		w.centerInParent();

		return;
	};

};

class WindowTestWindow1
extends NetherOS.Window {

	onConstruct() {

		let self = this;

		this.setBody(TemplateWindow1);

		this.btnCenter = this.getInputElement('ClickToCenter');
		this.btnResize = this.getInputElement('ClickToAutoSize');

		this.btnCenter.on('click', this.onCenter.bind(this));
		this.btnResize.on('click', this.onResizeAuto.bind(this));

		return;
	};

	onCenter() {

		this.centerInParent();

		return;
	};

	onResizeAuto() {

		this.resizeToFit();

		return;
	};

};

export default WindowTestApp;
