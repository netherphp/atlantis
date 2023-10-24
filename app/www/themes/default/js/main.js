import NUIUtil from '/share/nui/util.js';

function initMenu() {

	// @deprecated 2023-10-23
	// new theme uses SiteMenu element.

	let menu = jQuery('.PageHeader');
	let menutoggle = jQuery('.PageHeaderMenuToggle');

	////////

	menutoggle.on('click', function() {
		menu.toggleClass('MenuOpen');
		return;
	});

	return;
};

function initCopyLinks() {

	let copylinks = jQuery('a[data-nui-copy-value], button[data-nui-copy-value]');

	////////

	copylinks.on(
		'click',
		NUIUtil.elementCopyValueToClipboard
	);

	return;
};

function initColourModer() {

	jQuery('a[data-nui-theme-toggle], button[data-nui-theme-toggle]')
	.on('click', function() {

		let theme = document.documentElement.dataset.bsTheme;
		let mode = this.dataset.nuiThemeToggle;

		console.log(`theme switch mode ${mode}`);

		if(mode === '--toggle') {
			if(theme === 'dark')
			theme = 'light';
			else
			theme = 'dark';


		}

		else {
			theme = mode;
		}

		document.cookie = `theme=${theme};path=/;max-age=31536000`;
		document.documentElement.dataset.bsTheme = theme;
		return;
	});

	return;
};

function initColourSwitch() {

	jQuery('input[data-nui-theme-switch]')
	.on('change', function() {

		let that = jQuery(this);
		let vals = that.attr('data-nui-theme-switch').split('/');
		let on = that.is(':checked');
		let theme = vals[on ? 1 : 0];

		document.cookie = `theme=${theme};path=/;max-age=31536000`;
		document.documentElement.dataset.bsTheme = theme;

		pingColourSwitch();

		return;
	});

	pingColourSwitch();

	return;
};

function pingColourSwitch() {

	jQuery('input[data-nui-theme-switch]')
	.each(function() {

		let that = jQuery(this);
		let vals = that.attr('data-nui-theme-switch').split('/');

		if(document.cookie.indexOf(`theme=${vals[0]}`) >= 0) {
			console.log(`current theme: dark ${vals[0]}`);
			that.removeAttr('checked');
			return;
		}

		if(document.cookie.indexOf(`theme=${vals[1]}`) >= 0) {
			console.log(`current theme: light ${vals[1]}`);
			that.attr('checked', 'checked');
			return;
		}

		return;
	});

	return;
};

jQuery(function(){

	initColourSwitch();
	initCopyLinks();

	initColourModer(); // @deprecated 2023-10-23.
	initMenu();        // @deprecated 2023-10-23.

	return;
});

export default true;
