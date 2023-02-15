
class Util {

	static elementCopyValueToClipboard() {
	/*//
	copy the specified text to the clipboard. the source element will have its content
	modified for a few seconds to visually demonstrate something has happened. generally
	we are expecting things calling this to be buttons.
	//*/

		let that = jQuery(this);
		let what = that.attr('data-nui-copy-value');
		let originalText = null;

		////////

		Util.copyValueToClipboard(what);

		////////

		originalText = jQuery(that).html();
		jQuery(that).text('Copied!');
		setTimeout(function(){ jQuery(that).html(originalText); },1000);

		return false;
	};

	static copyValueToClipboard(what) {

		let textbox = null;

		// create a textbox that contains the content we want and add it to
		// the dom. we couldn't display none it or else the selection would
		// not work. we can however set it to 0x0.

		jQuery('body').append(
			textbox = jQuery('<textarea />')
			.css({'width':'0px','height':'0px','border':'0px;'})
			.val(what)
		);

		console.log(`[Util.copyValueToClipboard] ${what}`);

		textbox.select();
		document.execCommand('copy');
		textbox.remove();

		return;
	};

};

export default Util;
