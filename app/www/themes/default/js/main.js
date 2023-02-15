import NUIUtil from '/share/nui/util.js';

function initMenu() {

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

			document.cookie = `theme=${theme};path=/;max-age=31536000`;
		};

		document.documentElement.dataset.bsTheme = theme;
		return;
	});

	return;
};

jQuery(function(){

	initColourModer();
	initMenu();
	initCopyLinks();

	return;
});

export default true;
