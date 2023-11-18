
class CountdownTimer {

	constructor({ element, date, auto, mode, zero, zeroText, leapyears, showZeros }) {

		this.idd = null;
		this.element = jQuery(element);
		this.date = date;
		this.auto = auto;
		this.mode = mode ?? 'until';
		this.showZeros = showZeros ?? true;

		this.zero = zero ?? false;
		this.zeroText = zeroText ?? false;

		this.leapyears = leapyears ?? false;
		this.wrapDigitHTML = [ '<span class="CountdownTimerDigit">', '</span>' ];
		this.wrapLabelHTML = [ '<span class="CountdownTimerLabel">', '</span>' ];

		////////

		if(this.auto) {
			this.update();
			this.run();
		}

		////////

		return;
	};

	run() {

		this.stop();

		this.iid = setInterval(
			this.update.bind(this),
			500
		);

		return;
	};

	stop() {

		if(this.iid !== null)
		clearInterval(this.iid);

		return;
	};

	timedata(origin){

		let total;
		let seconds;
		let minutes;
		let hours;
		let days;
		let years;
		let dir = 1;

		const MS = 1000;
		const SecPerMin = 60;
		const MinPerHr = 60;
		const HrPerDay = 24;
		const DayPerYr = this.leapyears ? 365.25 : 365;

		////////

		if(this.mode === 'until')
		total = Date.parse(origin) - Date.parse(new Date());
		else
		total = Date.parse(new Date()) - Date.parse(origin);

		total /= MS;

		////////

		if(total < 0) {
			dir = -1;

			if(this.zero)
			total = 0;
		}

		total = Math.abs(total);

		////////

		seconds = Math.floor(
			(total) % SecPerMin
		);

		minutes = Math.floor(
			(total / SecPerMin) % MinPerHr
		);

		hours = Math.floor(
			(total / (MinPerHr * SecPerMin)) % HrPerDay
		);

		days = Math.floor(
			(total / (HrPerDay * MinPerHr * SecPerMin)) % DayPerYr
		);

		years = Math.floor(
			(total / (HrPerDay * MinPerHr * SecPerMin * DayPerYr))
		);

		return { dir, total, years, days, hours, minutes, seconds };
	};

	wrapDigit(val) {

		return `${this.wrapDigitHTML[0]}${val}${this.wrapDigitHTML[1]}`;
	};

	wrapLabel(val) {

		return `${this.wrapLabelHTML[0]}${val}${this.wrapLabelHTML[1]}`;
	};

	update() {
		let timedata = this.timedata(this.date);
		let dataset = [];

		//console.log(timedata);

		if(timedata.total <= 0) {
			if(this.zero) {
				if(this.zeroText)
				this.element.html(`${this.wrapLabel(this.zeroText)}`);
				else
				this.element.html(`${this.wrapDigit(timedata.minutes)}${this.wrapLabel('M')} ${this.wrapDigit(timedata.seconds)}${this.wrapLabel('S')}`);

				return;
			}
		}

		if(timedata.dir < 0)
		dataset.push(`${this.wrapLabel('-')} `);

		if(timedata.years || this.showZeros)
		dataset.push(`${this.wrapDigit(timedata.years)}${this.wrapLabel('Y')} `);

		if(timedata.days || this.showZeros)
		dataset.push(`${this.wrapDigit(timedata.days)}${this.wrapLabel('D')} `);

		if(timedata.hours || this.showZeros)
		dataset.push(`${this.wrapDigit(timedata.hours)}${this.wrapLabel('H')} `);

		dataset.push(`${this.wrapDigit(timedata.minutes)}${this.wrapLabel('M')} `);
		dataset.push(`${this.wrapDigit(timedata.seconds)}${this.wrapLabel('S')} `);

		this.element.html(dataset.join(' '));

		return;
	};

};

export default CountdownTimer;
