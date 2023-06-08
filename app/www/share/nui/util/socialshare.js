
class SocialShareSelectors {

	constructor({ Facebook, Twitter }) {

		this.Facebook = Facebook;
		this.Twitter = Twitter;

		return;
	};

};

class SocialShare {

	constructor(url, autobind=false) {

		this.url = url;

		if(autobind)
		this.bind({});

		return;
	};

	open(url) {

		let props = "width=600, height=400, scrollbars=no";
		let type = "pop";

		let yo = window.open(url, type, props);

		return;
	};

	bind({ Facebook=null, Twitter=null }) {

		if(Facebook === null)
		Facebook = '.ShareFacebook';

		if(Twitter === null)
		Twitter = '.ShareTwitter';

		////////

		(this)
		.bindFacebook(Facebook)
		.bindTwitter(Twitter);

		return;
	};

	bindFacebook(selector='.ShareFacebook') {

		let self = this;

		jQuery(selector)
		.on('click', function() {
			self.open(`https://www.facebook.com/sharer/sharer.php?u=${self.url}`);
			return false;
		});

		return this;
	};

	bindTwitter(selector='.ShareTwitter') {

		let self = this;

		jQuery(selector)
		.on('click', function() {
			self.open(`https://twitter.com/intent/tweet?url=${self.url}`);
			return false;
		});

		return this;
	};

};

export default SocialShare;

export { SocialShare, SocialShareSelectors };
