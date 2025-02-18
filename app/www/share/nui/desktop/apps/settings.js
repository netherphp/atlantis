////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

import NetherOS from '../__main.js';

await NetherOS.load();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

let TemplateConfigWindowHTML = `
<div>
	<table class="table">
		<thead>
			<tr>
				<th class="th-grow">Setting</th>
				<th class="th-shrink">Value</th>
				<th class="th-shrink"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="text-nowrap">Nullary</td>
				<td class="text-nowrap ta-right">
					<input type="color" data-win-input="--atl-dtop-cfg-colour-nullary" />
				</td>
				<td class="text-nowrap">
					<button class="atl-dtop-btn" data-win-action="setting-reset" data-setting-name="--atl-dtop-cfg-colour-nullary">
						<i class="mdi mdi-backup-restore"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td class="text-nowrap">Primary</td>
				<td class="text-nowrap ta-right">
					<input type="color" data-win-input="--atl-dtop-cfg-colour-primary" />
				</td>
				<td class="text-nowrap">
					<button class="atl-dtop-btn" data-win-action="setting-reset" data-setting-name="--atl-dtop-cfg-colour-primary">
						<i class="mdi mdi-backup-restore"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td class="text-nowrap">Window Inactive</td>
				<td class="text-nowrap ta-right">
					<select class="form-select" data-win-input="OS.WindowInactiveClass" style="min-width:150px;">
						<option value="atl-dtop-desktop-window-inactive-none">None</option>
						<option value="atl-dtop-desktop-window-inactive-dim">Dim</option>
						<option value="atl-dtop-desktop-window-inactive-dimblur">Dim &amp; Blur</option>
					</select>
				</td>
				<td class="text-nowrap">
					<button class="atl-dtop-btn" data-win-action="setting-reset" data-setting-name="OS.WindowInactiveClass">
						<i class="mdi mdi-backup-restore"></i>
					</button>
				</td>
			</tr>
			<tr>
				<td class="text-nowrap">Full Screen Windows on Mobile</td>
				<td class="text-nowrap ta-right">
					<select class="form-select" data-win-input="OS.WindowAutoMaximise" style="min-width:150px;">
						<option value="0">Never</option>
						<option value="1">Only on Mobile/Portrait screens</option>
						<option value="2">Always</option>
					</select>
				</td>
				<td class="tw-normal">
					<button class="atl-dtop-btn" data-win-action="setting-reset" data-setting-name="OS.WindowAutoMaximise">
						<i class="mdi mdi-backup-restore"></i>
					</button>
				</td>
			</tr>
		</tbody>
	</table>
</div>
`;

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SettingsApp
extends NetherOS.App {

	onConstruct() {

		(this)
		.setName('Settings')
		.setIdent('net.pegasusgate.atl.settingsapp')
		.setIcon('mdi mdi-cog')
		.setSingleInstance(true)
		.setPinToTaskbarEnd();

		return this;
	};

	onLaunchSingle() {

		let w = new SettingsAppWindow(this);

		this.registerWindow(w);
		w.show();
		w.centerInParent();

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class SettingsAppWindow
extends NetherOS.Window {

	onConstruct() {

		this.inColourNullary = null;
		this.inColourPrimary = null;
		this.inWinInactiveClass = null;
		this.inWinAutoMaximise = null;

		(this)
		.setBody(TemplateConfigWindowHTML)
		.setSize(40, 75, '%');

		this.bindValues();
		this.fillValues();

		return;
	};

	bindValues() {

		this.inColourNullary = this.getInputElement('--atl-dtop-cfg-colour-nullary');
		this.inColourPrimary = this.getInputElement('--atl-dtop-cfg-colour-primary');
		this.inWinInactiveClass = this.getInputElement('OS.WindowInactiveClass');
		this.inWinAutoMaximise = this.getInputElement('OS.WindowAutoMaximise');

		this.inColourNullary.on('change', this.onSettingChange.bind(this));
		this.inColourPrimary.on('change', this.onSettingChange.bind(this));
		this.inWinInactiveClass.on('change', this.onSettingChange.bind(this));
		this.inWinAutoMaximise.on('change', this.onSettingChange.bind(this));

		this.setAction('setting-reset', this.onSettingReset.bind(this));

		return;
	};

	fillValues() {

		let colours = this.os.fetchStyleVarList([
			'--atl-dtop-cfg-colour-nullary',
			'--atl-dtop-cfg-colour-primary'
		]);

		let winInactiveClass = (
			this.os.fetch('OS.WindowInactiveClass')
			|| this.os.configDefaults['OS.WindowInactiveClass']
		);

		// the html5 colour input cannot take alpha values yet.

		this.inColourNullary.val(colours['--atl-dtop-cfg-colour-nullary'].substr(0, 7));
		this.inColourPrimary.val(colours['--atl-dtop-cfg-colour-primary'].substr(0, 7));
		this.inWinInactiveClass.val(winInactiveClass);
		this.inWinAutoMaximise.val(this.os.fetch('OS.WindowAutoMaximise'));

		return;
	};

	onSettingReset(jEv) {

		let n = jEv.target.dataset.settingName;

		////////

		switch(n) {
			case 'OS.WindowAutoMaximise':
				this.inWinAutoMaxPortraitMode.val(this.os.configDefaults[n]);
				this.os.save(n, this.os.configDefaults[n]);
			break;
			case 'OS.WindowInactiveClass':
				this.inWinInactiveClass.val(this.os.configDefaults[n]);
				this.os.dmgr.resetWindowInactiveClass();
				this.os.delete(n);
			break;
			case '--atl-dtop-cfg-colour-nullary':
			case '--atl-dtop-cfg-colour-primary':
				this.getInputElement(n).val(this.os.styleVarDefaults[n].substr(0, 7));
				this.os.pushStyleVar(n, this.os.styleVarDefaults[n]);
				this.os.delete(n);
			break;
		};

		////////

		return false;
	};

	onSettingChange(jEv) {

		let n = jEv.target.dataset.winInput;
		let v = jEv.target.value;

		////////

		switch(n) {
			case 'OS.WindowAutoMaximise':
				this.os.save(n, parseInt(v));
			break;
			case 'OS.WindowInactiveClass':
				this.os.dmgr.resetWindowInactiveClass();
				this.os.dmgr.pushWindowInactiveClass(v);
				this.os.save(n, v);
			break;
			case '--atl-dtop-cfg-colour-nullary':
			case '--atl-dtop-cfg-colour-primary':
				this.os.pushStyleVar(n, v);
				this.os.save(n, v);
			break;
		};

		////////

		return;
	};

};

////////////////////////////////////////////////////////////////////////////////
export default SettingsApp /////////////////////////////////////////////////////
