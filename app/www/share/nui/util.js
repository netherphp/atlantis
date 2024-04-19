
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

	static copyValueToClipboard(what, that=null) {

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

		if(that) {
			originalText = jQuery(that).html();
			jQuery(that).text('Copied!');
			setTimeout(function(){ jQuery(that).html(originalText); },1000);
		}

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static TemporaryStyleClassSwap(el, toRem, toAdd) {

		el.removeClass(toRem).addClass(toAdd);

		setTimeout(
			(()=> el.removeClass(toAdd).addClass(toRem)),
			500
		);

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static ElementValueOrNull(el) {
	/*//
	@date 2024-04-11
	given an element selector return the value of it or null.
	//*/

		return Util.ValueOrNull(jQuery(el).val());
	};

	static EVON(el) {

		return Util.ElementValueOrNull(el);
	};

	static ValueOrNull(v) {
	/*//
	@date 2024-04-11
	given a value return it if it means anything else return null;
	//*/

		let val = jQuery.trim(v);

		if(val.length !== 0)
		return val;

		return null;
	};

	static VON(v) {

		return Util.ValueOrNull(v);
	};

};

export default Util;
