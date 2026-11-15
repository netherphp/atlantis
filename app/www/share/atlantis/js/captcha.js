////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// requires: jquery-global
// requires: nui-api-json

import API from '/share/nui/api/json.js';

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

class Captcha {

	id = null;

	element = null;
	img =  null;
	token = null;
	code = null;
	btnContrast = null;	

	codeTryTimer = null;
	rateLimitTimer = null;
	
	ttlTimer = null;
	ttlValue = null;
	ttlStart = null;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	constructor(elem) {
		
		this.element = jQuery(elem);
		this.id = this.element.attr('id');
		this.img = this.element.find('[data-captcha-image]');
		this.token = this.element.find('[data-captcha-token]');
		this.code = this.element.find('[data-captcha-code]');
		this.btnContrast = this.element.find('[data-captcha-toggle-contrast]');
		this.btnFresh = this.element.find('[data-captcha-fresh]');
		this.pbarLifespan = this.element.find('.progress');

		(this.code)
		.on('keyup', this.onCodeKeyUp.bind(this));

		(this.btnContrast)
		.on('click', this.onToggleContrast.bind(this));

		(this.btnFresh)
		.on('click', this.onFresh.bind(this));

		this.updateStatusPending();
		this.refresh();

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	startCountdown(ttl) {

		if(this.ttlTimer)
		clearTimeout(this.ttlTimer);

		////////

		this.ttlValue = ttl;
		this.ttlStart = Math.floor(Date.now() / 1000);

		this.onTimerTTL();

		return this;
	};

	refresh() {

		let that = this;
		let api = new API.Request('FRESH', '/api/captcha');
		let data = { 'Type': 'nether' };

		////////

		that.code.val('');
		that.updateStatusLoading();
		that.updateProgressTTL(0);

		(api.send(data))
		.then(function(r) {
			that.startCountdown(r.payload.TTL - 2);
			that.updateStatusPending();
			that.updateToken(r.payload.Token);
			that.updateImage(r.payload.Image);
			return;
		})
		.catch(function(r) {
			if(r.error === 2) {
				that.updateStatusPending();
				alert(r.message);
				return;
			}

			api.catch(r);
			return;
		});
		
		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	updateStatusValid() {
		this.element.attr('data-captcha-status', 'valid');
		return;
	};

	updateStatusInvalid() {
		this.element.attr('data-captcha-status', 'invalid');
		return;
	};

	updateStatusPending() {
		this.element.attr('data-captcha-status', 'pending');
		return;
	};

	updateStatusLoading() {
		this.element.attr('data-captcha-status', 'loading');
		return;
	};

	updateImage(src) {

		this.img.attr('src', src);

		return;
	};

	updateToken(token) {

		this.token.val(token);

		return;
	};

	updateProgressTTL(value) {

		(this.element)
		.css('--progress-value', `${value}%`);

		if(value < 25)
		this.element.attr('data-status-ttl', 'warning');
		else
		this.element.attr('data-status-ttl', 'ok');

		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	onCodeKeyUp(ev) {

		if(this.rateLimitTimer !== null)
		return;

		////////

		this.updateStatusLoading();

		if(this.codeTryTimer !== null)
		clearTimeout(this.codeTryTimer);

		this.codeTryTimer = setTimeout(
			this.onCodeTry.bind(this, [ ev ]),
			750
		);

		return false;
	};

	onCodeTry(ev) {

		let that = this;
		let api = new API.Request('CHECK', '/api/captcha');
		let data = { Type: 'nether' };

		data['nether-captcha-token'] = this.token.val();
		data['nether-captcha-code'] = this.code.val();

		(api.send(data))
		.then(function(r) {
			
			if(r.payload.Valid)
			that.updateStatusValid();
			else
			that.updateStatusInvalid();

			return;
		})
		.catch(function(r) {
			if(r.error === 2) {
				that.onRateLimited(ev);
				console.log(`[onCodeTry] ${r.message}`);
				return;
			}

			api.catch(r);
			return;
		});

		return false;
	}

	onRateLimited(ev) {

		this.rateLimitTimer = setTimeout(
			this.onCodeTry.bind(this, [ ev ]),
			2000
		);

		return false;
	};

	onToggleContrast(ev) {

		const key = 'data-image-filter';
		let current = this.element.attr(key);

		if(current !== 'invert') {
			this.element.attr(key, 'invert');
			this.btnContrast.addClass('active');
		}

		else {
			this.element.attr(key, '');
			this.btnContrast.removeClass('active');
		}

		return false;
	};

	onFresh(ev) {
	
		this.refresh();

		return false;
	};

	onTimerTTL() {

		let now = Math.floor(Date.now() / 1000);
		let diff = (now - this.ttlStart);
		let value = 100 - ((diff / this.ttlValue) * 100);

		////////

		if(value > 100) value = 100;
		if(value < 0) value = 0;

		this.updateProgressTTL(value);

		////////

		if(value > 0) {
			this.ttlTimer = setTimeout(
				this.onTimerTTL.bind(this),
				1000
			);

			return false;
		}

		this.refresh();

		////////

		return false;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static WhenDocumentReady() {
		// this<document>

		jQuery('.nether-captcha')
		.each(function() {

			if(typeof this.dataset.captchaStatus === 'undefined')
			new Captcha(this);

			return;
		});

		return;
	};

	static Realboot() {
		jQuery(Captcha.WhenDocumentReady);
		return;
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static RealbootEnabled() {

		let url = new URL(import.meta.url);
		let auto = url.searchParams.get('realboot') === 'true';

		return auto;
	};

};

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

if(Captcha.RealbootEnabled())
Captcha.Realboot();

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////

export default Captcha;
