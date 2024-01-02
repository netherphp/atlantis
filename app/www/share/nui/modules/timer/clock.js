import { DateTime } from '/share/atlantis/lib/date/luxon.js';

class Clock {

	constructor(el, offset, label) {

		this.element = el;
		this.offset = offset;
		this.label = label;

		this.tick = false;

		return;
	};

	update(recall=false) {

		let d = DateTime.now().setZone(this.offset);

		let t = d.toLocaleString({
			hour: 'numeric', minute: '2-digit', hour12: false,
			literal: '-'
		});

		if(this.tick) {
			t = t.replace(':', '<span class="o-0">:</span>');
			this.tick = false;
		}

		else {
			t = t.replace(':', '<span class="o-100">:</span>');
			this.tick = true;
		}

		if(this.label)
		t = `${t} ${this.label}`;

		this.element.html(t);

		if(recall)
		setTimeout(()=> this.update(recall), 1000);

		return;
	};

	run() {

		this.update(true);

		return;
	};

};

export default Clock;
