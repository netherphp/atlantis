import JsonAPI from '/share/nui/api/json.js?v=<?php $Printer($CacheBuster) ?>';

let MessageTemplateString = `
<div class="row mb-2 MessageText" data-message-uuid="">
	<div class="col-9">
		<div class="MessageContent"></div>
		<div class="MessageTimestamp"></div>
	</div>
</div>
`;

class Messenger {

	constructor() {

		this.input = null;
		this.send = null;
		this.log = null;
		this.thread = null;

		this.polling = 3000;

		this.bindElements();
		this.updateLog();

		if(this.polling > 0)
		setInterval(this.updateLog.bind(this), this.polling);

		return;
	};

	bindElements() {

		// fetch the html elements.
		this.input = jQuery('.MessengerInputText');
		this.send = jQuery('.MessengerInputSend');
		this.log = jQuery('.MessageTextLog');

		// fetch assigned data.
		this.thread = parseInt(this.input.attr('data-thread-id'));

		if(!this.thread)
		this.thread = null;

		////////

		this.input.on('keyup', this.onInput.bind(this));
		this.send.on('click', this.txSendMsg.bind(this));

		return;
	};

	onInput(jev) {

		let ev = jev.originalEvent;

		if(ev.code === 'Enter') {
			this.txSendMsg();
			return;
		}

		return;
	};

	txSendMsg() {

		let msg = jQuery.trim(this.input.val());

		this.input.val('');

		this.constructor.sendMessage(
			this.thread, msg,
			(result)=> this.updateLog()
		);

		return;
	};

	rxUpdateLog(result) {

		for(const msg of result.payload.Messages) {
			let msgsel = `.MessageText[data-message-uuid="${msg.UUID}"]`;

			if(jQuery(msgsel).length > 0)
			continue;

			////////

			let row = (
				jQuery(MessageTemplateString)
				.attr('data-message-uuid', msg.UUID)
			);

			row.addClass(
				msg.IsToUser === 1
				? 'MessageTextInbound justify-content-start'
				: 'MessageTextOutbound justify-content-end'
			);

			row.find('.MessageContent').text(msg.Message);
			row.find('.MessageTimestamp').text(msg.DateSent);

			////////

			this.log.append(row);
		}

		return;
	};

	updateLog() {

		let lastMsgUuid = this.findLastMessageUUID();

		let req = new JsonAPI.Request('GET', '/api/messenger/thread');
		let data = { ID: this.thread, SinceUUID: lastMsgUuid };

		//console.log(data);

		(req.send(data))
		.then(this.rxUpdateLog.bind(this))
		.catch(req.catch);

		return;
	};

	findLastMessageUUID() {

		let msg = this.log.find('.MessageText:last');

		if(msg.length === 0)
		return null;

		return msg.attr('data-message-uuid');
	};

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static sendMessage(thread, msg, onSent=null) {

		let req = new JsonAPI.Request('POST', '/api/messenger/inbox');
		let data = { ThreadID: thread, Message: msg };

		(req.send(data))
		.then(function(result) {
			onSent(result);
			return;
		})
		.catch(req.catch);

		return;
	};

};

export default Messenger;
